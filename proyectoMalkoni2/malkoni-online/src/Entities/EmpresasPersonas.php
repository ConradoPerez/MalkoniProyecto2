<?php
// src/Entities/EmpresasPersonas.php
namespace Entities;

use Doctrine\ORM\Mapping as ORM;
use Entities\Empresas;
use Entities\Personas;

/**
 * @ORM\Entity
 * @ORM\Table(
 *   name="empresas_personas",
 *   uniqueConstraints={
 *     @ORM\UniqueConstraint(name="uniq_empresa_persona", columns={"empresa_id","persona_id"})
 *   },
 *   indexes={
 *     @ORM\Index(name="idx_empresa_id", columns={"empresa_id"}),
 *     @ORM\Index(name="idx_persona_id", columns={"persona_id"})
 *   }
 * )
 */
class EmpresasPersonas
{
    /** @ORM\Id @ORM\GeneratedValue @ORM\Column(type="integer") */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="Entities\Empresas")
     * @ORM\JoinColumn(name="empresa_id", referencedColumnName="id", nullable=false, onDelete="CASCADE")
     */
    private $empresa;

    /**
     * @ORM\ManyToOne(targetEntity="Entities\Personas")
     * @ORM\JoinColumn(name="persona_id", referencedColumnName="id", nullable=false, onDelete="CASCADE")
     */
    private $persona;

    /** @ORM\Column(type="smallint", options={"unsigned":true,"default":1}) */
    private $estado = 1;

    /** @ORM\Column(type="datetime", options={"default":"CURRENT_TIMESTAMP"}) */
    private $fecha_alta;

    public function __construct()
    {
        $this->fecha_alta = new \DateTime();
    }

    public function getId(): ?int { return $this->id; }

    public function getEmpresa(): ?Empresas { return $this->empresa; }
    public function setEmpresa(Empresas $empresa): self { $this->empresa = $empresa; return $this; }

    public function getPersona(): ?Personas { return $this->persona; }
    public function setPersona(Personas $persona): self { $this->persona = $persona; return $this; }

    public function getEstado(): int { return (int)$this->estado; }
    public function setEstado(int $estado): self { $this->estado = $estado; return $this; }

    public function getFechaAlta(): \DateTimeInterface { return $this->fecha_alta; }
}
