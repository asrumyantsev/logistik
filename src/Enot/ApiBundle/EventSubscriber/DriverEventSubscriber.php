<?php
/**
 * ...
 */

namespace Enot\ApiBundle\EventSubscriber;


use Enot\ApiBundle\Event\DriverEvent;
use Enot\NotificationBundle\Services\NotificationManager;
use Enot\NotificationBundle\Services\PushManager;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class DriverEventSubscriber implements EventSubscriberInterface
{
    const TITLE_PUSH = 'Заявка';
    const MSG_ATTACHED = 'Вам назначена новая заявка';
    const MSG_DETACHED = 'Вас сняли с заявки';

    /**
     * @var NotificationManager
     */
    private $notificationManager;

    /**
     * DriverEventSubscriber constructor.
     * @param NotificationManager $notificationManager
     */
    public function __construct(NotificationManager $notificationManager)
    {

        $this->notificationManager = $notificationManager;
    }

    /**
     * Returns an array of event names this subscriber wants to listen to.
     *
     * The array keys are event names and the value can be:
     *
     *  * The method name to call (priority defaults to 0)
     *  * An array composed of the method name to call and the priority
     *  * An array of arrays composed of the method names to call and respective
     *    priorities, or 0 if unset
     *
     * For instance:
     *
     *  * array('eventName' => 'methodName')
     *  * array('eventName' => array('methodName', $priority))
     *  * array('eventName' => array(array('methodName1', $priority), array('methodName2')))
     *
     * @return array The event names to listen to
     */
    public static function getSubscribedEvents()
    {
        return [
            DriverEvent::ATTACHED => 'onDriverAttached',
            DriverEvent::DETACHED => 'onDriverDetached'
        ];
    }

    /**
     * @param DriverEvent $event
     * @return \Enot\ApiBundle\Entity\Driver
     */
    public function getOldDriver(DriverEvent $event)
    {
        return $event->getTransportation()->getDriver();
    }

    /**
     * @param DriverEvent $event
     * @return \Enot\ApiBundle\Entity\Driver
     */
    public function getNewDriver(DriverEvent $event)
    {
        return $event->getDriver();
    }

    public function onDriverAttached(DriverEvent $event)
    {
        if(!$this->getNewDriver($event) || !$this->getOldDriver($event)) {
            return;
        }
        /** @var PushManager $pushManager */
        $pushManager = $this->notificationManager->get(NotificationManager::NOTIFICATION_PUSH);
        $pushManager->send(null, self::TITLE_PUSH, self::MSG_ATTACHED, [
            'typeId' => DriverEvent::ATTACHED,
            'content' => [
                'driverId' => $this->getNewDriver($event)->getId()
            ],
            'filters' => [
                'userId' => $this->getNewDriver($event)->getPhone()
            ],
            'content_available' => true
        ]);
    }

    public function onDriverDetached(DriverEvent $event)
    {
        if(!$this->getNewDriver($event) || !$this->getOldDriver($event)) {
            return;
        }
        /** @var PushManager $pushManager */
        $pushManager = $this->notificationManager->get(NotificationManager::NOTIFICATION_PUSH);
        $pushManager->send(null, self::TITLE_PUSH, self::MSG_DETACHED, [
            'typeId' => DriverEvent::DETACHED,
            'content' => [
                'driverId' => $this->getOldDriver($event)->getId()
            ],
            'filters' => [
                'userId' => $this->getNewDriver($event)->getPhone()
            ],
            'content_available' => true
        ]);
    }
}