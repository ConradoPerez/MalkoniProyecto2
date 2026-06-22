<?php
namespace Entities;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="Paises")
 */
class Paises
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /** @ORM\Column(type="string", nullable=false) */
    private $nombre;

    // === GETTERS ===
    public function getId() {
        return $this->id;
    }

    public function getNombre() {
        return $this->nombre;
    }

    // === SETTERS ===
    public function setNombre($nombre) {
        $this->nombre = $nombre;
        return $this;
    }
}
