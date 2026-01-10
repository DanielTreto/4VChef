<?php

namespace App\Entity;

use App\Repository\PasoRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PasoRepository::class)]
class Paso
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?int $orden = null;

    #[ORM\Column(length: 255)]
    private ?string $descripcion = null;

    #[ORM\ManyToOne(inversedBy: 'pasos')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Receta $receta = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getOrden(): ?int
    {
        return $this->orden;
    }

    public function setOrden(int $orden): static
    {
        $this->orden = $orden;

        return $this;
    }

    public function getDescripcion(): ?string
    {
        return $this->descripcion;
    }

    public function setDescripcion(string $descripcion): static
    {
        $this->descripcion = $descripcion;

        return $this;
    }

    public function getReceta(): ?Receta
    {
        return $this->receta;
    }

    public function setReceta(?Receta $receta): static
    {
        $this->receta = $receta;

        return $this;
    }
}
