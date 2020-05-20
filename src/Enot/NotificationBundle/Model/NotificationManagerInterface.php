<?php
/**
 *
 */

namespace Enot\NotificationBundle\Model;


interface NotificationManagerInterface
{
    public function send($receiver, $title, $pushMessage, $data = []);
}