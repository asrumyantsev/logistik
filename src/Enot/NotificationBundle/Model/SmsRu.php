<?php
/**
 *
 */

namespace Enot\NotificationBundle\Model;


use Doctrine\ORM\EntityManager;
use Enot\ApiBundle\Services\HttpClient;
use Enot\ApiBundle\Utils\EnotError;
use Symfony\Component\HttpFoundation\Response;

class SmsRu implements SendSmsInterface
{
    const
        OPTION_SMS_API_ID = 'sms_api_id',
        OPTION_SMS_URL = 'sms_url';

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
     * @param $phone
     * @param $message
     * @return bool
     * @throws SendSmsException
     */
    public function sendSms($phone, $message)
    {
        try {
            $optionsRepository = $this->entityManager->getRepository('EnotApiBundle:Options');
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
                }

                throw new SendSmsException(null, $response->status_text, Response::HTTP_INTERNAL_SERVER_ERROR);
            }
            throw new SendSmsException(EnotError::ERR_CONNECTION_SMS, '', Response::HTTP_INTERNAL_SERVER_ERROR);
        } catch (\Exception $exception) {
            throw new SendSmsException(null, $exception->getMessage(), $exception->getCode());
        }
    }
}