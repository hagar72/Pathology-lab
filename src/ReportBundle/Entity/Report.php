<?php

namespace ReportBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * Report
 *
 * @ORM\Table(name="reports", indexes={@ORM\Index(name="fk_user_id", columns={"user_id"})})
 * @ORM\Entity
 */
class Report
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
     * @ORM\Column(name="name", type="string", length=50, nullable=false)
     */
    private $name;

    /**
     * @var \UserBundle\Entity\User
     *
     * @ORM\ManyToOne(targetEntity="UserBundle\Entity\User")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="user_id", referencedColumnName="id")
     * })
     */
    private $user;

    /**
     * @ORM\OneToMany(targetEntity="ReportBundle\Entity\ReportParameter", mappedBy="report", cascade={"persist"})
     */
    protected $reportParameters;

    public function __construct()
    {
        $this->reportParameters = new ArrayCollection();
    }

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
     * Set name
     *
     * @param string $name
     * @return Report
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name
     *
     * @return string 
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set user
     *
     * @param \UserBundle\Entity\User $user
     * @return Report
     */
    public function setUser(\UserBundle\Entity\User $user = null)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * Get user
     *
     * @return \UserBundle\Entity\User 
     */
    public function getUser()
    {
        return $this->user;
    }
    
    public function getReportParameters()
    {
        return $this->reportParameters;
    }
    
    public function addReportParameter(ReportParameter $reportParameter)
    {
        $reportParameter->addReport($this);

        $this->reportParameters->add($reportParameter);
    }

    public function removeReportParameter(ReportParameter $reportParameter)
    {
        $this->reportParameters->removeElement($reportParameter);
    }
}
