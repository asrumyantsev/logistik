<?php
/**
 *
 */

namespace Enot\NotificationBundle\Services;


use Enot\NotificationBundle\Factory\SmsFactory;
use Enot\NotificationBundle\Model\NotificationManagerInterface;

class SmsManager implements NotificationManagerInterface
{
    /** @var SmsFactory */
    private $smsFactory;

    public function __construct(SmsFactory $smsFactory)
    {
        $this->smsFactory = $smsFactory;
    }

    /**
     * @param $receiver
     * @param $title
     * @param $pushMessage
     * @param array $data
     * @return bool
     */
    public function send($receiver, $title, $pushMessage, $data = [])
    {
        $code = isset($data['code']) ? $data['code'] : null;
        $smsSender = $this->smsFactory->get($code);
        return $smsSender->sendSms($receiver, $pushMessage);
    }
}