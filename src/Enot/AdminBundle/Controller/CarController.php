<?php

namespace Enot\AdminBundle\Controller;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;
use Enot\ApiBundle\Entity\Partner;
use Enot\ApiBundle\Repository\VehicleRepository;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;


class CarController extends BaseController
{
    /**
     * @Template()
     * @Route("/", name="enot_admin_car")
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function indexAction(Request $request)
    {
        /** @var VehicleRepository $repository */
        $repository = $this->getDoctrine()->getRepository("EnotApiBundle:Vehicle");
        $partner = null;
        if($request->query->get("partner")) {
            /** @var Partner $partner */
            $partner = $this->getDoctrine()->getRepository("EnotApiBundle:Partner")->findOneBy([
                "user" => $this->getUserEntity()
            ]);
        }

        if(!$this->isGranted("ROLE_SUPER_ADMIN")) {
            /** @var Partner $partner */
            $partner = $this->getDoctrine()->getRepository("EnotApiBundle:Partner")->findOneBy([
                "user" => $this->getUserEntity()
            ]);
        }

        $cars = $repository->findByParams($partner);

        /** @var EntityRepository $repository */
        $repository = $this->getDoctrine()->getRepository("EnotApiBundle:Partner");
        /** @var QueryBuilder $query */
        $query = $repository->createQueryBuilder("p");

        $partners = $query->getQuery()->getResult();

        return $this->render('EnotAdminBundle:Car:index.html.twig', $this->getParams(['cars' => $cars, 'partners' => $partners]));
    }
}
