<?php
// src/Entities/Empresas.php
namespace Entities;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Entities\Direcciones;

/**
 * @ORM\Entity
 * @ORM\Table(name="Empresas")
 */
class Empresas
{
    /** @ORM\Id @ORM\GeneratedValue @ORM\Column(type="integer") */
    private $id;

    /** @ORM\Column(type="string", length=40, nullable=true) */
    private $cod_cliente;

    /** @ORM\Column(type="string", length=255, nullable=true) */
    private $razon_social;

    /** @ORM\Column(type="string", length=40, nullable=true) */
    private $cuit;

    /** 
     * DNI (int(20) en MySQL). Usamos integer en Doctrine.
     * @ORM\Column(type="integer", nullable=true)
     */
    private $dni;

    /** @ORM\Column(type="text", nullable=true) */
    private $observacion;

    /** @ORM\Column(name="CodCondIVA", type="string", length=5, nullable=true) */
    private $codCondIVA;

    /** @ORM\Column(type="string", length=80, nullable=true) */
    private $num_tel;

    /** @ORM\Column(type="string", length=255, nullable=true) */
    private $email;

    /**
     * Estado: 0 = Inactivo, 1 = Activo
     * @ORM\Column(type="smallint", options={"unsigned":true, "default":1})
     */
    private $estado = 1;

    /** @ORM\Column(type="date", nullable=true) */
    private $fecha_inicial;

    /** @ORM\Column(type="date", nullable=true) */
    private $fecha_alta;

    /**
     * Último contacto con la empresa.
     * @ORM\Column(name="fecha_ult_contacto", type="date", nullable=true)
     */
    private $fechaUltContacto;

    /** @ORM\Column(type="boolean", options={"default":false}) */
    private $validado = false;

    /** @ORM\Column(type="string", length=64, nullable=true) */
    private $validacion_token;

    /**
     * Baja lógica: 0 = activo, 1 = inactivo
     * @ORM\Column(type="boolean", options={"default":false})
     */
    private $baja = false;

    /**
     * @ORM\OneToMany(targetEntity="Entities\Direcciones", mappedBy="empresa", cascade={"persist","remove"})
     */
    private $direcciones;

    public function __construct()
    {
        $this->direcciones = new ArrayCollection();
    }

    // === GETTERS ===
    public function getId(): ?int { return $this->id; }
    public function getCodCliente(): ?string { return $this->cod_cliente; }
    public function getRazonSocial(): ?string { return $this->razon_social; }
    public function getCuit(): ?string { return $this->cuit; }
    public function getDni(): ?int { return $this->dni; }
    public function getObservacion(): ?string { return $this->observacion; }
    public function getCodCondIVA(): ?string { return $this->codCondIVA; }
    public function getNumTel(): ?string { return $this->num_tel; }
    public function getEmail(): ?string { return $this->email; }
    public function getEstado(): int { return $this->estado; }
    public function getFechaInicial(): ?\DateTimeInterface { return $this->fecha_inicial; }
    public function getFechaAlta(): ?\DateTimeInterface { return $this->fecha_alta; }
    public function getFechaUltContacto(): ?\DateTimeInterface { return $this->fechaUltContacto; }
    public function isValidado(): bool { return $this->validado; }
    public function getValidacionToken(): ?string { return $this->validacion_token; }
    public function isBaja(): bool { return $this->baja; }

    /** @return Collection|Direcciones[] */
    public function getDirecciones(): Collection { return $this->direcciones; }

    // === SETTERS ===
    public function setCodCliente(?string $cod_cliente): self { $this->cod_cliente = $cod_cliente; return $this; }
    public function setRazonSocial(?string $razon_social): self { $this->razon_social = $razon_social; return $this; }
    public function setCuit(?string $cuit): self { $this->cuit = $cuit; return $this; }
    public function setDni(?int $dni): self { $this->dni = $dni; return $this; }
    public function setObservacion(?string $observacion): self { $this->observacion = $observacion; return $this; }
    public function setCodCondIVA(?string $codCondIVA): self { $this->codCondIVA = $codCondIVA; return $this; }
    public function setNumTel(?string $num_tel): self { $this->num_tel = $num_tel; return $this; }
    public function setEmail(?string $email): self { $this->email = $email; return $this; }
    public function setEstado(int $estado): self { $this->estado = $estado; return $this; }
    public function setFechaInicial(?\DateTimeInterface $fecha_inicial): self { $this->fecha_inicial = $fecha_inicial; return $this; }
    public function setFechaAlta(?\DateTimeInterface $fecha_alta): self { $this->fecha_alta = $fecha_alta; return $this; }
    public function setFechaUltContacto(?\DateTimeInterface $fechaUltContacto): self { $this->fechaUltContacto = $fechaUltContacto; return $this; }
    public function setValidado(bool $validado): self { $this->validado = $validado; return $this; }
    public function setValidacionToken(?string $token): self { $this->validacion_token = $token; return $this; }
    public function setBaja(bool $baja): self { $this->baja = $baja; return $this; }

    // === Relaciones con Direcciones ===
    public function addDireccion(Direcciones $d): self
    {
        if (!$this->direcciones->contains($d)) {
            $this->direcciones[] = $d;
            $d->setEmpresa($this);
        }
        return $this;
    }

    public function removeDireccion(Direcciones $d): self
    {
        if ($this->direcciones->removeElement($d) && $d->getEmpresa() === $this) {
            $d->setEmpresa(null);
        }
        return $this;
    }
}
