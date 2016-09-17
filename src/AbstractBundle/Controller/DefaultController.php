<?php

namespace AbstractBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

class DefaultController extends Controller
{
    /**
     * @Route("/")
     */
    public function indexAction()
    {
        if($this->container->get('security.context')->isGranted('ROLE_ADMIN')) {
            return $this->redirectToRoute('users_index');
        } elseif($this->container->get('security.context')->isGranted('ROLE_PATIENT')) {
            return $this->redirectToRoute('patient_reports');
        } else {
            return $this->redirectToRoute('fos_user_security_login');
        }
        
    }
}
