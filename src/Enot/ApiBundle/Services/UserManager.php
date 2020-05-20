<?php
/**
 * ...
 */

namespace Enot\ApiBundle\Services;

use Enot\ApiBundle\Entity\AuthorizationVehicleDriver;
use Enot\ApiBundle\Entity\Driver;
use Enot\ApiBundle\Entity\User;
use Enot\ApiBundle\Entity\Vehicle;
use Enot\ApiBundle\Model\AuthStatusModel;
use Enot\ApiBundle\Model\Coordinates;
use Enot\ApiBundle\Services\Main\HttpClientInterface;
use \FOS\UserBundle\Doctrine\UserManager as FosUserManager;
use Doctrine\ORM\EntityManager;
use Enot\ApiBundle\Entity\Phone;
use Enot\ApiBundle\Services\Exceptions\UserServiceException;
use Enot\ApiBundle\Utils\EnotError;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Encoder\EncoderFactory;

class UserManager
{
    const
        OPTION_SMS_API_ID = 'sms_api_id',
        OPTION_SMS_URL = 'sms_url',
        DEFAULT_PIN_CODE = 5555;

    /** @var EntityManager|null */
    private $entityManager = null;

    /** @var FosUserManager|null */
    private $fosUserManager = null;

    /** @var HttpClientInterface|null */
    private $httpClient = null;

    /** @var EncoderFactory */
    private $encoderFactory;

    /**
     * @param EntityManager $entityManager
     * @param FosUserManager $userManager
     * @param HttpClientInterface $httpClient
     * @param EncoderFactory $encoderFactory
     */
    public function __construct(EntityManager $entityManager,
                                FosUserManager $userManager,
                                HttpClientInterface $httpClient,
                                EncoderFactory $encoderFactory)
    {
        $this->entityManager = $entityManager;
        $this->fosUserManager = $userManager;
        $this->httpClient = $httpClient;
        $this->encoderFactory = $encoderFactory;
    }

    /**
     * @return \Enot\ApiBundle\Repository\DriverRepository
     */
    public function getDriverRepository()
    {
        return $this->entityManager->getRepository('EnotApiBundle:Driver');
    }

    /**
     * @return \Enot\ApiBundle\Repository\VehicleRepository
     */
    public function getVehicleRepository()
    {
        return $this->entityManager->getRepository('EnotApiBundle:Vehicle');
    }

    /**
     * @param $phone
     * @param $password
     * @param $isSmsSend
     * @return string
     * @throws UserServiceException
     */
    public function sendPinCode($phone, $password, $isSmsSend)
    {
        if (!isset($phone)) {
            throw new UserServiceException(EnotError::WRONG_PHONE, '', Response::HTTP_BAD_REQUEST);
        }

        $validatedPhone = $this->validatePhone((string)$phone);
        if (!isset($validatedPhone)) {
            throw new UserServiceException(EnotError::WRONG_PHONE, '', Response::HTTP_BAD_REQUEST);
        }

        if (!$this->getDriverRepository()->findOneByPhone($validatedPhone)) {
            throw new UserServiceException(EnotError::DRIVER_NOT_FOUND, '', Response::HTTP_BAD_REQUEST);
        }

        $email = $validatedPhone . '@evserver.ru';
        $pinCode = $this->createUser($validatedPhone, $email, $password, $isSmsSend);

        if ($isSmsSend) {
            $smsResponse = $this->sendSms($validatedPhone, $pinCode);
        } else {
            $smsResponse = true;
        }

        if ($smsResponse) {
            $result = ResponseManager::STATUS_SUCCESS;
        } else {
            $result = ResponseManager::STATUS_FAIL;
        }

        return $result;
    }

    /**
     * @param $phone
     * @param $code
     * @return bool
     * @throws UserServiceException
     * @throws \Doctrine\ORM\ORMException
     */
    public function confirmUser($phone, $code)
    {
        if (isset($phone) && isset($code)) {

            $validatedPhone = $this->validatePhone((string)$phone);

            if (isset($validatedPhone)) {

                $existUser = $this->fosUserManager->findUserByUsername($validatedPhone);
                if ($existUser) {
                    if ($existUser->getConfirmationToken() == $code) {
                        $existUser->setEnabled(true);
                        $existUser->setConfirmationToken(null);
                        $this->fosUserManager->updateUser($existUser);

                        $this->addPhone($existUser->getId(), $validatedPhone);

                        return ResponseManager::STATUS_SUCCESS;
                    } else {
                        throw new UserServiceException(EnotError::WRONG_CODE, '', Response::HTTP_BAD_REQUEST);
                    }
                } else {
                    throw new UserServiceException(EnotError::WRONG_PHONE, '', Response::HTTP_BAD_REQUEST);
                }
            } else {
                throw new UserServiceException(EnotError::WRONG_PHONE, '', Response::HTTP_BAD_REQUEST);
            }
        } else {
            throw new UserServiceException(EnotError::WRONG_PHONE_OR_CODE, '', Response::HTTP_BAD_REQUEST);
        }
    }

    /**
     * Возвращает информацию о пользователе
     *
     * @param User $user
     * @return mixed
     * @throws UserServiceException
     */
    public function getCustomerInfo(User $user)
    {
        if (isset($user)) {
            $customersRepository = $this->entityManager->getRepository('EnotApiBundle:Customer');
            $customer = $customersRepository->findOneBy(['user' => $user]);

            return $customer;
        } else {
            throw new UserServiceException(EnotError::WRONG_TOKEN, '', Response::HTTP_BAD_REQUEST);
        }
    }

    /**
     * Return phone number without non-numeric symbols
     *
     * @param $phone
     *
     * @return null|string|string[]
     */
    public function getClearPhone($phone)
    {
        return preg_replace("/[^0-9]/", "", $phone);
    }

    /**
     * Return 11-numeric phone number or null
     *
     * @param string $phone
     * @return null|string
     */
    public function validatePhone($phone)
    {
        $result = null;
        //phone number without non-numeric caracters
        $clearPhone = preg_replace("/[^0-9]/", "", $phone);

        if (strlen($clearPhone) === 11) {
            if (substr($clearPhone, 0, 1) == 7) {
                $result = $clearPhone;
            }
        }

        return $result;
    }

    /**
     * Check user login/pass
     *
     * @param $username
     * @param $password
     * @param $mac
     * @param null $position
     * @return mixed
     * @throws UserServiceException
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \Exception
     */
    public function checkAuth($username, $password, $mac, $position = null)
    {
        $user = $this->fosUserManager->findUserByUsername($username);
        if (!$user) {
            throw new UserServiceException(EnotError::WRONG_PARAMETERS, '', Response::HTTP_BAD_REQUEST);
        }
        $encoder = $this->encoderFactory->getEncoder($user);
        $isPasswordValid = $encoder->isPasswordValid($user->getPassword(), $password, $user->getSalt());

        /** @var Driver $driver */
        $driver = $this->getDriverRepository()->findOneByPhone($user->getUsername());
        /** @var Vehicle $vehicle */
        $vehicle = $this->getVehicleRepository()->findOneByDeviceMac($mac);
        if (!$user->isEnabled() || !$isPasswordValid || !$driver || !$vehicle) {
            throw new UserServiceException(EnotError::WRONG_PARAMETERS, '', Response::HTTP_BAD_REQUEST);
        }

        $authDriver = $this->entityManager->getRepository("EnotApiBundle:AuthorizationVehicleDriver")
            ->findOneBy(['driver' => $driver, 'endAt' => null], ['id' => "DESC"]);

        if ($authDriver) {
            throw new UserServiceException(EnotError::WRONG_PARAMETERS, '', Response::HTTP_BAD_REQUEST);
        }

        $authVehicle = $this->entityManager->getRepository("EnotApiBundle:AuthorizationVehicleDriver")
            ->findOneBy(['vehicle' => $vehicle, 'endAt' => null], ['id' => "DESC"]);

        if ($authVehicle) {
            throw new UserServiceException(EnotError::WRONG_PARAMETERS, '', Response::HTTP_BAD_REQUEST);
        }

        $authVehicleDriver = new AuthorizationVehicleDriver();
        $authVehicleDriver->setDriver($driver);
        $authVehicleDriver->setVehicle($vehicle);
        $authVehicleDriver->setStartAt(new \DateTime());
        //save auth parameters

        $positionCoordinates = $this->parseCoordinates($position);
        $authVehicleDriver->setPosition($positionCoordinates);
        $authVehicleDriver->refreshTime();

        $this->entityManager->persist($authVehicleDriver);
        $this->entityManager->flush();

        $result = new AuthStatusModel($driver, $vehicle);
        return $result;
    }

    /**
     * @param $phone
     * @param $position
     * @return AuthStatusModel
     * @throws UserServiceException
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \Exception
     */
    public function updatePosition($phone, $position)
    {
        $user = $this->fosUserManager->findUserByUsername($phone);
        if (!$user) {
            throw new UserServiceException(EnotError::WRONG_PARAMETERS, '', Response::HTTP_BAD_REQUEST);
        }
        /** @var Driver $driver */
        $driver = $this->getDriverRepository()->findOneByPhone($user->getUsername());

        /** @var AuthorizationVehicleDriver $authVehicleDriver */
        $authVehicleDriver = $this->entityManager->getRepository("EnotApiBundle:AuthorizationVehicleDriver")
            ->findOneBy(['driver' => $driver, "endAt" => null], ['id' => "DESC"]);

        if (!$authVehicleDriver) {
            throw new UserServiceException(EnotError::WRONG_PARAMETERS, '', Response::HTTP_BAD_REQUEST);
        }

        $positionCoordinates = $this->parseCoordinates($position);
        $authVehicleDriver->setPosition($positionCoordinates);
        $authVehicleDriver->refreshTime();

        $this->entityManager->persist($authVehicleDriver);
        $this->entityManager->flush();

        $result = new AuthStatusModel($authVehicleDriver->getDriver(), $authVehicleDriver->getVehicle());
        return $result;
    }

    /**
     * @todo Need to remove this method cause it duplicated of CreateTransportationRequestModel::parseCoordinates
     *
     * @param string $coordinatesString
     * @return Coordinates
     */
    private function parseCoordinates($coordinatesString)
    {
        if (!$coordinatesString) {
            $coordinatesString = '';
        }

        $coordinatesArray = explode(',', str_replace(' ', '', $coordinatesString));
        if (!$coordinatesArray || count($coordinatesArray) < 2) {
            return null;
        }

        $latitude = $coordinatesArray[0];
        $longitude = $coordinatesArray[1];

        return new Coordinates($latitude, $longitude);
    }

    /**
     * Create user and set pinCode to confirmationToken field. Return pinCode
     *
     * @param $username
     * @param $email
     * @param $password
     * @param $isSmsSend
     * @return int
     */
    public function createUser($username, $email, $password, $isSmsSend)
    {
        $pinCode = $isSmsSend ? $this->generatePinCode() : self::DEFAULT_PIN_CODE;

        $existUser = $this->fosUserManager->findUserByUsername($username);
        if (isset($existUser)) {
            $existUser->setPlainPassword($password);
            $existUser->setConfirmationToken($pinCode);
            $this->fosUserManager->updateUser($existUser);
        } else {
            $user = $this->fosUserManager->createUser();
            $user->setUsername($username);
            $user->setEmail($email);
            $user->setPlainPassword($password);
            $user->setConfirmationToken($pinCode);
            $user->setEnabled(false);
            $this->fosUserManager->updateUser($user);
        }

        return $pinCode;
    }

    /**
     * @return int
     */
    private function generatePinCode()
    {
        $pinCode = rand(1111, 9999);
        return $pinCode;
    }

    /**
     * @param $phone
     * @param $message
     * @return bool
     * @throws UserServiceException
     */
    private function sendSms($phone, $message)
    {
        try {
            $optionsRepository = $this->entityManager->getRepository('EnotApiBundle:Option');
            $apiId = $optionsRepository->find(self::OPTION_SMS_API_ID)->getValue();
            $smsUrl = $optionsRepository->find(self::OPTION_SMS_URL)->getValue();

            $params = [
                'api_id' => $apiId,
                'to' => $phone,
                'msg' => $message,
                'json' => 1,
                'from' => 'EVSERVER'
            ];
            $query = $smsUrl . http_build_query($params);

            $responseString = $this->httpClient->get($query);
            $response = \GuzzleHttp\json_decode($responseString);

            if ($response) { // Получен ответ от сервера
                if ($response->status == "OK") { // Запрос выполнился
                    return true;
                } else { // Запрос не выполнился (возможно ошибка авторизации, параметрах, итд...)
                    throw new UserServiceException(null, $response->status_text, Response::HTTP_INTERNAL_SERVER_ERROR);
                }
            } else {
                throw new UserServiceException(EnotError::ERR_CONNECTION_SMS, '', Response::HTTP_INTERNAL_SERVER_ERROR);
            }

        } catch (\Exception $exception) {
            throw new UserServiceException(null, $exception->getMessage(), $exception->getCode());
        }
    }

    /**
     * @param $userId
     * @param $phone
     * @throws \Doctrine\ORM\ORMException
     */
    private function addPhone($userId, $phone)
    {
        $user = $this->entityManager->getRepository('EnotApiBundle:User')->find($userId);
        $customerRepository = $this->entityManager->getRepository('EnotApiBundle:Customer');
        $customer = $customerRepository->findOneBy(['user' => $user]);

        $phoneRepository = $this->entityManager->getRepository('EnotApiBundle:Phone');
        $existPhone = $phoneRepository->findOneBy(['number' => $phone]);

        if (!isset($existPhone)) {
            $newPhone = new Phone();
            $newPhone->setCustomer($customer);
            $newPhone->setNumber($phone);

            $this->entityManager->persist($newPhone);
            $this->entityManager->flush($newPhone);
        }
    }
}