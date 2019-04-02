<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * grupos
 *
 * @ORM\Table(name="grupos")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\gruposRepository")
 */
class grupos
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
     * @var string
     *
     * @ORM\Column(name="ip", type="string", length=15)
     */
    private $ip;

    /**
     * @var string
     *
     * @ORM\Column(name="grupo", type="string", length=50)
     */
    private $grupo;

    /**
     * @var string
     *
     * @ORM\Column(name="plantel", type="string", length=50)
     */
    private $plantel;


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
     * Set ip
     *
     * @param string $ip
     *
     * @return grupos
     */
    public function setIp($ip)
    {
        $this->ip = $ip;

        return $this;
    }

    /**
     * Get ip
     *
     * @return string
     */
    public function getIp()
    {
        return $this->ip;
    }

    /**
     * Set grupo
     *
     * @param string $grupo
     *
     * @return grupos
     */
    public function setGrupo($grupo)
    {
        $this->grupo = $grupo;

        return $this;
    }

    /**
     * Get grupo
     *
     * @return string
     */
    public function getGrupo()
    {
        return $this->grupo;
    }

    /**
     * Set plantel
     *
     * @param string $plantel
     *
     * @return grupos
     */
    public function setPlantel($plantel)
    {
        $this->plantel = $plantel;

        return $this;
    }

    /**
     * Get plantel
     *
     * @return string
     */
    public function getPlantel()
    {
        return $this->plantel;
    }
}

