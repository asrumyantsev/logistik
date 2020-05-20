<?php

namespace Enot\LogBundle\EventSubscriber;

use Enot\ApiBundle\Entity\User;


use Enot\LogBundle\Services\LogInterface;
use Enot\LogBundle\Utils\Logger;
use Psr\Container\ContainerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

use Symfony\Component\HttpKernel\Event\FilterControllerEvent;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;

class RequestResponseSubscriber implements EventSubscriberInterface
{
    /**
     * @var TokenStorage
     */
    private $tokenStorage;

    /**
     * @var Logger
     */
    private $logger;

    /** @var string */
    private $requestId = null;

    /**
     * @var ContainerInterface
     */
    private $container;

    public function __construct(ContainerInterface $container, TokenStorage $tokenStorage, Logger $logger)
    {
        $this->tokenStorage = $tokenStorage;
        $this->logger = $logger;
        $this->container = $container;
    }

    /**
     * Logging any request for that Controllers who implements LogInterface
     *
     * @param FilterControllerEvent $event
     * @throws \Enot\LogBundle\Exceptions\LoggerException
     * @throws \Exception
     */
    public function onRequest(FilterControllerEvent $event)
    {
        $controller = $this->getControllerNewInstance($event->getRequest()->attributes->get('_controller'));
        if (!$controller instanceof LogInterface) {
            return;
        }

        //generate requestId
        $this->requestId = Logger::getGuid();

        //getting request body as array
        $context = $event->getRequest()->request->all();

        //getting user
        $user = $this->getUser();

        //parse domain as ControllerName
        $domain = $this->parseControllerName($event->getRequest()->attributes->get('_controller'));

        //parse event as ActionName
        $logEvent = $this->parseActionName($event->getRequest()->attributes->get('_controller'));

        //saving user and request id
        $this->logger->setParameters($user, $this->requestId);

        //make a log
        $this->logger->log($context, $logEvent, $domain, $this->logger::CATEGORY_REQUEST);
    }

    /**
     * Logging any response
     *
     * @param FilterResponseEvent $event
     * @throws \Enot\LogBundle\Exceptions\LoggerException
     * @throws \Exception
     */
    public function onResponse(FilterResponseEvent $event)
    {
        $controller = $this->getControllerNewInstance($event->getRequest()->attributes->get('_controller'));
        if (!$controller instanceof LogInterface) {
            return;
        }

        //add requestId to existing response if it possible
        try {
            //getting
            $response = $event->getResponse();
            $context = json_decode($response->getContent());

            //changing
            $context->request_id = $this->requestId;

            //setting
            $response->setContent(json_encode($context));
            $event->setResponse($response);
        } catch (\Exception $exception) {
            $context = $event->getResponse()->getContent();
        }

        //getting user
        $user = $this->getUser();

        //parse domain as ControllerName
        $domain = $this->parseControllerName($event->getRequest()->attributes->get('_controller'));

        //parse event as ActionName
        $logEvent = $this->parseActionName($event->getRequest()->attributes->get('_controller'));

        //saving user and request id
        $this->logger->setParameters($user, $this->requestId);

        //make a log
        $this->logger->log((array)$context, $logEvent, $domain, $this->logger::CATEGORY_RESPONSE);
    }

    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::CONTROLLER => 'onRequest',
            KernelEvents::RESPONSE => 'onResponse'
        ];
    }

    /**
     * @param $source
     * @return mixed
     * @throws \Exception
     */
    private function getControllerNewInstance($source)
    {
        if (!$source) {
            //Exception if controller action not found (this exception has already been processed!!!)
            throw new \Exception();
        }

        if (strpos($source, '::')) {
            $controllerClass = explode('::', $source)[0];
        } elseif (strpos($source, ':')) {
            $controllerClass = explode(':', $source)[0];
        } else {
            $controllerClass = $source;
        }

        if ($this->container->has($controllerClass)) {
            $result = $this->container->get($controllerClass);
        } else {
            $result = new $controllerClass;
        }

        return $result;
    }

    /**
     * @param $source
     * @return mixed
     */
    private function parseControllerName($source)
    {
        $controllerPathArray = explode('::', $source);
        $controllerPathArray = explode("\\", $controllerPathArray[0]);
        $controllerName = array_pop($controllerPathArray);

        return $controllerName;
    }

    /**
     * @param $source
     * @return mixed
     */
    private function parseActionName($source)
    {
        $controllerPathArray = explode('::', $source);
        $actionName = array_pop($controllerPathArray);

        return $actionName;
    }

    /**
     * @return User|null
     */
    private function getUser()
    {
        //getting user
        $user = null;
        $token = $this->tokenStorage->getToken();
        if (null !== $token) {
            if ($token->getUser() instanceof User) {
                $user = $token->getUser();
            }
        }

        return $user;
    }

}