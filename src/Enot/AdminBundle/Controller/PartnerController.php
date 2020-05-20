<?php

namespace Enot\AdminBundle\Controller;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;
use Enot\ApiBundle\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;


class PartnerController extends BaseController
{
    /**
     * @Template()
     * @Route("/", name="enot_admin_partner")
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function indexAction()
    {
        /** @var EntityRepository $repository */
        $repository = $this->getDoctrine()->getRepository("EnotApiBundle:Partner");
        /** @var QueryBuilder $query */
        $query = $repository->createQueryBuilder("p");

        $partners = $query->getQuery()->getResult();

        return $this->render('EnotAdminBundle:Partner:index.html.twig', $this->getParams(['partners' => $partners]));
    }

    /**
     * @Template()
     * @Route("/edit/{id}", name="enot_admin_partner_edit")
     * @param Request $request
     * @param $id
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function editAction(Request $request, $id)
    {
        $partner = $this->getDoctrine()->getRepository("EnotApiBundle:Partner")->find($id);
        if ($request->getMethod() == Request::METHOD_POST) {
            $partner->setPriority($request->request->get("priority"));
            $manager = $this->get("doctrine.orm.default_entity_manager");
            /** @var User $user */
            $user = $partner->getUser();
            $emailSend = false;
            if ($request->request->get("password")) {
                $user->setPlainPassword($request->request->get("password"));
                $emailSend = true;
                $userManager = $this->container->get('fos_user.user_manager');
                $userManager->updatePassword($user);
            }
            if ($request->request->get("email") && $request->request->get("email") != $user->getEmail()) {
                $user->setEmail($request->request->get("email"));
                $emailSend = true;
            }

            if ($emailSend) {
                $mailer = $this->get("enot_notification.email_manager");
                $message = "Изменение данных о партнере: \n Email: " . $user->getEmail();
                if($request->request->get("password")) {
                    $message .= "\nПароль: " . $request->request->get("password");
                }

                $mailer->send($user->getEmail(), "Изменение данных партнера", $message);
            }
            $user->setEnabled(true);
            $manager->persist($user);
            $manager->persist($partner);
            $manager->flush();
            return $this->redirectToRoute("enot_admin_partner");
        }
        return $this->render('EnotAdminBundle:Partner:edit.html.twig', $this->getParams(['partnerEntity' => $partner]));
    }

    /**
     * @Route("/under/{id}", name="enot_admin_partner_under_user")
     * @param Request $request
     * @param $id
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function underAction(Request $request, $id)
    {
        $partner = $this->getDoctrine()->getRepository("EnotApiBundle:Partner")->find($id);
        $oldUserId = $this->getUserEntity()->getId();
        $token = new UsernamePasswordToken($partner->getUser(), null, 'main', $partner->getUser()->getRoles());
        $this->get('security.token_storage')->setToken($token);

        $this->get('session')->set('_security_main', serialize($token));

        // Fire the login event manually
        $event = new InteractiveLoginEvent($request, $token);
        $this->get("event_dispatcher")->dispatch("security.interactive_login", $event);
        $request->getSession()->set("__old_user", $oldUserId);
        return $this->redirectToRoute("enot_admin_homepage");
    }
}
