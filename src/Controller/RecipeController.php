<?php

namespace App\Controller;

use App\Entity\Paso;
use App\Entity\Receta;
use App\Model\PasoDTO;
use App\Model\RecetaDTO;
use App\Entity\TipoReceta;
use App\Entity\Valoracion;
use App\Entity\Ingrediente;
use App\Model\TipoRecetaDTO;
use App\Entity\TipoNutriente;
use App\Model\IngredienteDTO;
use App\Model\TipoNutrienteDTO;
use App\Model\RespuestaErrorDTO;
use App\Model\ValorNutritivoDTO;
use App\Entity\RecetasNutrientes;
use App\Model\ValoracionDTO;
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
                $errorMensaje = new RespuestaErrorDTO(400, "El tipo debe ser un entero positivo");
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
            $errorMensaje = new RespuestaErrorDTO(500, "Error al recuperar recetas");
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
            if (!isset($data['title'])) {
                $errorMensaje = new RespuestaErrorDTO(400, "El campo title es obligatorio");
                return new JsonResponse($errorMensaje, 400);
            }

            if (!isset($data['number-diner'])) {
                $errorMensaje = new RespuestaErrorDTO(400, "El campo number-diner es obligatorio");
                return new JsonResponse($errorMensaje, 400);
            }

            if ($data['number-diner'] <= 0) {
                $errorMensaje = new RespuestaErrorDTO(400, "El número de comensales debe ser positivo");
                return new JsonResponse($errorMensaje, 400);
            }

            // Repositorios
            $tipoRecetaRepo = $this->entityManager->getRepository(TipoReceta::class);
            $tipoNutrienteRepo = $this->entityManager->getRepository(TipoNutriente::class);

            // Validar Tipo de Receta (Solo si se envía)
            $tipoReceta = null;
            if (isset($data['type-id'])) {
                if (!$this->esEnteroPositivo((string)$data['type-id'])) {
                     $errorMensaje = new RespuestaErrorDTO(400, "El type-id debe ser un entero positivo");
                     return new JsonResponse($errorMensaje, 400);
                }
                $tipoReceta = $tipoRecetaRepo->find($data['type-id']);
                if (!$tipoReceta) {
                    $errorMensaje = new RespuestaErrorDTO(400, "El tipo de receta no existe");
                    return new JsonResponse($errorMensaje, 400);
                }
            }

            // Validar Ingredientes (> 0)
            if (!isset($data['ingredients']) || !is_array($data['ingredients']) || count($data['ingredients']) < 1) {
                $errorMensaje = new RespuestaErrorDTO(400, "La receta debe tener al menos 1 ingrediente");
                return new JsonResponse($errorMensaje, 400);
            }

            // Validar Pasos (> 0)
            if (!isset($data['steps']) || !is_array($data['steps']) || count($data['steps']) < 1) {
                $errorMensaje = new RespuestaErrorDTO(400, "La receta debe tener al menos 1 paso");
                return new JsonResponse($errorMensaje, 400);
            }

            // Validar Nutrientes
            if (isset($data['nutrients']) && is_array($data['nutrients'])) {
                foreach ($data['nutrients'] as $index => $nutData) {
                    // Buscamos si existe el tipo
                    $existeTipo = $tipoNutrienteRepo->find($nutData['type-id']);
                    if (!$existeTipo) {
                        $errorMensaje = new RespuestaErrorDTO(400, "El tipo de nutriente con ID " . $nutData['type-id'] . " no existe");
                        return new JsonResponse($errorMensaje, 400);
                    }
                }
            }

            // Crear Entidad Receta
            $receta = new Receta();
            $receta->setTitulo($data['title']);
            $receta->setComensales($data['number-diner']);
            $receta->setTipo($tipoReceta);
            $receta->setEliminada(false);

            // Añadir Ingredientes
            foreach ($data['ingredients'] as $ingData) {
                if (!isset($ingData['name'], $ingData['quantity'], $ingData['unit'])) {
                    $errorMensaje = new RespuestaErrorDTO(400, "Datos de ingrediente incompletos");
                    return new JsonResponse($errorMensaje, 400);
                }
                if ($ingData['quantity'] <= 0) {
                     $errorMensaje = new RespuestaErrorDTO(400, "La cantidad del ingrediente debe ser positiva (> 0)");
                     return new JsonResponse($errorMensaje, 400);
                }
                $ingrediente = new Ingrediente();
                $ingrediente->setNombre($ingData['name']);
                $ingrediente->setCantidad($ingData['quantity']);
                $ingrediente->setUnidad($ingData['unit']);
                $receta->addIngrediente($ingrediente);
            }

            // Añadir Pasos
            foreach ($data['steps'] as $stepData) {
                if (!isset($stepData['order'], $stepData['description'])) {
                    $errorMensaje = new RespuestaErrorDTO(400, "Datos de paso incompletos");
                    return new JsonResponse($errorMensaje, 400);
                }
                $paso = new Paso();
                $paso->setOrden($stepData['order']);
                $paso->setDescripcion($stepData['description']);
                $receta->addPaso($paso);
            }

            // Añadir Nutrientes
            if (isset($data['nutrients']) && is_array($data['nutrients'])) {
                foreach ($data['nutrients'] as $nutData) {
                    if (!isset($nutData['type-id'], $nutData['quantity'])) {
                        $errorMensaje = new RespuestaErrorDTO(400, "Datos de nutriente incompletos");
                        return new JsonResponse($errorMensaje, 400);
                    }
                    if ($nutData['quantity'] < 0) {
                        $errorMensaje = new RespuestaErrorDTO(400, "La cantidad del nutriente no puede ser negativa");
                        return new JsonResponse($errorMensaje, 400);
                    }
                    $tipoNutriente = $tipoNutrienteRepo->find($nutData['type-id']);
                    $nutriente = new RecetasNutrientes();
                    $nutriente->setTipo($tipoNutriente);
                    $nutriente->setCantidad($nutData['quantity']);
                    $receta->addValoresNutritivo($nutriente);
                }
            }

            /// Persistimos
            $this->entityManager->persist($receta);
            $this->entityManager->flush();

            /// Monto Respuesta
            return $this->json($this->toDTO($receta));
        } catch (\Throwable $th) {
            $errorMensaje = new RespuestaErrorDTO(500, "Error al crear la receta");
            return new JsonResponse($errorMensaje, 500);
        }
    }

    #[Route('/recipes/{id}', name: 'delete_recipe', methods: ['DELETE'])]
    public function deleteRecipe(int $id): JsonResponse
    {
        try {
            // Buscamos la receta por su ID
            $receta = $this->entityManager->getRepository(Receta::class)->find($id);

            // Verificamos si existe
            if (!$receta) {
                $errorMensaje = new RespuestaErrorDTO(400, "No se encontró la receta con id " . $id);
                return new JsonResponse($errorMensaje, 400);
            }

            // Verificamos si ya está eliminada
            if ($receta->isEliminada()) {
                $errorMensaje = new RespuestaErrorDTO(400, "La receta con id " . $id . " ya está eliminada");
                return new JsonResponse($errorMensaje, 400);
            }

            // Realizamos el borrado
            $receta->setEliminada(true);
            $this->entityManager->flush();

            // Devolvemos el objeto Receta
            return $this->json($this->toDTO($receta));
        } catch (\Throwable $th) {
            $errorMensaje = new RespuestaErrorDTO(500, "Error al eliminar la receta");
            return new JsonResponse($errorMensaje, 500);
        }
    }

    #[Route('/recipes/{id}/rating/{rate}', name: 'vote_recipe', methods: ['POST'])]
    public function voteRecipe(string $id, string $rate, Request $request): JsonResponse
    {
        try {
            // Validar ID receta
            if (!$this->esEnteroPositivo($id)) {
                $errorMensaje = new RespuestaErrorDTO(400, "El ID de la receta debe ser un entero positivo");
                return new JsonResponse($errorMensaje, 400);
            }

            // Validar calificación
            if (!ctype_digit($rate) || $rate < 0 || $rate > 5) {
                $errorMensaje = new RespuestaErrorDTO(400, "La calificación de la receta debe ser un entero entre 0 y 5");
                return new JsonResponse($errorMensaje, 400);
            }

            $id = (int)$id;
            $rate = (int)$rate;

            // Validar existencia de la receta
            $receta = $this->entityManager->getRepository(Receta::class)->find($id);
            if (!$receta || $receta->isEliminada()) {
                $errorMensaje = new RespuestaErrorDTO(400, "No se encontró la receta con id " . $id);
                return new JsonResponse($errorMensaje, 400);
            }

            // Validar IP única
            $clientIp = $request->getClientIp() ?? 'Unknown';

            // Repositorio de Valoraciones
            $valoracionRepo = $this->entityManager->getRepository(Valoracion::class);

            // Buscamos si ya existe una valoración de esta IP para esta Receta
            $votoExistente = $valoracionRepo->findOneBy([
                'receta' => $receta,
                'ip' => $clientIp
            ]);

            if ($votoExistente) {
                $errorMensaje = new RespuestaErrorDTO(400, "Error: Ya has votado esta receta desde esta IP");
                return new JsonResponse($errorMensaje, 400);
            }

            // Persistir Valoración
            $valoracion = new Valoracion();
            $valoracion->setCalificacion($rate);
            $valoracion->setIp($clientIp);
            $valoracion->setReceta($receta);

            $this->entityManager->persist($valoracion);
            $this->entityManager->flush();

            // Devolvemos el objeto Receta
            return $this->json($this->toDTO($receta));
        } catch (\Throwable $th) {
            $errorMensaje = new RespuestaErrorDTO(500, "Error al registrar el voto");
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

        // 5. Valoraciones (Calculado)
        $valoraciones = $receta->getValoraciones();
        $numVotos = count($valoraciones);
        $avgCalificacion = 0;
        if ($numVotos > 0) {
            $total = 0;
            foreach ($valoraciones as $v) {
                $total += $v->getCalificacion();
            }
            $avgCalificacion = $total / $numVotos;
        }
        $ratingDTO = new ValoracionDTO($numVotos, $avgCalificacion);

        // 6. Crear el RecetaDTO final
        return new RecetaDTO(
            $receta->getId(),
            $receta->getTitulo(),
            $receta->getComensales(),
            $tipoDTO,
            $ingredientesDTO,
            $pasosDTO,
            $nutrientesDTO,
            $ratingDTO
        );
    }
}
