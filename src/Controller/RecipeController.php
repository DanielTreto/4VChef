<?php

namespace App\Controller;

use App\Entity\Receta;
use App\Model\PasoDTO;
use App\Model\RecetaDTO;
use App\Model\TipoRecetaDTO;
use App\Model\IngredienteDTO;
use App\Model\TipoNutrienteDTO;
use App\Model\RespuestaErrorDTO;
use App\Model\ValorNutritivoDTO;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Attribute\MapQueryParameter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

final class RecipeController extends AbstractController
{
    public function __construct(private EntityManagerInterface $entityManager) {}

    #[Route('/recipes', name: 'search_recipes', methods: ['GET'])]
    public function getAllRecipes(#[MapQueryParameter(name: 'type')] ?string $tipo  = null): JsonResponse
    {
        try {

            // Valido el tipo, que debe de ser un entero
            if ($tipo != null && !$this->esEnteroPositivo($tipo)) {
                $errorMensaje = new RespuestaErrorDTO(10, "Validación tipo receta invalida");
                return new JsonResponse($errorMensaje, 400);
            }

            // Si hay tipo entonces busco por tipo, sino busco cualquiera
            if ($tipo != null) {
                $tipoEntero = (int)$tipo;
                // Recupero la información de BBDD
                $recetasBBDD = $this->entityManager
                    ->getRepository(Receta::class)
                    ->findByType($tipoEntero);
            } else {
                // Recupero la información de BBDD
                $recetasBBDD = $this->entityManager
                    ->getRepository(Receta::class)
                    ->findAll();
            }

            // Convierto de Entidades a DTO
            $recetasDTO = [];
            foreach ($recetasBBDD as $recetaEntidad) {
                if (!$recetaEntidad->isEliminada()) {

                    // 1. Convertir la entidad TipoReceta a su DTO
                    $tipo = $recetaEntidad->getTipo();
                    $tipoDTO = new TipoRecetaDTO(
                        $tipo?->getId() ?? 0,
                        $tipo?->getNombre() ?? 'Sin clasificar',
                        $tipo?->getDescripcion() ?? ''
                    );

                    // 2. Convertir la colección de Ingredientes a un array de DTOs
                    $ingredientesDTO = [];
                    foreach ($recetaEntidad->getIngredientes() as $ing) {
                        $ingredientesDTO[] = new IngredienteDTO($ing->getNombre(), $ing->getCantidad(), $ing->getUnidad());
                    }

                    // 3. Convertir la colección de Pasos a un array de DTOs
                    $pasosDTO = [];
                    foreach ($recetaEntidad->getPasos() as $paso) {
                        $pasosDTO[] = new PasoDTO($paso->getOrden(), $paso->getDescripcion());
                    }

                    // 4. Convertir la colección de Valores Nutritivos a un array de DTOs
                    $nutrientesDTO = [];
                    foreach ($recetaEntidad->getValoresNutritivos() as $nutriente) {
                        $tipoNut = $nutriente->getTipo();
                        // Asumimos que si no hay tipo, el ID es 0 y nombre Desconocido
                        $tipoNutrienteDTO = new TipoNutrienteDTO(
                            $tipoNut?->getId() ?? 0,
                            $tipoNut?->getNombre() ?? 'Desconocido',
                            $tipoNut?->getUnidad() ?? ''
                        );
                        $nutrientesDTO[] = new ValorNutritivoDTO(
                            $tipoNutrienteDTO,
                            $nutriente->getCantidad(),
                        );
                    }

                    // 5. Crear el RecetaDTO con los datos ya transformados
                    $recetasDTO[] = new RecetaDTO(
                        $recetaEntidad->getId(),
                        $recetaEntidad->getTitulo(),
                        $recetaEntidad->getComensales(),
                        $tipoDTO,
                        $ingredientesDTO,
                        $pasosDTO,
                        $nutrientesDTO
                    );
                }
            }

            return $this->json($recetasDTO);
        } catch (\Throwable $th) {
            $errorMensaje = new RespuestaErrorDTO(1000, "Error General");
            return new JsonResponse($errorMensaje, 500);
        }
    }

    private function esEnteroPositivo(string $valor): bool
    {
        // Comprueba que todos los caracteres sean dígitos
        if (!ctype_digit($valor)) {
            return false;
        }

        // Convierte a entero y verifica que sea mayor que 0
        return (int)$valor > 0;
    }
}
