<?php
/**
 *
 */

namespace Enot\NotificationBundle\Services;


class NotificationManager
{
    const NOTIFICATION_PUSH = 1,
        NOTIFICATION_SMS = 2,
        NOTIFICATION_EMAIL = 3;
    /** @var PushManager */
    private $pushManager;

    /** @var SmsManager */
    private $smsManager;

    /** @var EmailManager */
    private $emailManager;

    public function __construct(PushManager $pushManager, SmsManager $smsManager, EmailManager $emailManager)
    {
        $this->pushManager = $pushManager;
        $this->smsManager = $smsManager;
        $this->emailManager = $emailManager;
    }

    public function get($notificationType)
    {
        $result = null;
        switch ($notificationType) {
            case self::NOTIFICATION_PUSH:
                $result = $this->pushManager;
                break;
            case self::NOTIFICATION_SMS:
                $result = $this->smsManager;
                break;
            case self::NOTIFICATION_EMAIL:
                $result = $this->emailManager;
                break;
        }

        return $result;
    }
}