<?php

namespace App\Controller;

use App\Repository\ToolRepository;
use App\Repository\IORepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/tools')]
class ToolListController extends AbstractController
{
    #[Route('/', name: 'app_tools')]
    public function listTools(ToolRepository $toolRepository, IORepository $ioRepository): Response
    {
        $tools = $toolRepository->findAll();
        $toolsWithLastIO = [];
        
        foreach ($tools as $tool) {
            // Get the last IO for this tool
            $lastIO = $ioRepository->findLastIOByTool($tool->getId());
            
            $toolsWithLastIO[] = [
                'tool' => $tool,
                'lastIO' => $lastIO
            ];
        }
        
        return $this->render('tool/list.html.twig', [
            'toolsWithLastIO' => $toolsWithLastIO,
        ]);
    }
} 