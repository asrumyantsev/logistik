<?php
/**
 *
 */

namespace Enot\NotificationBundle\Model;


interface SendSmsInterface
{
    public function sendSms($phone, $message);
}