<?php

namespace Enot\ApiBundle\Controller;

use Enot\ApiBundle\Entity\User;
use Enot\ApiBundle\Services\Main\MasterException;
use Enot\ApiBundle\Utils\EnotError;
use Enot\LogBundle\Services\LogInterface;
use FOS\RestBundle\Controller\FOSRestController;
use Symfony\Component\HttpFoundation\Response;

class BaseController extends FOSRestController implements LogInterface
{
    /**
     * @return User|null
     */
    protected function getUserEntity(): ?User
    {
        $user = null;
        if ($this->getUser()) {
            $user = $this->getDoctrine()->getRepository('EnotApiBundle:User')->find($this->getUser());
        }

        return $user;
    }

    /**
     * @param $value
     * @return mixed
     * @throws MasterException
     */
    protected function checkRequire($value)
    {
        if (!$value && $value != false) {
            throw new MasterException(EnotError::WRONG_PARAMETERS, '', Response::HTTP_BAD_REQUEST);
        }

        return $value;
    }
}
