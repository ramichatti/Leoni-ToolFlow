<?php

namespace App\Controller;

use App\Repository\ToolRepository;
use App\Repository\MeasureRepository;
use App\Repository\IORepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ToolDetailsController extends AbstractController
{
    #[Route('/tool/details/{id}', name: 'app_tool_details')]
    public function index(string $id, ToolRepository $toolRepository, MeasureRepository $measureRepository, IORepository $ioRepository): Response
    {
        $tool = $toolRepository->find($id);

        if (!$tool) {
            throw $this->createNotFoundException('Tool not found');
        }

        // Vérifier si l'utilisateur a le rôle ROLE_MAINTENANCE
        if (!$this->isGranted('ROLE_MAINTENANCE')) {
            throw $this->createAccessDeniedException('Access denied');
        }

        // Get all measures
        $measures = $measureRepository->findAll();

        // Get last IO for availability/machine display
        $lastIO = $ioRepository->findLastIOByTool($id);

        return $this->render('tool_details/index.html.twig', [
            'tool' => $tool,
            'measures' => $measures,
            'lastIO' => $lastIO,
        ]);
    }
}