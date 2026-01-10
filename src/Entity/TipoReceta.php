<?php

namespace App\Entity;

use App\Repository\TipoRecetaRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: TipoRecetaRepository::class)]
class TipoReceta
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 50)]
    private ?string $nombre = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $descripcion = null;

    /**
     * @var Collection<int, Receta>
     */
    #[ORM\OneToMany(targetEntity: Receta::class, mappedBy: 'tipo')]
    private Collection $recetas;

    public function __construct()
    {
        $this->recetas = new ArrayCollection();
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

    public function getDescripcion(): ?string
    {
        return $this->descripcion;
    }

    public function setDescripcion(?string $descripcion): static
    {
        $this->descripcion = $descripcion;

        return $this;
    }

    /**
     * @return Collection<int, Receta>
     */
    public function getRecetas(): Collection
    {
        return $this->recetas;
    }

    public function addReceta(Receta $receta): static
    {
        if (!$this->recetas->contains($receta)) {
            $this->recetas->add($receta);
            $receta->setTipo($this);
        }

        return $this;
    }

    public function removeReceta(Receta $receta): static
    {
        if ($this->recetas->removeElement($receta)) {
            // set the owning side to null (unless already changed)
            if ($receta->getTipo() === $this) {
                $receta->setTipo(null);
            }
        }

        return $this;
    }
}
