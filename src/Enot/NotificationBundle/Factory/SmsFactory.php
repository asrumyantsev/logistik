<?php
/**
 *
 */

namespace Enot\NotificationBundle\Factory;


use Enot\NotificationBundle\Model\Nexmo;
use Enot\NotificationBundle\Model\SmsRu;

class SmsFactory
{
    const RUSSIAN_PHONE_CODE = "RU";

    /** @var Nexmo */
    private $nexmo;

    /** @var SmsRu */
    private $smsRu;

    public function __construct(Nexmo $nexmo, SmsRu $smsRu)
    {
        $this->nexmo = $nexmo;
        $this->smsRu = $smsRu;
    }

    /**
     * @param string $code
     * @return Nexmo|SmsRu
     */
    public function get($code)
    {
        switch ($code) {
            case self::RUSSIAN_PHONE_CODE:
                return $this->smsRu;
            default:
                return $this->nexmo;
        }
    }
}