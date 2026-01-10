<?php

namespace App\Controller;

use App\Entity\TipoNutriente;
use App\Model\TipoNutrienteDTO;
use App\Model\RespuestaErrorDTO;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

final class NutrientTypeController extends AbstractController
{
    public function __construct(private EntityManagerInterface $entityManager) {}

    #[Route('/nutrient-types', name: 'search_nutrients', methods: ['GET'])]
    public function getAllNutrientTypes(): JsonResponse
    {
        try {

            // Recupero la información de BBDD
            $tiposRecetasBBDD = $this->entityManager
                                        ->getRepository(TipoNutriente::class)
                                        ->findAll();

            // Convierto de Entidades a DTO
            $tipoRestaurantesDTO = [];
            foreach ($tiposRecetasBBDD as $tipoRecetaEntidad) {
                $tipoRestaurantesDTO[] = new TipoNutrienteDTO($tipoRecetaEntidad->getId(),$tipoRecetaEntidad->getNombre(),$tipoRecetaEntidad->getUnidad());
            }

            return $this->json($tipoRestaurantesDTO);

        } catch (\Throwable $th) {
            $errorMensaje = new RespuestaErrorDTO(1000, "Error General");
            return new JsonResponse($errorMensaje, 500);
        }
    }
}
