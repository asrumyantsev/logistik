<?php
/**
 *
 */

namespace Enot\NotificationBundle\Services;


use Doctrine\ORM\EntityManager;
use Enot\ApiBundle\Services\Main\HttpClientInterface;
use Enot\NotificationBundle\Model\NotificationManagerInterface;
use Enot\NotificationBundle\Model\PushMessage;

class PushManager implements NotificationManagerInterface
{
    const
        OPTION_PUSH_URL = 'push_url',
        OPTION_PUSH_API_KEY = 'push_api_key';

    /** @var HttpClientInterface|null */
    private $httpClient = null;

    /** @var EntityManager */
    private $entityManager;

    /**
     * @param EntityManager $entityManager
     * @param HttpClientInterface $httpClient
     */
    public function __construct(EntityManager $entityManager, HttpClientInterface $httpClient)
    {
        $this->httpClient = $httpClient;
        $this->entityManager = $entityManager;
    }

    /**
     * @param PushMessage $message
     * @return bool
     */
    private function sendRequest(PushMessage $message)
    {
        $optionsRepository = $this->entityManager->getRepository('EnotApiBundle:Option');
        $url = $optionsRepository->find(self::OPTION_PUSH_URL)->getValue();
        $apiKey = $optionsRepository->find(self::OPTION_PUSH_API_KEY)->getValue();

        $this->httpClient->setHeaderAuthName('Authorization');
        //отключаем проверку сертификата
        $curlOpt = [
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false
        ];

        $this->httpClient->post($url,
            \GuzzleHttp\json_encode($message->getFields()),
            "Basic $apiKey",
            "json",
            $curlOpt);

        return true;
    }

    /**
     * @param $receiver
     * @param $title
     * @param $message
     * @param array $data
     * @return bool
     */
    public function send($receiver, $title, $message, $data = [])
    {
        $pushMessage = new PushMessage();
        $pushMessage->setTitle($title);
        $pushMessage->setText($message);

        if (isset($data['content_available'])) {
            $pushMessage->setContentAvailable($data['content_available']);
            unset($data['content_available']);
        }

        if (isset($data['filters'])) {
            foreach ($data['filters'] as $filterKey => $filterValue) {
                $pushMessage->addFilter($filterKey, $filterValue);
            }
            unset($data['filters']);
        }

        $pushMessage->setData($data);
        return $this->sendRequest($pushMessage);
    }
}