<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2019-06-19
 * Time: 17:16
 */

namespace Enot\AdminBundle\Controller;


use Enot\ApiBundle\Entity\User;
use FOS\RestBundle\Controller\FOSRestController;

class BaseController extends FOSRestController
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

    public function getParams($params)
    {
        $user = $this->getUserEntity();
        $partner = $this->getDoctrine()->getRepository('EnotApiBundle:Partner')->findOneBy([
            "user" => $user
        ]);

        return array_merge($params, ['user' => $user, 'partner' => $partner]);
    }
}