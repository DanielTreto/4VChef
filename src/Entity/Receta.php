<?php

namespace App\Entity;

use App\Repository\RecetaRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: RecetaRepository::class)]
class Receta
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 100)]
    private ?string $titulo = null;

    #[ORM\Column]
    private ?int $comensales = null;

    #[ORM\ManyToOne(inversedBy: 'recetas')]
    private ?TipoReceta $tipo = null;

    /**
     * @var Collection<int, Ingrediente>
     */
    #[ORM\OneToMany(targetEntity: Ingrediente::class, mappedBy: 'receta', orphanRemoval: true)]
    private Collection $ingredientes;

    /**
     * @var Collection<int, Paso>
     */
    #[ORM\OneToMany(targetEntity: Paso::class, mappedBy: 'receta', orphanRemoval: true)]
    private Collection $pasos;

    /**
     * @var Collection<int, Valoracion>
     */
    #[ORM\OneToMany(targetEntity: Valoracion::class, mappedBy: 'receta')]
    private Collection $valoraciones;

    /**
     * @var Collection<int, RecetasNutrientes>
     */
    #[ORM\OneToMany(targetEntity: RecetasNutrientes::class, mappedBy: 'receta')]
    private Collection $valoresNutritivos;

    #[ORM\Column]
    private ?bool $eliminada = null;

    public function __construct()
    {
        $this->ingredientes = new ArrayCollection();
        $this->pasos = new ArrayCollection();
        $this->valoraciones = new ArrayCollection();
        $this->valoresNutritivos = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitulo(): ?string
    {
        return $this->titulo;
    }

    public function setTitulo(string $titulo): static
    {
        $this->titulo = $titulo;

        return $this;
    }

    public function getComensales(): ?int
    {
        return $this->comensales;
    }

    public function setComensales(int $comensales): static
    {
        $this->comensales = $comensales;

        return $this;
    }

    public function getTipo(): ?TipoReceta
    {
        return $this->tipo;
    }

    public function setTipo(?TipoReceta $tipo): static
    {
        $this->tipo = $tipo;

        return $this;
    }

    /**
     * @return Collection<int, Ingrediente>
     */
    public function getIngredientes(): Collection
    {
        return $this->ingredientes;
    }

    public function addIngrediente(Ingrediente $ingrediente): static
    {
        if (!$this->ingredientes->contains($ingrediente)) {
            $this->ingredientes->add($ingrediente);
            $ingrediente->setReceta($this);
        }

        return $this;
    }

    public function removeIngrediente(Ingrediente $ingrediente): static
    {
        if ($this->ingredientes->removeElement($ingrediente)) {
            // set the owning side to null (unless already changed)
            if ($ingrediente->getReceta() === $this) {
                $ingrediente->setReceta(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Paso>
     */
    public function getPasos(): Collection
    {
        return $this->pasos;
    }

    public function addPaso(Paso $paso): static
    {
        if (!$this->pasos->contains($paso)) {
            $this->pasos->add($paso);
            $paso->setReceta($this);
        }

        return $this;
    }

    public function removePaso(Paso $paso): static
    {
        if ($this->pasos->removeElement($paso)) {
            // set the owning side to null (unless already changed)
            if ($paso->getReceta() === $this) {
                $paso->setReceta(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Valoracion>
     */
    public function getValoraciones(): Collection
    {
        return $this->valoraciones;
    }

    public function addValoracione(Valoracion $valoracione): static
    {
        if (!$this->valoraciones->contains($valoracione)) {
            $this->valoraciones->add($valoracione);
            $valoracione->setReceta($this);
        }

        return $this;
    }

    public function removeValoracione(Valoracion $valoracione): static
    {
        if ($this->valoraciones->removeElement($valoracione)) {
            // set the owning side to null (unless already changed)
            if ($valoracione->getReceta() === $this) {
                $valoracione->setReceta(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, RecetasNutrientes>
     */
    public function getValoresNutritivos(): Collection
    {
        return $this->valoresNutritivos;
    }

    public function addValoresNutritivo(RecetasNutrientes $valoresNutritivo): static
    {
        if (!$this->valoresNutritivos->contains($valoresNutritivo)) {
            $this->valoresNutritivos->add($valoresNutritivo);
            $valoresNutritivo->setReceta($this);
        }

        return $this;
    }

    public function removeValoresNutritivo(RecetasNutrientes $valoresNutritivo): static
    {
        if ($this->valoresNutritivos->removeElement($valoresNutritivo)) {
            // set the owning side to null (unless already changed)
            if ($valoresNutritivo->getReceta() === $this) {
                $valoresNutritivo->setReceta(null);
            }
        }

        return $this;
    }

    public function isEliminada(): ?bool
    {
        return $this->eliminada;
    }

    public function setEliminada(bool $eliminada): static
    {
        $this->eliminada = $eliminada;

        return $this;
    }

}
