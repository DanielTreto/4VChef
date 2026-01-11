<?php

namespace App\Controller;

use App\Model\RespuestaErrorDTO;
use App\Model\TipoRecetaDTO;
use App\Entity\TipoReceta;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

final class RecipeTypeController extends AbstractController
{

    public function __construct(private EntityManagerInterface $entityManager) {}

    #[Route('/recipe-types', name: 'search_recipe_types', methods: ['GET'])]
    public function getAllRecipeTypes(): JsonResponse
    {
        try {

            // Recupero la información de BBDD
            $tiposRecetasBBDD = $this->entityManager
                                        ->getRepository(TipoReceta::class)
                                        ->findAll();

            // Convierto de Entidades a DTO
            $tipoRecetaDTO = [];
            foreach ($tiposRecetasBBDD as $tipoRecetaEntidad) {
                $tipoRecetaDTO[] = new TipoRecetaDTO($tipoRecetaEntidad->getId(),$tipoRecetaEntidad->getNombre(),$tipoRecetaEntidad->getDescripcion());
            }

            return $this->json($tipoRecetaDTO);

        } catch (\Throwable $th) {
            $errorMensaje = new RespuestaErrorDTO(1000, "Error al recuperar tipos de recetas");
            return new JsonResponse($errorMensaje, 500);
        }
    }
}
