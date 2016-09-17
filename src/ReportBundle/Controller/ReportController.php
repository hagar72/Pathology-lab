<?php

namespace ReportBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use ReportBundle\Entity\Report;
use ReportBundle\Form\ReportType;
use ReportBundle\Entity\ReportParameter;
use Doctrine\Common\Collections\ArrayCollection;
use Swift_Attachment;
/**
 * Report controller.
 *
 * @Route("/reports")
 */
class ReportController extends Controller
{
    /**
     * Lists all Report entities.
     *
     * @Route("/", name="reports_index")
     * @Method("GET")
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();

        $reports = $em->getRepository('ReportBundle:Report')->findAll();

        return $this->render('report/index.html.twig', array(
            'reports' => $reports,
        ));
    }
    
    /**
     * Lists all Report entities.
     *
     * @Route("/my-reports", name="patient_reports")
     * @Method("GET")
     */
    public function myReportsAction()
    {
        $user = $this->get('security.context')->getToken()->getUser();
        $em = $this->getDoctrine()->getManager();
        $reports = $em->getRepository('ReportBundle:Report')->findBy(array('user' => $user->getId()));

        return $this->render('report/my_reports_list.html.twig', array(
            'reports' => $reports,
        ));
    }
    
    /**
     * Finds and displays a Report entity.
     *
     * @Route("/show-my-report/{id}", name="show_my_report")
     * @Method("GET")
     */
    public function showMyReportAction(Report $report)
    {   
        return $this->render('report/my_report.html.twig', array(
            'report' => $report,
        ));
    }
    
    /**
     * Creates a new Report entity.
     *
     * @Route("/pdf/{id}", name="get_pdf")
     * @Method({"GET"})
     */
    public function getPdf(Report $report)
    {   
        $html = $this->renderView('report/print.html.twig', array('report' => $report));
        $this->returnPDFResponseFromHTML($html, $report);
    }

    private function returnPDFResponseFromHTML($html, $report, $response = 'I'){
        
        //set_time_limit(30); uncomment this line according to your needs
        // If you are not in a controller, retrieve of some way the service container and then retrieve it
        //$pdf = $this->container->get("white_october.tcpdf")->create('vertical', PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
        //if you are in a controlller use :
        $pdf = $this->get("white_october.tcpdf")->create('vertical', PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
        $pdf->SetAuthor($report->getUser()->getUsername());
        $pdf->SetTitle($report->getName());
        $pdf->SetSubject($report->getName());
        $pdf->setFontSubsetting(true);
        $pdf->SetFont('helvetica', '', 11, '', true);
        $pdf->AddPage();
        
        $filename = $report->getId() . '-' . $report->getUser()->getUsername () . '-' . $report->getName();
        
        $pdf->writeHTMLCell($w = 0, $h = 0, $x = '', $y = '', $html, $border = 0, $ln = 1, $fill = 0, $reseth = true, $align = '', $autopadding = true);
        if('F' == $response) {
            $pdf->Output($this->get('kernel')->getRootDir() . '/../web/pdfs/' . $filename.".pdf", $response); // This will output the PDF as a file
            return $filename;
        } else {
            $pdf->Output($filename.".pdf", $response); // This will output the PDF as a response directly
        }
    }
    
    
    /**
     * Creates a new Report entity.
     *
     * @Route("/send/{id}", name="send_pdf")
     * @Method({"GET"})
     */
    public function sendPdf(Report $report)
    {   
        $html = $this->renderView('report/print.html.twig', array('report' => $report));
        $filename = $this->returnPDFResponseFromHTML($html, $report, /* response type */ 'F');
        
        $message = \Swift_Message::newInstance()
            ->setSubject($report->getName())
            ->setFrom(array($this->container->getParameter('mailer_user') => 'Patient report'))
            ->setTo($report->getUser()->getEmail())
            ->setBody(
                    'Hello ' . $report->getUser()->getUsername() . ', '.
                    "\n\nKindly find attached report: \"\n" . $report->getName() . '"'
            )
            ->attach(Swift_Attachment::fromPath($this->get('kernel')->getRootDir() . '/../web/pdfs/' . $filename.".pdf"))
        ;

        $this->get('mailer')->send($message);
        
        $this->addFlash('success', 'Report has been sent to ' . $report->getUser()->getEmail());
        return $this->redirectToRoute('patient_reports');
    }
    
    /**
     * Creates a new Report entity.
     *
     * @Route("/new", name="reports_new")
     * @Method({"GET", "POST"})
     */
    public function newAction(Request $request)
    {
        $report = new Report();
        $form = $this->createForm('ReportBundle\Form\ReportType', $report);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($report);
            $em->flush();

            return $this->redirectToRoute('reports_show', array('id' => $report->getId()));
        }

        return $this->render('report/new.html.twig', array(
            'report' => $report,
            'form' => $form->createView(),
        ));
    }

    /**
     * Finds and displays a Report entity.
     *
     * @Route("/{id}", name="reports_show")
     * @Method("GET")
     */
    public function showAction(Report $report)
    {
        $deleteForm = $this->createDeleteForm($report);

        return $this->render('report/show.html.twig', array(
            'report' => $report,
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Displays a form to edit an existing Report entity.
     *
     * @Route("/{id}/edit", name="reports_edit")
     * @Method({"GET", "POST"})
     */
    public function editAction(Request $request, Report $report)
    {
        $originalParameters = new ArrayCollection();

        // Create an ArrayCollection of the current Tag objects in the database
        foreach ($report->getReportParameters() as $reportParameter) {
            $originalParameters->add($reportParameter);
        }
        $deleteForm = $this->createDeleteForm($report);
        $editForm = $this->createForm('ReportBundle\Form\ReportType', $report);
        $editForm->handleRequest($request);

        if ($editForm->isSubmitted() && $editForm->isValid()) {
            $em = $this->getDoctrine()->getManager();
            
            foreach ($originalParameters as $reportParameter) {
                if (false === $report->getReportParameters()->contains($reportParameter)) {
                    // remove the report from the reportParameter
                    $reportParameter->setReport(null);
                    // Delete the report parameter
                    $em->remove($reportParameter);
                }
            }

            $em->persist($report);
            $em->flush();

            return $this->redirectToRoute('reports_edit', array('id' => $report->getId()));
        }

        return $this->render('report/edit.html.twig', array(
            'report' => $report,
            'edit_form' => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Deletes a Report entity.
     *
     * @Route("/{id}", name="reports_delete")
     * @Method("DELETE")
     */
    public function deleteAction(Request $request, Report $report)
    {
        $form = $this->createDeleteForm($report);
        $form->handleRequest($request);

        $em = $this->getDoctrine()->getManager();
        $reportParameters = $em->getRepository('ReportBundle:ReportParameter')->findBy(array('report' => $report->getId()));
        foreach ($reportParameters as $reportParameter) {
            $em->remove($reportParameter);
        }
        if ($form->isSubmitted() && $form->isValid()) {
            $em->remove($report);
            $em->flush();
        }

        $this->addFlash('success', 'Report has been removed successfuly');
        return $this->redirectToRoute('reports_index');
    }

    /**
     * Creates a form to delete a Report entity.
     *
     * @param Report $report The Report entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm(Report $report)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('reports_delete', array('id' => $report->getId())))
            ->setMethod('DELETE')
            ->getForm()
        ;
    }
}
