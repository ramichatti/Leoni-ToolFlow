<?php

namespace App\Controller\Api;

use App\Entity\Tool;
use App\Repository\ToolRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/api/tools', name: 'api_tools_')]
#[IsGranted('ROLE_MAINTENANCE')]
class ToolController extends AbstractController
{
    private $toolRepository;
    private $entityManager;

    public function __construct(ToolRepository $toolRepository, EntityManagerInterface $entityManager)
    {
        $this->toolRepository = $toolRepository;
        $this->entityManager = $entityManager;
    }

    #[Route('/{id}', name: 'get', methods: ['GET'])]
    public function getTool(string $id): JsonResponse
    {
        $tool = $this->toolRepository->findToolById($id);

        if (!$tool) {
            return $this->json([
                'success' => false,
                'message' => 'Tool not found'
            ], 404);
        }

        // Convertir les énums en chaînes pour l'affichage
        return $this->json([
            'success' => true,
            'tool' => [
                'id' => $tool->getId(),
                'description' => $tool->getDescription()->value,
                'manufacturer' => $tool->getManufacturer()->value,
                'createdAt' => $tool->getCreatedAt()->format('Y-m-d H:i:s'),
                'createdBy' => [
                    'firstName' => $tool->getCreatedBy()->getFirstName(),
                    'lastName' => $tool->getCreatedBy()->getLastName()
                ],
                'exists' => true
            ]
        ]);
    }

    #[Route('/io', name: 'io', methods: ['POST'])]
    public function handleIO(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $toolId = $data['toolId'] ?? null;
        $action = $data['action'] ?? null;

        if (!$toolId || !$action || !in_array($action, ['in', 'out'])) {
            return $this->json([
                'success' => false,
                'message' => 'Invalid request parameters'
            ], 400);
        }

        $tool = $this->toolRepository->findToolById($toolId);

        if (!$tool) {
            return $this->json([
                'success' => false,
                'message' => 'Tool not found'
            ], 404);
        }

        // Ici, vous pouvez ajouter la logique pour gérer les mouvements d'outils
        // Par exemple, créer une nouvelle entrée dans une table de mouvements

        return $this->json([
            'success' => true,
            'message' => sprintf('Tool successfully %s', $action === 'in' ? 'checked in' : 'checked out'),
            'action' => $action
        ]);
    }
} 