<?php
// src/Entities/Direcciones.php
namespace Entities;

use Doctrine\ORM\Mapping as ORM;
use Entities\Provincias;
use Entities\Empresas;
use Entities\Localidades;
use Entities\Paises;

/**
 * @ORM\Entity
 * @ORM\Table(name="Direcciones")
 */
class Direcciones
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * Domicilio (calle y altura si aplica)
     * @ORM\Column(name="domicilio", type="string", length=255, nullable=true)
     */
    private $domicilio;

    /**
     * Barrio o zona
     * @ORM\Column(name="barrio", type="string", length=100, nullable=true)
     */
    private $barrio;

    /**
     * C¨®digo Postal
     * @ORM\Column(name="cp", type="string", length=20, nullable=true)
     */
    private $cp;

    /**
     * Observaciones adicionales
     * @ORM\Column(name="observaciones", type="text", nullable=true)
     */
    private $observaciones;

    /**
     * Pa¨ªs
     * @ORM\ManyToOne(targetEntity="Entities\Paises")
     * @ORM\JoinColumn(name="id_pais", referencedColumnName="id", nullable=true)
     */
    private $pais;

    /**
     * Provincia
     * @ORM\ManyToOne(targetEntity="Entities\Provincias")
     * @ORM\JoinColumn(name="id_provincia", referencedColumnName="id", nullable=true)
     */
    private $provincia;

    /**
     * Localidad
     * @ORM\ManyToOne(targetEntity="Entities\Localidades")
     * @ORM\JoinColumn(name="id_localidad", referencedColumnName="id", nullable=true)
     */
    private $localidad;

    /**
     * Empresa asociada
     * @ORM\ManyToOne(targetEntity="Entities\Empresas", inversedBy="direcciones")
     * @ORM\JoinColumn(name="id_empresa", referencedColumnName="id", nullable=false, onDelete="CASCADE")
     */
    private $empresa;

    // === GETTERS ===

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDomicilio(): ?string
    {
        return $this->domicilio;
    }

    public function getBarrio(): ?string
    {
        return $this->barrio;
    }

    public function getCp(): ?string
    {
        return $this->cp;
    }

    public function getObservaciones(): ?string
    {
        return $this->observaciones;
    }

    public function getPais(): ?Paises
    {
        return $this->pais;
    }

    public function getProvincia(): ?Provincias
    {
        return $this->provincia;
    }

    public function getLocalidad(): ?Localidades
    {
        return $this->localidad;
    }

    public function getEmpresa(): ?Empresas
    {
        return $this->empresa;
    }

    // === SETTERS ===

    public function setDomicilio(?string $domicilio): self
    {
        $this->domicilio = $domicilio;
        return $this;
    }

    public function setBarrio(?string $barrio): self
    {
        $this->barrio = $barrio;
        return $this;
    }

    public function setCp(?string $cp): self
    {
        $this->cp = $cp;
        return $this;
    }

    public function setObservaciones(?string $observaciones): self
    {
        $this->observaciones = $observaciones;
        return $this;
    }

    public function setPais(?Paises $pais): self
    {
        $this->pais = $pais;
        return $this;
    }

    public function setProvincia(?Provincias $provincia): self
    {
        $this->provincia = $provincia;
        return $this;
    }

    public function setLocalidad(?Localidades $localidad): self
    {
        $this->localidad = $localidad;
        return $this;
    }

    public function setEmpresa(Empresas $empresa): self
    {
        $this->empresa = $empresa;
        return $this;
    }
}
