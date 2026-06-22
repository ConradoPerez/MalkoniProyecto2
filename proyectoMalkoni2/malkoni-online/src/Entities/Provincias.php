<?php
namespace Entities;

use Doctrine\ORM\Mapping as ORM;
use Entities\Paises;

/**
 * @ORM\Entity
 * @ORM\Table(name="Provincias")
 */
class Provincias
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /** @ORM\Column(type="string", nullable=false) */
    private $nombre;

    /**
     * @ORM\ManyToOne(targetEntity="Entities\Paises")
     * @ORM\JoinColumn(name="id_pais", referencedColumnName="id", nullable=false)
     */
    private $pais;

    // === GETTERS ===
    public function getId() {
        return $this->id;
    }

    public function getNombre() {
        return $this->nombre;
    }

    public function getPais() {
        return $this->pais;
    }

    // === SETTERS ===
    public function setNombre($nombre) {
        $this->nombre = $nombre;
        return $this;
    }

    public function setPais($pais) {
        $this->pais = $pais;
        return $this;
    }
}
