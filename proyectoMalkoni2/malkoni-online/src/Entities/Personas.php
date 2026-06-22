<?php
// src/Entities/Personas.php
namespace Entities;

use Doctrine\ORM\Mapping as ORM;
use Entities\Empresas;

/**
 * @ORM\Entity
 * @ORM\Table(name="Personas")
 */
class Personas
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /** @ORM\Column(type="string", nullable=true) */
    private $nombre;

    /** @ORM\Column(type="string", nullable=true) */
    private $apellido;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    private $genero;

    /**
     * DNI como VARCHAR para no perder ceros a la izquierda.
     * Guardamos siempre 8 dígitos (si viene 7 => se rellena con 0 adelante).
     *
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    private $dni;

    /** @ORM\Column(type="string", nullable=true) */
    private $email;

    /** @ORM\Column(type="string", nullable=true) */
    private $num_tel;

    /** @ORM\Column(type="string", nullable=true) */
    private $pass;

    /**
     * @ORM\ManyToOne(targetEntity="Entities\Empresas")
     * @ORM\JoinColumn(name="id_empresa", referencedColumnName="id", nullable=true)
     */
    private $empresa;

    /**
     * @ORM\Column(name="token_OPT", type="string", length=20, nullable=true)
     */
    private $tokenOpt;

    /**
     * @ORM\Column(name="reset_token", type="string", length=64, nullable=true)
     */
    private $resetToken;

    /**
     * @ORM\Column(name="validacion_token", type="string", length=64, nullable=true)
     */
    private $validacion_token;

    /**
     * @ORM\Column(type="integer", length=2, nullable=true)
     */
    private $rol;

    /**
     * @ORM\Column(type="integer", name="estado_persona", nullable=true)
     */
    private $estadoPersona;

    /**
     * @ORM\ManyToOne(targetEntity="Entities\Empresas")
     * @ORM\JoinColumn(name="empresa_activa_id", referencedColumnName="id", nullable=true)
     */
    private $empresaActiva;

    // === GETTERS ===

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNombre(): ?string
    {
        return $this->nombre;
    }

    public function getApellido(): ?string
    {
        return $this->apellido;
    }

    public function getGenero(): ?string
    {
        return $this->genero;
    }

    public function getDni(): ?string
    {
        return $this->dni;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function getNumTel(): ?string
    {
        return $this->num_tel;
    }

    public function getPass(): ?string
    {
        return $this->pass;
    }

    public function getEmpresa(): ?Empresas
    {
        return $this->empresa;
    }

    public function getTokenOpt(): ?string
    {
        return $this->tokenOpt;
    }

    public function getResetToken(): ?string
    {
        return $this->resetToken;
    }

    public function getValidacionToken(): ?string
    {
        return $this->validacion_token;
    }

    public function getRol(): ?int
    {
        return $this->rol;
    }

    public function getEstadoPersona(): ?int
    {
        return $this->estadoPersona;
    }

    public function getEmpresaActiva(): ?Empresas
    {
        return $this->empresaActiva;
    }

    // === SETTERS ===

    public function setNombre(?string $nombre): self
    {
        $this->nombre = $nombre;
        return $this;
    }

    public function setApellido(?string $apellido): self
    {
        $this->apellido = $apellido;
        return $this;
    }

    public function setGenero(?string $genero): self
    {
        $this->genero = $genero;
        return $this;
    }

    public function setDni(?string $dni): self
    {
        if ($dni === null) {
            $this->dni = null;
            return $this;
        }

        $dni = preg_replace('/\D/', '', $dni);
        if ($dni === '') {
            $this->dni = null;
            return $this;
        }

        $len = strlen($dni);
        if ($len !== 7 && $len !== 8) {
            throw new \InvalidArgumentException('DNI debe tener 7 u 8 dígitos');
        }

        $this->dni = str_pad($dni, 8, '0', STR_PAD_LEFT);
        return $this;
    }

    public function setEmail(?string $email): self
    {
        $this->email = $email;
        return $this;
    }

    public function setNumTel(?string $num_tel): self
    {
        $this->num_tel = $num_tel;
        return $this;
    }

    public function setPass(?string $pass): self
    {
        $this->pass = $pass;
        return $this;
    }

    public function setEmpresa(?Empresas $empresa): self
    {
        $this->empresa = $empresa;
        return $this;
    }

    public function setTokenOpt(?string $tokenOpt): self
    {
        $this->tokenOpt = $tokenOpt;
        return $this;
    }

    public function setResetToken(?string $resetToken): self
    {
        $this->resetToken = $resetToken;
        return $this;
    }

    public function setValidacionToken(?string $validacion_token): self
    {
        $this->validacion_token = $validacion_token;
        return $this;
    }

    public function setRol(?int $rol): self
    {
        $this->rol = $rol;
        return $this;
    }

    public function setEstadoPersona(?int $estado): self
    {
        $this->estadoPersona = $estado;
        return $this;
    }

    public function setEmpresaActiva(?Empresas $empresa): self
    {
        $this->empresaActiva = $empresa;
        return $this;
    }
}
