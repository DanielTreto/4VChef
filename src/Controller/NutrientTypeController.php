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
            $tiposNutrienteBBDD = $this->entityManager
                ->getRepository(TipoNutriente::class)
                ->findAll();

            // Convierto de Entidades a DTO
            $tipoNutrientesDTO = [];
            foreach ($tiposNutrienteBBDD as $tipoNutrienteEntidad) {
                $tipoNutrientesDTO[] = new TipoNutrienteDTO($tipoNutrienteEntidad->getId(), $tipoNutrienteEntidad->getNombre(), $tipoNutrienteEntidad->getUnidad());
            }

            return $this->json($tipoNutrientesDTO);
        } catch (\Throwable $th) {
            $errorMensaje = new RespuestaErrorDTO(500, "Error al recuperar tipos de nutrientes");
            return new JsonResponse($errorMensaje, 500);
        }
    }
}
