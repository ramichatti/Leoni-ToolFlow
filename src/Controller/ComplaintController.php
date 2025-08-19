<?php

namespace App\Controller;

use App\Entity\Complaint;
use App\Form\ComplaintType;
use App\Repository\ComplaintRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/complaints')]
#[IsGranted('ROLE_USER')]
class ComplaintController extends AbstractController
{
    #[Route('/', name: 'app_complaints')]
    public function index(ComplaintRepository $complaintRepository): Response
    {
        $user = $this->getUser();
        $complaints = $complaintRepository->findByUser($user);

        return $this->render('complaint/index.html.twig', [
            'complaints' => $complaints,
        ]);
    }

    #[Route('/new', name: 'app_complaint_new')]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $complaint = new Complaint();
        $form = $this->createForm(ComplaintType::class, $complaint);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $complaint->setUser($this->getUser());
            
            $entityManager->persist($complaint);
            $entityManager->flush();

            $this->addFlash('success', 'Your complaint has been submitted successfully.');
            return $this->redirectToRoute('app_complaints');
        }

        return $this->render('complaint/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}', name: 'app_complaint_show', methods: ['GET'])]
    public function show(Complaint $complaint): Response
    {
        // Security check - users can only view their own complaints
        if ($complaint->getUser() !== $this->getUser()) {
            throw $this->createAccessDeniedException('You cannot access this complaint.');
        }

        return $this->render('complaint/show.html.twig', [
            'complaint' => $complaint,
        ]);
    }
} 