<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Calendar
 *
 * @ORM\Table(name="calendar")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\CalendarRepository")
 */
class Calendar
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var int
     *
     * @ORM\Column(name="nb_visitor", type="integer")
     */
    private $nbVisitor;


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
     * Set nbVisitor
     *
     * @param integer $nbVisitor
     *
     * @return Calendar
     */
    public function setNbVisitor($nbVisitor)
    {
        $this->nbVisitor = $nbVisitor;

        return $this;
    }

    /**
     * Get nbVisitor
     *
     * @return int
     */
    public function getNbVisitor()
    {
        return $this->nbVisitor;
    }
}
