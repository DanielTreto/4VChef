<?php

namespace App\Entity;

use App\Repository\RecetasNutrientesRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: RecetasNutrientesRepository::class)]
class RecetasNutrientes
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'valoresNutritivos')]
    private ?Receta $receta = null;

    #[ORM\ManyToOne( inversedBy: 'recetasNutrientes')]
    private ?TipoNutriente $tipoNutriente = null;

    #[ORM\Column]
    private ?float $cantidad = null;

    public function getId(): ?int
    {
        return $this->id;
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

    public function getTipo(): ?TipoNutriente
    {
        return $this->tipoNutriente;
    }

    public function setTipo(?TipoNutriente $tipoNutriente): static
    {
        $this->tipoNutriente = $tipoNutriente;

        return $this;
    }

    public function getCantidad(): ?float
    {
        return $this->cantidad;
    }

    public function setCantidad(float $cantidad): static
    {
        $this->cantidad = $cantidad;

        return $this;
    }
}
