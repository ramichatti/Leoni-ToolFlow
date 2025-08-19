<?php

namespace App\Controller;

use App\Entity\Tool;
use App\Form\ToolType;
use App\Repository\IORepository;
use App\Repository\ToolRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/tools')]
#[IsGranted('ROLE_ADMIN')]
class ToolController extends AbstractController
{
    #[Route('/', name: 'app_admin_tools')]
    public function index(ToolRepository $toolRepository, IORepository $ioRepository): Response
    {
        $tools = $toolRepository->findAll();
        
        $toolsWithLastIO = [];
        foreach ($tools as $tool) {
            $lastIO = $ioRepository->findLastIOByTool($tool->getId());
            $toolsWithLastIO[] = [
                'tool' => $tool,
                'lastIO' => $lastIO,
            ];
        }
        
        return $this->render('admin/tools/index.html.twig', [
            'toolsWithLastIO' => $toolsWithLastIO,
        ]);
    }
    
    #[Route('/new', name: 'app_admin_tools_new')]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $tool = new Tool();
        $form = $this->createForm(ToolType::class, $tool);
        $form->handleRequest($request);
        
        if ($form->isSubmitted() && $form->isValid()) {
            // Set the current user as the creator
            $tool->setCreatedBy($this->getUser());
            
            $entityManager->persist($tool);
            $entityManager->flush();
            
            $this->addFlash('success', 'Tool created successfully.');
            
            return $this->redirectToRoute('app_admin_tools');
        }
        
        return $this->render('admin/tools/new.html.twig', [
            'tool' => $tool,
            'form' => $form->createView(),
        ]);
    }
    
    #[Route('/{id}/edit', name: 'app_admin_tools_edit')]
    public function edit(Request $request, Tool $tool, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(ToolType::class, $tool);
        $form->handleRequest($request);
        
        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();
            
            $this->addFlash('success', 'Tool updated successfully.');
            
            return $this->redirectToRoute('app_admin_tools');
        }
        
        return $this->render('admin/tools/edit.html.twig', [
            'tool' => $tool,
            'form' => $form->createView(),
        ]);
    }
    
    #[Route('/{id}/delete', name: 'app_admin_tools_delete', methods: ['POST'])]
    public function delete(Request $request, Tool $tool, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$tool->getId(), $request->request->get('_token'))) {
            $entityManager->remove($tool);
            $entityManager->flush();
            
            if ($request->isXmlHttpRequest()) {
                return new JsonResponse([
                    'success' => true,
                    'message' => sprintf('Tool "%s" has been deleted.', $tool->getId())
                ]);
            }
            
            $this->addFlash('success', 'Tool deleted successfully.');
        } else {
            if ($request->isXmlHttpRequest()) {
                return new JsonResponse([
                    'success' => false,
                    'message' => 'Invalid CSRF token.'
                ], 400);
            }
        }
        
        return $this->redirectToRoute('app_admin_tools');
    }
} 