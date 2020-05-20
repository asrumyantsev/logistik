<?php

namespace Enot\AdminBundle\Controller;

use DateTime;
use Enot\ApiBundle\Entity\Driver;
use Enot\ApiBundle\Entity\Partner;
use Enot\ApiBundle\Entity\Report;
use Enot\ApiBundle\Entity\Transportation;
use Enot\ApiBundle\Entity\Vehicle;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Swift_Mailer;
use Swift_Message;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;


class DefaultController extends BaseController
{
    /**
     * @Template()
     * @Route("/", name="enot_admin_homepage")
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \Exception
     */
    public function indexAction()
    {
        $user = $this->getUserEntity();
        $partner = null;
        if (!$this->isGranted("ROLE_SUPER_ADMIN")) {
            $partner = $this->getDoctrine()->getRepository("EnotApiBundle:Partner")->findOneBy(['user' => $user]);
        }

        $entityManager = $this->get("doctrine.orm.default_entity_manager");
        $repository = $entityManager->getRepository("EnotApiBundle:Transportation");

        $date = new DateTime();
        $date->setTime(0, 0, 0);

        $params = $this->getParams([
            "partner_count" => count($entityManager->getRepository("EnotApiBundle:Partner")->findAll()),
            "vehicle_count" => count($entityManager->getRepository("EnotApiBundle:Vehicle")->findByParams($partner)),
            "driver_count" => count($entityManager->getRepository("EnotApiBundle:Driver")->findByParams($partner)),
            "waiting_count" => $repository->findByParams($partner, null, null, ["event" => 1, "assign" => false]),
            "assign_count" => $repository->findByParams($partner, null, null, ["event" => 1]),
            "loaded_count" => $repository->findByParams($partner, null, null, ["event" => 3]),
            "departure_count" => $repository->findByParams($partner, null, null, ["event" => 4]),
            "delivered_count" => $repository->findByParams($partner, null, null, ["event" => 5]),
            "empty_count" => $repository->findByParams($partner, null, null, ["event" => 6]),
            "completed_count" => $repository->findByParams($partner, null, null, ["event" => 7, "dateFrom" => $date->format("d.m.Y")]),
            'transportation_count' => count($repository->findByParams($partner))
        ]);
        return $this->render('EnotAdminBundle:Default:index.html.twig', $params);
    }

    /**
     * @Template()
     * @Route("/rate", name="enot_admin_rate")
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function rateAction()
    {
        return $this->render('EnotAdminBundle:Default:rate.html.twig', $this->getParams([]));
    }

    /**
     * @Template()
     * @Route("/report", name="enot_admin_report")
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function reportAction()
    {
        $manager = $this->get("doctrine.orm.default_entity_manager");

        $user = $this->getUserEntity();
        $partner = null;
        if (!$this->isGranted("ROLE_SUPER_ADMIN")) {
            $partner = $this->getDoctrine()->getRepository("EnotApiBundle:Partner")->findOneBy(['user' => $user]);
        }
        $vehicles = $manager->getRepository("EnotApiBundle:Vehicle")->findByParams($partner);

        $drivers = $manager->getRepository("EnotApiBundle:Driver")->findByParams($partner);

        $query = $manager->getRepository("EnotApiBundle:Report")->createQueryBuilder("r")
            ->leftJoin("r.vehicle", "v");

        if ($partner) {
            $query->where("r.partner = :partner")->setParameter("partner", $partner);
        }

        return $this->render('EnotAdminBundle:Default:report.html.twig', $this->getParams([
            'vehicles' => $vehicles,
            'drivers' => $drivers,
            'reports' => $query->getQuery()->getResult()
        ]));
    }

    /**
     * @Route("/return_user", name="enot_admin_return_user")
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function returnUserAction(Request $request)
    {
        if ($request->getSession()->get("__old_user")) {
            $user = $this->getDoctrine()->getRepository("EnotApiBundle:User")->find($request->getSession()->get("__old_user"));
            $token = new UsernamePasswordToken($user, null, 'main', $user->getRoles());
            $this->get('security.token_storage')->setToken($token);

            // If the firewall name is not main, then the set value would be instead:
            // $this->get('session')->set('_security_XXXFIREWALLNAMEXXX', serialize($token));
            $this->get('session')->set('_security_main', serialize($token));

            // Fire the login event manually
            $event = new InteractiveLoginEvent($request, $token);
            $this->get("event_dispatcher")->dispatch("security.interactive_login", $event);
            $request->getSession()->remove("__old_user");
        }

        return $this->redirectToRoute("enot_admin_homepage");
    }

    /**
     * @Template()
     * @Route("/create_report", name="enot_admin_create_report")
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function createReportAction(Request $request)
    {
        $query = $this->get("doctrine.orm.default_entity_manager")
            ->getRepository("EnotApiBundle:Transportation")
            ->createQueryBuilder("t")
            ->join("t.vehicle", "v")
            ->leftJoin("v.partner", "p")
            ->join("t.driver", "d")
            ->orderBy("t.id", "DESC");
        if ($request->request->get("vehicle")) {
            $query->andWhere("v.id = :vehicleId")->setParameter("vehicleId", $request->request->get("vehicle"));
        }
        if ($request->request->get("driver")) {
            $query->andWhere("d.id = :driverId")->setParameter("driverId", $request->request->get("driver"));
        }
        if ($request->request->get("dateFrom")) {
            $query->andWhere("t.createdAt > :dateFrom")->setParameter("dateFrom", $request->request->get("dateFrom"));
        }
        if ($request->request->get("dateTo")) {
            $query->andWhere("t.createdAt > :dateFrom")->setParameter("dateFrom", $request->request->get("dateFrom"));
        }

        if (!$this->isGranted("ROLE_SUPER_ADMIN")) {
            /** @var Partner $partner */
            $partner = $this->getDoctrine()->getRepository("EnotApiBundle:Partner")->findOneBy([
                "user" => $this->getUserEntity()
            ]);

            if ($partner) {
                $query->andWhere("p.id = :partner")
                    ->setParameter("partner", $partner->getId());
            }
        }

        $transportations = $query->getQuery()->getResult();

        $reports = [];
        return $this->render('EnotAdminBundle:Default:createReport.html.twig', $this->getParams([
            'transportations' => $transportations,
            'reports' => $reports
        ]));
    }

    /**
     * @Route("/form_report", name="enot_admin_form_report")
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     * @throws \Doctrine\ORM\ORMException
     * @throws \Exception
     */
    public function formReportAction(Request $request)
    {
        $manager = $this->get("doctrine.orm.default_entity_manager");
        $partner = null;
        if (!$this->isGranted("ROLE_SUPER_ADMIN")) {
            /** @var Partner $partner */
            $partner = $this->getDoctrine()->getRepository("EnotApiBundle:Partner")->findOneBy([
                "user" => $this->getUserEntity()
            ]);
        }

        /** @var Transportation[] $transportations */
        $transportations = $manager->getRepository("EnotApiBundle:Transportation")->findByParams($partner,
            $request->request->get("vehicle"),
            $request->request->get("driver"),
            [
                "order" => ["id", "DESC"],
                "dateFrom" => $request->request->get("dateFrom"),
                "dateTo" => $request->request->get("dateTo"),
            ]);

        $values = [];

        foreach ($transportations as $transportation) {
            $values[] = [
                $transportation->getVehicle() && $transportation->getVehicle()->getPartner() ? $transportation->getVehicle()->getPartner()->getName() : "",
                $transportation->getDriver() ? $transportation->getDriver()->getName() : "",
                $transportation->getFromAddress(),
                $transportation->getToAddress(),
                $transportation->getDeliveryUnladenAddress(),
                $transportation->getVehicle() ? $transportation->getVehicle()->getName() : "",
                $transportation->getContainerNumber(),
                "Обычный",
                $transportation->getPrice(),
                $transportation->getEstimatedPrice(),
                $transportation->isPassedInvoice() ? "Да" : "Нет"
            ];
        }
        $date = new \DateTime();

        $this->get("enot_admin.excel_manager")->createAndSave([
            ["title" => "Партнер", "options" => ["width" => 10]],
            ["title" => "ФИО"],
            ["title" => "Откуда"],
            ["title" => "Куда"],
            ["title" => "Адрес сдачи порожнего"],
            ["title" => "Номер машины"],
            ["title" => "Номер груза"],
            ["title" => "Тип перевозки"],
            ["title" => "Стоимость"],
            ["title" => "Примерная стоимость"],
            ["title" => "Документы"],
        ], $values, $date->format("d_m_Y_H_i_s"));

        $report = new Report();
        $report->setCreateAt($date);

        if ($request->request->get("dateFrom")) {
            $report->setDateFrom(DateTime::createFromFormat('d.m.Y', $request->request->get("dateFrom")));
        }

        if ($request->request->get("dateTo")) {
            $report->setDateTo(DateTime::createFromFormat('d.m.Y', $request->request->get("dateTo")));
        }

        $report->setName($date->format("d_m_Y_H_i_s"));
        $report->setFileName($date->format("d_m_Y_H_i_s") . ".xlsx");


        if ($request->request->get("vehicle")) {
            /** @var Vehicle $vehicle */
            $vehicle = $manager->getRepository("EnotApiBundle:Vehicle")->find($request->request->get("vehicle"));
            $report->setVehicle($vehicle);
        }
        if ($request->request->get("driver")) {
            /** @var Driver $driver */
            $driver = $manager->getRepository("EnotApiBundle:Driver")->find($request->request->get("driver"));
            $report->setDriver($driver);
        }

        $report->setPartner($partner);

        $manager->persist($report);
        $manager->flush($report);

        return $this->redirectToRoute("enot_admin_report");
    }

    /**
     * @Route("/download_report/{id}", name="enot_admin_download_report")
     * @param $id
     * @return void
     */
    public function downloadReportAction($id)
    {
        $manager = $this->get("doctrine.orm.default_entity_manager");

        /** @var Report $report */
        $report = $manager->getRepository("EnotApiBundle:Report")->find($id);

        $fileLocation = "./reports/" . $report->getFileName();

        header('Content-Description: File Transfer');
        header("Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet");
        header("Content-Disposition: attachment; filename=\"" . basename($fileLocation) . "\"");
        header("Content-Transfer-Encoding: binary");
        header("Expires: 0");
        header("Pragma: public");
        header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
        header('Content-Length: ' . filesize($fileLocation)); //Remove

        ob_clean();
        flush();

        readfile($fileLocation);
    }

    /**
     * @Route("/delete_report/{id}", name="enot_admin_delete_report")
     * @param $id
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function deleteReportAction($id)
    {
        $manager = $this->get("doctrine.orm.default_entity_manager");

        /** @var Report $report */
        $report = $manager->getRepository("EnotApiBundle:Report")->find($id);

        $manager->remove($report);
        $manager->flush($report);

        return $this->redirectToRoute("enot_admin_report");

    }
}
