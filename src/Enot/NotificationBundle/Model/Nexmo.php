<?php
/**
 *
 */

namespace Enot\NotificationBundle\Model;


use Doctrine\ORM\EntityManager;
use Enot\ApiBundle\Services\HttpClient;
use Enot\ApiBundle\Utils\EnotError;
use Symfony\Component\HttpFoundation\Response;

class Nexmo implements SendSmsInterface
{
    const
        OPTION_SMS_API_KEY = 'nexmo_api_key',
        OPTION_SMS_URL = 'nexmo_url',
        OPTION_SMS_API_SECRET = 'nexmo_api_secret';

    /** @var EntityManager */
    private $entityManager;

    /** @var HttpClient */
    private $httpClient;

    public function __construct(EntityManager $entityManager, HttpClient $httpClient)
    {
        $this->entityManager = $entityManager;
        $this->httpClient = $httpClient;
    }

    /**
     * @param string $phone
     * @param string $message
     * @return bool
     * @throws SendSmsException
     */
    public function sendSms($phone, $message)
    {
        try {
            $optionsRepository = $this->entityManager->getRepository('EnotApiBundle:Options');
            $apiKey = $optionsRepository->find(self::OPTION_SMS_API_KEY)->getValue();
            $url = $optionsRepository->find(self::OPTION_SMS_URL)->getValue();
            $secret = $optionsRepository->find(self::OPTION_SMS_API_SECRET)->getValue();

            $params = [
                'api_key' => $apiKey,
                'to' => $phone,
                'text' => $message,
                'from' => 'EVSERVER',
                'api_secret' => $secret
            ];

            $responseString = $this->httpClient->post($url, json_encode($params));
            $response = \GuzzleHttp\json_decode($responseString);

            if ($response) {
                if (current($response->messages)->status == 0) {
                    return true;
                }
            }

            throw new SendSmsException(EnotError::ERR_CONNECTION_SMS, '', Response::HTTP_INTERNAL_SERVER_ERROR);
        } catch (\Exception $exception) {
            throw new SendSmsException(null, $exception->getMessage(), $exception->getCode());
        }
    }
}