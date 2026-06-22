<?php
// src/Entities/Localidades.php
namespace Entities;

use Doctrine\ORM\Mapping as ORM;
use Entities\Provincias;

/**
 * @ORM\Entity
 * @ORM\Table(name="Localidades")
 */
class Localidades
{
    /** 
     * @ORM\Id 
     * @ORM\GeneratedValue 
     * @ORM\Column(type="integer") 
     */
    private $id;

    /** 
     * @ORM\Column(type="string", length=20, nullable=false) 
     */
    private $nombre;

    /**
     * @ORM\ManyToOne(targetEntity="Entities\Provincias")
     * @ORM\JoinColumn(name="id_provincia", referencedColumnName="id", nullable=true)
     */
    private $provincia;

    // === GETTERS ===

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNombre(): ?string
    {
        return $this->nombre;
    }

    public function getProvincia(): ?Provincias
    {
        return $this->provincia;
    }

    // === SETTERS ===

    public function setNombre(string $nombre): self
    {
        $this->nombre = $nombre;
        return $this;
    }

    public function setProvincia(?Provincias $provincia): self
    {
        $this->provincia = $provincia;
        return $this;
    }
}
