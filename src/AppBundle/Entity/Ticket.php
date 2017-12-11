<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Gedmo\Mapping\Annotation as Gedmo;


/**
 * Ticket
 *
 * @ORM\Table(name="ticket")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\TicketRepository")
 */
class Ticket
{
    /**
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\Visitor", mappedBy="ticket", cascade={"persist"})
    */
    private $visitors;

    /**
     * @ORM\OneToOne(targetEntity="AppBundle\Entity\Bill", cascade={"persist"})
     * @ORM\JoinColumn(nullable=true)
    */
    private $bill;

    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var bool
     *
     * @ORM\Column(name="half_day", type="boolean")
     */
    private $halfDay = false;

    /**
     * @var date
     *
     * @ORM\Column(name="day", type="date")
     */
    private $day;

    /**
     * @var int
     *
     * @ORM\Column(name="price", type="integer")
     */
    private $price;

    /**
     * @var int
     *
     * @ORM\Column(name="nb_visitor", type="integer")
     */
    private $nbVisitor;

    public function __construct()
    {
        $this->visitors = new ArrayCollection();
    }

    /**
     * Add visitor
     *
     * @param \AppBundle\Entity\Visitor $visitor
    */
    public function addVisitor(Visitor $visitor)
    {
        $this->visitors[] = $visitor;
        $visitor->setTicket($this);
    }

    /**
     * Remove visitor
     *
     * @param \AppBundle\Entity\Visitor $visitor
    */
    public function removeVisitor(Visitor $visitor)
    {
        $this->visitors->removeElement($visitor);
    }

    /**
     * Get visitors
     *
     * @param \AppBundle\Entity\Visitor $visitor
    */
    public function getVisitors()
    {
        return $this->visitors;
    }

    /**
     * Get id
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set halfDay
     *
     * @param boolean $halfDay
     *
     * @return Ticket
     */
    public function setHalfDay($halfDay)
    {
        $this->halfDay = $halfDay;

        return $this;
    }

    /**
     * Get halfDay
     *
     * @return bool
     */
    public function getHalfDay()
    {
        return $this->halfDay;
    }

    /**
     * Set bill
     *
     * @param \AppBundle\Entity\Bill $bill
     *
     * @return Ticket
     */
    public function setBill(\AppBundle\Entity\Bill $bill)
    {
        $this->bill = $bill;

        return $this;
    }

    /**
     * Get bill
     *
     * @return \AppBundle\Entity\Bill
     */
    public function getBill()
    {
        return $this->bill;
    }

    /**
     * Set day
     *
     * @param \DateTime $day
     *
     * @return Ticket
     */
    public function setDay($day)
    {
        $this->day = $day;

        return $this;
    }

    /**
     * Get day
     *
     * @return \DateTime
     */
    public function getDay()
    {
        return $this->day;
    }

    /**
     * Set price
     *
     * @param integer $price
     *
     * @return Ticket
     */
    public function setPrice($price)
    {
        $this->price = $price;

        return $this;
    }

    /**
     * Get price
     *
     * @return integer
     */
    public function getPrice()
    {
        return $this->price;
    }

    /**
     * Set nbVisitor
     *
     * @param integer $nbVisitor
     *
     * @return Ticket
     */
    public function setNbVisitor($nbVisitor)
    {
        $this->nbVisitor = $nbVisitor;

        return $this;
    }

    /**
     * Get nbVisitor
     *
     * @return integer
     */
    public function getNbVisitor()
    {
        return $this->nbVisitor;
    }
}
