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
