<?php

namespace App\Controller\Admin;

use App\Entity\IO;
use App\Repository\IORepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/io')]
#[IsGranted('ROLE_ADMIN')]
class IOController extends AbstractController
{
    private $entityManager;
    private $ioRepository;

    public function __construct(
        EntityManagerInterface $entityManager,
        IORepository $ioRepository
    ) {
        $this->entityManager = $entityManager;
        $this->ioRepository = $ioRepository;
    }

    #[Route('/', name: 'app_admin_io')]
    public function index(): Response
    {
        $ios = $this->ioRepository->findBy([], ['dateEntre' => 'DESC']);

        return $this->render('admin/io/index.html.twig', [
            'ios' => $ios
        ]);
    }

    #[Route('/{id}', name: 'app_admin_io_show', methods: ['GET'])]
    public function show(IO $io): Response
    {
        return $this->render('admin/io/show.html.twig', [
            'io' => $io
        ]);
    }

    #[Route('/{id}/edit', name: 'app_admin_io_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, IO $io): Response
    {
        if ($request->isMethod('POST')) {
            // Get form data
            $section = $request->request->get('section');
            $crimpingHeight = $request->request->get('crimpingHeight');
            $insulationHeight = $request->request->get('insulationHeight');
            $crimpingWidth = $request->request->get('crimpingWidth');
            $insulationWidth = $request->request->get('insulationWidth');
            $conformite = $request->request->get('conformite');
            
            // Validate data
            $errors = [];
            
            if (!is_numeric($section) || $section <= 0) {
                $errors[] = 'Section must be a positive number';
            }
            
            if (!is_numeric($crimpingHeight) || $crimpingHeight <= 0) {
                $errors[] = 'Crimping height must be a positive number';
            }
            
            if (!is_numeric($insulationHeight) || $insulationHeight <= 0) {
                $errors[] = 'Insulation height must be a positive number';
            }
            
            if (!is_numeric($crimpingWidth) || $crimpingWidth <= 0) {
                $errors[] = 'Crimping width must be a positive number';
            }
            
            if (!is_numeric($insulationWidth) || $insulationWidth <= 0) {
                $errors[] = 'Insulation width must be a positive number';
            }
            
            if (empty($errors)) {
                // Update IO entity
                $io->setSection((float) $section);
                $io->setCrimpingHeight((float) $crimpingHeight);
                $io->setInsulationHeight((float) $insulationHeight);
                $io->setCrimpingWidth((float) $crimpingWidth);
                $io->setInsulationWidth((float) $insulationWidth);
                
                // Check if measurements are within tolerance and update conformity
                $measure = $io->getMeasure();
                $tolerance = 0.1;
                $isConforme = true;
                
                if (abs($io->getSection() - $measure->getSection()) > $tolerance) {
                    $isConforme = false;
                }
                
                if (abs($io->getCrimpingHeight() - $measure->getCrimpingHeight()) > $tolerance) {
                    $isConforme = false;
                }
                
                if (abs($io->getInsulationHeight() - $measure->getInsulationHeight()) > $tolerance) {
                    $isConforme = false;
                }
                
                if (abs($io->getCrimpingWidth() - $measure->getCrimpingWidth()) > $tolerance) {
                    $isConforme = false;
                }
                
                if (abs($io->getInsulationWidth() - $measure->getInsulationWidth()) > $tolerance) {
                    $isConforme = false;
                }
                
                // Set conformity status
                $io->setConformite($isConforme ? 'conforme' : 'non conforme');
                
                // Save changes
                $this->entityManager->flush();
                
                $this->addFlash('success', 'IO record updated successfully');
                return $this->redirectToRoute('app_admin_io');
            } else {
                foreach ($errors as $error) {
                    $this->addFlash('danger', $error);
                }
            }
        }
        
        return $this->render('admin/io/edit.html.twig', [
            'io' => $io
        ]);
    }

    #[Route('/{id}', name: 'app_admin_io_delete', methods: ['POST'])]
    public function delete(Request $request, IO $io): Response
    {
        if ($this->isCsrfTokenValid('delete'.$io->getId(), $request->request->get('_token'))) {
            $this->entityManager->remove($io);
            $this->entityManager->flush();
            $this->addFlash('success', 'IO record deleted successfully');
        }

        return $this->redirectToRoute('app_admin_io', [], Response::HTTP_SEE_OTHER);
    }
} 