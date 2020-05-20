<?php

namespace Enot\AdminBundle\Controller;

use DateTime;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;
use Enot\ApiBundle\Entity\Partner;
use Enot\ApiBundle\Entity\Transportation;
use FOS\RestBundle\Controller\Annotations as Rest;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;


class TransportationController extends BaseController
{
    /**
     * @Template()
     * @Route("/", name="enot_admin_transportation")
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function indexAction(Request $request)
    {
        /** @var EntityRepository $repository */
        $repository = $this->getDoctrine()->getRepository("EnotApiBundle:Transportation");
        /** @var QueryBuilder $query */
        $query = $repository->createQueryBuilder("p")->orderBy("p.id", "DESC")
            ->leftJoin("p.vehicle", "v")
            ->leftJoin("v.partner", "pp");
        $query->andWhere("p.deletedAt is null");

        if ($request->query->get("partner")) {
            $query->where("pp.id = :partner")
                ->setParameter("partner", $request->query->get("partner"));
        }

        if ($request->query->get("status")) {
            $query->andWhere("p.lastEvent = :status")
                ->setParameter("status", $request->query->get("status") == 8 ? 0 :$request->query->get("status"));
        }

        if ($request->query->get("dateFrom")) {
            $query->andWhere("p.dateStart >= :dateFrom")
                ->setParameter("dateFrom", DateTime::createFromFormat('d.m.Y', $request->query->get("dateFrom")));
        }

        if ($request->query->get("dateTo")) {
            $query->andWhere("p.dateStart <= :dateTo")
                ->setParameter("dateTo", DateTime::createFromFormat('d.m.Y', $request->query->get("dateTo")));
        }

        if(!$this->isGranted("ROLE_SUPER_ADMIN")) {
            /** @var Partner $partner */
            $partner = $this->getDoctrine()->getRepository("EnotApiBundle:Partner")->findOneBy([
                "user" => $this->getUserEntity()
            ]);

            if($partner) {
                $query->andWhere("pp.id = :partner")
                    ->setParameter("partner", $partner->getId());
            }
        }

        $transportations = $query->setMaxResults(100)->getQuery()->getResult();

        /** @var EntityRepository $repository */
        $repository = $this->getDoctrine()->getRepository("EnotApiBundle:Partner");
        /** @var QueryBuilder $query */
        $query = $repository->createQueryBuilder("p");
        $partners = $query->getQuery()->getResult();
        return $this->render('EnotAdminBundle:Transportation:index.html.twig', $this->getParams(['transportations' => $transportations, 'partners' => $partners]));
    }

    /**
     *
     * @Rest\Get("/get_list", name="enot_admin_transportation_get_list")
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function getListAction(Request $request)
    {
        $limit = $request->query->get("limit", 100);
        /** @var EntityRepository $repository */
        $repository = $this->getDoctrine()->getRepository("EnotApiBundle:Transportation");
        /** @var QueryBuilder $query */
        $query = $repository->createQueryBuilder("p")->orderBy("p.id", "DESC")
            ->leftJoin("p.vehicle", "v")
            ->leftJoin("v.partner", "pp");
//        $query->where("p.completedAt is null");
        $query->andWhere("p.deletedAt is null");
        if ($request->query->get("partner")) {
            $query->where("pp.id = :partner")
                ->setParameter("partner", $request->query->get("partner"));
        }

        if ($request->query->get("status")) {
            $query->andWhere("p.lastEvent = :status")
                ->setParameter("status", $request->query->get("status") == 8 ? 0 :$request->query->get("status"));
        }

        if ($request->query->get("dateFrom")) {
            $query->andWhere("p.dateStart >= :dateFrom")
                ->setParameter("dateFrom", DateTime::createFromFormat('d.m.Y', $request->query->get("dateFrom")));
        }

        if ($request->query->get("dateTo")) {
            $query->andWhere("p.dateStart <= :dateTo")
                ->setParameter("dateTo", DateTime::createFromFormat('d.m.Y', $request->query->get("dateTo")));
        }

        if(!$this->isGranted("ROLE_SUPER_ADMIN")) {
            /** @var Partner $partner */
            $partner = $this->getDoctrine()->getRepository("EnotApiBundle:Partner")->findOneBy([
                "user" => $this->getUserEntity()
            ]);

            if($partner) {
                $query->andWhere("pp.id = :partner")
                    ->setParameter("partner", $partner->getId());
            }
        }

        $transportations = $query->setMaxResults($limit)->getQuery()->getResult();
        return $this->render('EnotAdminBundle:Transportation:part.html.twig', ['transportations' => $transportations]);
    }

    /**
     *
     * @Rest\Get("/cancel/{id}", name="enot_admin_transportation_cancel")
     * @param $id
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function cancelAction($id)
    {
        $manager = $this->get("enot_api.services.transportation_manager");
        /** @var Transportation $transportation */
        $transportation = $manager->getRepository()->find($id);
        $manager->cancel($transportation);

        return $this->redirectToRoute("enot_admin_transportation");
    }

    /**
     * @Route("/find", name="enot_admin_transportation_find")
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function getAction(Request $request)
    {
        $transportation = $this->getDoctrine()->getRepository("EnotApiBundle:Transportation")->findOneBy([
            "containerNumber" => $request->query->get("query"),
            "deletedAt" => null
        ]);
        $events = [];
        if($transportation) {
            $events = $this->getDoctrine()->getRepository("EnotApiBundle:TransportationEventHistory")->findBy(['transportation' => $transportation]);
        }
        return $this->render('EnotAdminBundle:Transportation:transportation.html.twig', $this->getParams(['transportation' => $transportation, 'events' => $events]));
    }
}
