<?php
/**
 * ...
 */

namespace Enot\ApiBundle\EventSubscriber;


use Enot\ApiBundle\Event\TransportationFieldEvent;
use Enot\NotificationBundle\Services\NotificationManager;
use Enot\NotificationBundle\Services\PushManager;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class TransportationFieldEventSubscriber implements EventSubscriberInterface
{
    const TITLE_PUSH = 'Заявка';
    private $messages = [
        'field.vehicle' => 'Изменен автомобиль',
        'field.trailer' => 'Изменен прицеп',
        'field.container_type' => 'Изменен тип контейнера',
        'field.container_number' => 'Изменен номер контейнера',
        'field.container_real_size' => 'Изменен размер контейнера',
        'field.from_address' => 'Изменен адрес получения груза',
        'field.from_address_gis' => 'Изменены координаты адреса получения груза',
        'field.to_address' => 'Изменен адрес назначения',
        'field.to_address_gis' => 'Изменены координаты адреса назначения',
        'field.delivery_unladen_address' => 'Изменен адрес сдачи порожнего',
        'field.delivery_unladen_address_gis' => 'Изменены координаты адреса сдачи порожнего',
        'field.date_start' => 'Изменена дата',
        'field.description' => 'Изменено описание',
        'field.price' => 'Изменена цена',
        'field.estimated_price' => 'Изменена приблизительная цена',
    ];

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
            TransportationFieldEvent::VEHICLE => 'onFieldChanged',
            TransportationFieldEvent::TRAILER => 'onFieldChanged',
            TransportationFieldEvent::CONTAINER_TYPE => 'onFieldChanged',
            TransportationFieldEvent::CONTAINER_NUMBER => 'onFieldChanged',
            TransportationFieldEvent::CONTAINER_REAL_SIZE => 'onFieldChanged',
            TransportationFieldEvent::FROM_ADDRESS => 'onFieldChanged',
            TransportationFieldEvent::FROM_ADDRESS_GIS => 'onFieldChanged',
            TransportationFieldEvent::TO_ADDRESS => 'onFieldChanged',
            TransportationFieldEvent::TO_ADDRESS_GIS => 'onFieldChanged',
            TransportationFieldEvent::DELIVERY_UNLADEN_ADDRESS => 'onFieldChanged',
            TransportationFieldEvent::DELIVERY_UNLADEN_ADDRESS_GIS => 'onFieldChanged',
            TransportationFieldEvent::DATE_START => 'onFieldChanged',
            TransportationFieldEvent::DESCRIPTION => 'onFieldChanged',
            TransportationFieldEvent::PRICE => 'onFieldChanged',
            TransportationFieldEvent::ESTIMATED_PRICE => 'onFieldChanged',
        ];
    }

    /**
     * @param TransportationFieldEvent $event
     * @return \Enot\ApiBundle\Entity\Driver
     */
    private function getDriver(TransportationFieldEvent $event)
    {
        return $event->getTransportation()->getDriver();
    }

    public function onFieldChanged(TransportationFieldEvent $event)
    {
        $message = $this->messages[$event->getField()];

        if(!$this->getDriver($event)) {
            return;
        }

        /** @var PushManager $pushManager */
        $pushManager = $this->notificationManager->get(NotificationManager::NOTIFICATION_PUSH);
        $pushManager->send(null, self::TITLE_PUSH, $message, [
            'typeId' => $event->getField(),
            'filters' => [
                'userId' => $this->getDriver($event)->getPhone()
            ],
            'content_available' => true
        ]);
    }
}