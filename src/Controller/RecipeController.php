<?php

namespace App\Controller;

use App\Entity\Paso;
use App\Entity\Receta;
use App\Model\PasoDTO;
use App\Model\RecetaDTO;
use App\Entity\Ingrediente;
use App\Model\TipoRecetaDTO;
use App\Model\IngredienteDTO;
use App\Model\TipoNutrienteDTO;
use App\Model\RespuestaErrorDTO;
use App\Model\ValorNutritivoDTO;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
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
                    $recetasDTO[] = $this->toDTO($recetaEntidad);
                }
            }

            return $this->json($recetasDTO);
        } catch (\Throwable $th) {
            $errorMensaje = new RespuestaErrorDTO(1000, "Error General");
            return new JsonResponse($errorMensaje, 500);
        }
    }

    #[Route('/recipes', name: 'post_recipes', methods: ['POST'])]
    public function newRecipes(Request $request): JsonResponse
    {

        try {
            // Recuperamos del request el Body
            $jsonBody = $request->getContent(); // Obtiene el cuerpo como texto
            $data = json_decode($jsonBody, true); // Lo decodifica a un array asociativo

            /// VALIDACIONES

            // Manejo de errores si el JSON no es válido
            if (json_last_error() !== JSON_ERROR_NONE) {
                return $this->json(['error' => 'JSON inválido'], 400);
            }

            // Valido campos obligatorios: titulo, comensales
            if (!isset($data['titulo'])) {
                $errorMensaje = new RespuestaErrorDTO(400, "El campo titulo es obligatorio");
                return new JsonResponse($errorMensaje, 400);
            }

            if (!isset($data['comensales'])) {
                $errorMensaje = new RespuestaErrorDTO(400, "El campo comensales es obligatorio");
                return new JsonResponse($errorMensaje, 400);
            }

            // Repositorios
            $tipoRecetaRepo = $this->entityManager->getRepository(\App\Entity\TipoReceta::class);
            $tipoNutrienteRepo = $this->entityManager->getRepository(\App\Entity\TipoNutriente::class);

            // Validar Tipo de Receta (Solo si se envía)
            $tipoReceta = null;
            if (isset($data['tipo-id'])) {
                $tipoReceta = $tipoRecetaRepo->find($data['tipo-id']);
                if (!$tipoReceta) {
                    $errorMensaje = new RespuestaErrorDTO(400, "El tipo de receta no existe");
                    return new JsonResponse($errorMensaje, 400);
                }
            }

            // Validar Ingredientes (> 0)
            if (!isset($data['ingredientes']) || !is_array($data['ingredientes']) || count($data['ingredientes']) < 1) {
                $errorMensaje = new RespuestaErrorDTO(400, "La receta debe tener al menos 1 ingrediente");
                return new JsonResponse($errorMensaje, 400);
            }

            // Validar Pasos (> 0)
            if (!isset($data['pasos']) || !is_array($data['pasos']) || count($data['pasos']) < 1) {
                $errorMensaje = new RespuestaErrorDTO(400, "La receta debe tener al menos 1 paso");
                return new JsonResponse($errorMensaje, 400);
            }

            // Validar Nutrientes
            if (isset($data['nutrientes']) && is_array($data['nutrientes'])) {
                foreach ($data['nutrientes'] as $index => $nutData) {
                    // Buscamos si existe el tipo
                    $existeTipo = $tipoNutrienteRepo->find($nutData['tipo-id']);
                    if (!$existeTipo) {
                        $errorMensaje = new RespuestaErrorDTO(400, "El tipo de nutriente con ID " . $nutData['tipo-id'] . " no existe");
                        return new JsonResponse($errorMensaje, 400);
                    }
                }
            }

            // Crear Entidad Receta
            $receta = new Receta();
            $receta->setTitulo($data['titulo']);
            $receta->setComensales($data['comensales']);
            $receta->setTipo($tipoReceta);
            $receta->setEliminada(false);

            // Añadir Ingredientes
            foreach ($data['ingredientes'] as $ingData) {
                if (!isset($ingData['nombre'], $ingData['cantidad'], $ingData['unidad'])) {
                     $errorMensaje = new RespuestaErrorDTO(400, "Datos de ingrediente incompletos");
                     return new JsonResponse($errorMensaje, 400);
                }
                $ingrediente = new Ingrediente();
                $ingrediente->setNombre($ingData['nombre']);
                $ingrediente->setCantidad($ingData['cantidad']);
                $ingrediente->setUnidad($ingData['unidad']);
                $receta->addIngrediente($ingrediente);
            }

            // Añadir Pasos
            foreach ($data['pasos'] as $stepData) {
                if (!isset($stepData['orden'], $stepData['descripcion'])) {
                     $errorMensaje = new RespuestaErrorDTO(400, "Datos de paso incompletos");
                     return new JsonResponse($errorMensaje, 400);
                }
                $paso = new Paso();
                $paso->setOrden($stepData['orden']);
                $paso->setDescripcion($stepData['descripcion']);
                $receta->addPaso($paso);
            }

            // Añadir Nutrientes
            if (isset($data['nutrientes']) && is_array($data['nutrientes'])) {
                foreach ($data['nutrientes'] as $nutData) {
                    if (!isset($nutData['tipo-id'], $nutData['cantidad'])) {
                        $errorMensaje = new RespuestaErrorDTO(400, "Datos de nutriente incompletos");
                        return new JsonResponse($errorMensaje, 400);
                    }
                    $tipoNutriente = $tipoNutrienteRepo->find($nutData['tipo-id']);
                    $nutriente = new RecetasNutrientes();
                    $nutriente->setTipo($tipoNutriente);
                    $nutriente->setCantidad($nutData['cantidad']);
                    $receta->addValoresNutritivo($nutriente);
                }
            }

            /// Persistimos
            $this->entityManager->persist($receta);
            $this->entityManager->flush();

            /// Monto Respuesta
            return $this->json($this->toDTO($receta));
        } catch (\Throwable $th) {
            $errorMensaje = new RespuestaErrorDTO(1000, "Error General: " . $th->getMessage());
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

    private function toDTO(Receta $receta): RecetaDTO
    {
        // 1. Convertir la entidad TipoReceta a su DTO
        $tipoDTO = new TipoRecetaDTO(
            $receta->getTipo()?->getId() ?? 0,
            $receta->getTipo()?->getNombre() ?? 'Sin clasificar',
            $receta->getTipo()?->getDescripcion() ?? ''
        );

        // 2. Convertir Ingredientes a DTOs
        $ingredientesDTO = [];
        foreach ($receta->getIngredientes() as $ing) {
            $ingredientesDTO[] = new IngredienteDTO($ing->getNombre(), $ing->getCantidad(), $ing->getUnidad());
        }

        // 3. Convertir Pasos a DTOs
        $pasosDTO = [];
        foreach ($receta->getPasos() as $paso) {
            $pasosDTO[] = new PasoDTO($paso->getOrden(), $paso->getDescripcion());
        }

        // 4. Convertir Nutrientes a DTOs
        $nutrientesDTO = [];
        foreach ($receta->getValoresNutritivos() as $nutriente) {
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

        // 5. Crear el RecetaDTO final
        return new RecetaDTO(
            $receta->getId(),
            $receta->getTitulo(),
            $receta->getComensales(),
            $tipoDTO,
            $ingredientesDTO,
            $pasosDTO,
            $nutrientesDTO
        );
    }
}
