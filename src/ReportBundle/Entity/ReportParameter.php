<?php

namespace ReportBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * ReportParameter
 *
 * @ORM\Table(name="report_parameters", indexes={@ORM\Index(name="fk_report_id", columns={"report_id"})})
 * @ORM\Entity
 */
class ReportParameter
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="parameter", type="string", length=200, nullable=false)
     */
    private $parameter;

    /**
     * @var string
     *
     * @ORM\Column(name="value", type="string", length=20, nullable=false)
     */
    private $value;

    /**
     * @var \ReportBundle\Entity\Report
     *
     * @ORM\ManyToOne(targetEntity="ReportBundle\Entity\Report", inversedBy="reportParamters")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="report_id", referencedColumnName="id")
     * })
     */
    private $report;



    /**
     * Get id
     *
     * @return integer 
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set parameter
     *
     * @param string $parameter
     * @return ReportParameter
     */
    public function setParameter($parameter)
    {
        $this->parameter = $parameter;

        return $this;
    }

    /**
     * Get parameter
     *
     * @return string 
     */
    public function getParameter()
    {
        return $this->parameter;
    }

    /**
     * Set value
     *
     * @param string $value
     * @return ReportParameter
     */
    public function setValue($value)
    {
        $this->value = $value;

        return $this;
    }

    /**
     * Get value
     *
     * @return string 
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * Set report
     *
     * @param \ReportBundle\Entity\Report $report
     * @return ReportParameter
     */
    public function setReport(\ReportBundle\Entity\Report $report = null)
    {
        $this->report = $report;

        return $this;
    }

    /**
     * Get report
     *
     * @return \ReportBundle\Entity\Report 
     */
    public function getReport()
    {
        return $this->report;
    }
    
    public function addReport(Report $report)
    {
        return $this->setReport($report);
    }
}
