<?php
/**
 *
 */

namespace Enot\NotificationBundle\Services;


use Enot\ApiBundle\Services\Main\HttpClientInterface;
use Enot\NotificationBundle\Model\NotificationManagerInterface;
use Swift_Mailer;
use Twig_Environment;

class EmailManager implements NotificationManagerInterface
{
    /** @var HttpClientInterface|null */
    private $httpClient = null;

    /** @var Twig_Environment|null */
    private $twig = null;

    /** @var Swift_Mailer|null */
    private $swiftMailer = null;

    /**
     * @param HttpClientInterface $httpClient
     * @param Twig_Environment $twig
     * @param Swift_Mailer $swiftMailer
     */
    public function __construct(HttpClientInterface $httpClient,
                                Twig_Environment $twig,
                                Swift_Mailer $swiftMailer)
    {
        $this->httpClient = $httpClient;
        $this->twig = $twig;
        $this->swiftMailer = $swiftMailer;
    }
    public function send($receiver, $title, $pushMessage, $data = [])
    {
        $swiftMessage = (new \Swift_Message($title))
            ->setFrom("system@deltransmsk.ru")
            ->setTo($receiver)
            ->setBody($pushMessage);
        $this->swiftMailer->send($swiftMessage);
    }

    /**
     * @param $receiver
     * @param $title
     * @param $template
     * @param array $data
     * @throws \Throwable
     * @throws \Twig_Error_Loader
     * @throws \Twig_Error_Syntax
     */
    public function sendByTemplate($receiver, $title, $template, $data = [])
    {
        $swiftMessage = (new \Swift_Message($title))
            ->setFrom(["system@deltransmsk.ru"])
            ->setTo([$receiver])
            ->setBody(
                $this->twig->createTemplate('Hi {{ name }}!')->render(['name' => 'simon']),
                'text/html'
            );

        $this->swiftMailer->send($swiftMessage);
        echo "send" . PHP_EOL;
    }
}