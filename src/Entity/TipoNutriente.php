<?php

namespace App\Entity;

use App\Repository\TipoNutrienteRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: TipoNutrienteRepository::class)]
class TipoNutriente
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 50)]
    private ?string $nombre = null;

    #[ORM\Column(length: 50)]
    private ?string $unidad = null;

    #[ORM\OneToMany(mappedBy: 'tipo', targetEntity: RecetasNutrientes::class)]
    private Collection $recetaNutrientes;

    public function __construct()
    {
        $this->recetaNutrientes = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNombre(): ?string
    {
        return $this->nombre;
    }

    public function setNombre(string $nombre): static
    {
        $this->nombre = $nombre;

        return $this;
    }

    public function getUnidad(): ?string
    {
        return $this->unidad;
    }

    public function setUnidad(string $unidad): static
    {
        $this->unidad = $unidad;

        return $this;
    }

    /**
     * @return Collection<int, RecetasNutrientes>
     */
    public function getRecetasNutrientes(): Collection
    {
        return $this->recetaNutrientes;
    }

    public function addRecetasNutrientes(RecetasNutrientes $recetaNutriente): static
    {
        if (!$this->recetaNutrientes->contains($recetaNutriente)) {
            $this->recetaNutrientes->add($recetaNutriente);
            $recetaNutriente->setTipo($this);
        }

        return $this;
    }

    public function removeRecetaNutrientes(RecetasNutrientes $recetaNutriente): static
    {
        if ($this->recetaNutrientes->removeElement($recetaNutriente)) {
            // set the owning side to null (unless already changed)
            if ($recetaNutriente->getTipo() === $this) {
                $recetaNutriente->setTipo(null);
            }
        }

        return $this;
    }
}
