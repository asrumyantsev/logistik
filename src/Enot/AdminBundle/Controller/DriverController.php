<?php

namespace Enot\AdminBundle\Controller;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;
use Enot\ApiBundle\Entity\AuthorizationVehicleDriver;
use Enot\ApiBundle\Entity\Driver;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use FOS\RestBundle\Controller\Annotations as Rest;


class DriverController extends BaseController
{
    /**
     * @Template()
     * @Route("/", name="enot_admin_driver")
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function indexAction(Request $request)
    {
        $partner = null;
        $user = $this->getUserEntity();

        if($request->query->get("partner")) {
            $partner = $this->getDoctrine()->getRepository("EnotApiBundle:Partner")->find($request->query->get("partner"));
        }

        if(!in_array("ROLE_SUPER_ADMIN", $user->getRoles())) {
            $partner = $this->getDoctrine()->getRepository("EnotApiBundle:Partner")->findOneBy(['user' => $user]);
        }

        $drivers = $this->getDoctrine()->getRepository("EnotApiBundle:Driver")->findByParams($partner, null, [
            "onLine" => $request->query->get("status")
        ]);

        /** @var EntityRepository $repository */
        $repository = $this->getDoctrine()->getRepository("EnotApiBundle:Partner");
        /** @var QueryBuilder $query */
        $query = $repository->createQueryBuilder("p");

        $partners = $query->getQuery()->getResult();

        return $this->render('EnotAdminBundle:Driver:index.html.twig', $this->getParams(['drivers' => $drivers, 'partners' => $partners]));
    }


    /**
     *
     * @Rest\Get("/switch/{id}", name="enot_admin_driver_switch")
     * @param $id
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \Exception
     */
    public function switchAction($id)
    {
        $manager = $this->get("doctrine.orm.default_entity_manager");
        /** @var Driver $driver */
        $driver = $manager->getRepository("EnotApiBundle:Driver")->findOneById($id);
        if($driver->getCurrentStatus()) {
            $status = $driver->getCurrentStatus();
            $status->setEndAt(new \DateTime());
            $manager->persist($status);
        } else {
            /** @var AuthorizationVehicleDriver $status */
            $status = $manager->getRepository("EnotApiBundle:AuthorizationVehicleDriver")->getLastDriverStatus($driver);
            $status->setEndAt(null);
            $manager->persist($status);
        }

        $manager->flush();

        return $this->redirectToRoute("enot_admin_driver");
    }
}
