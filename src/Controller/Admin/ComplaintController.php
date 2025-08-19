<?php

namespace App\Controller\Admin;

use App\Entity\Complaint;
use App\Form\ComplaintResponseType;
use App\Repository\ComplaintRepository;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

#[Route('/admin/complaints')]
#[IsGranted('ROLE_ADMIN')]
class ComplaintController extends AbstractController
{
    #[Route('/', name: 'app_admin_complaints')]
    public function index(ComplaintRepository $complaintRepository): Response
    {
        return $this->render('admin/complaint/index.html.twig', [
            'complaints' => $complaintRepository->findAllOrdered(),
        ]);
    }

    #[Route('/{id}', name: 'app_admin_complaint_show', methods: ['GET'])]
    public function show(Complaint $complaint): Response
    {
        return $this->render('admin/complaint/show.html.twig', [
            'complaint' => $complaint,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_admin_complaint_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Complaint $complaint, EntityManagerInterface $entityManager, MailerInterface $mailer, \Twig\Environment $twig, UrlGeneratorInterface $urlGenerator): Response
    {
        $form = $this->createForm(ComplaintResponseType::class, $complaint);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $complaint->setUpdatedAt(new DateTime());
            $entityManager->flush();

            // Send email to user when complaint is replied to
            $user = $complaint->getUser();
            if ($user && $user->getEmail()) {
                $loginUrl = $urlGenerator->generate('app_login', [], UrlGeneratorInterface::ABSOLUTE_URL);
                $htmlBody = $twig->render('email.html.twig', [
                    'firstName' => $user->getFirstName(),
                    'loginUrl' => $loginUrl,
                    'subject' => $complaint->getSubject(),
                ]);
                $email = (new Email())
                    ->from('ramichatti14@gmail.com')
                    ->to($user->getEmail())
                    ->subject('Your complaint has been processed')
                    ->html($htmlBody);
                $mailer->send($email);
            }

            $this->addFlash('success', 'Complaint status has been updated successfully.');
            return $this->redirectToRoute('app_admin_complaints');
        }

        return $this->render('admin/complaint/edit.html.twig', [
            'complaint' => $complaint,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}/delete', name: 'app_admin_complaint_delete', methods: ['POST'])]
    public function delete(Request $request, Complaint $complaint, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$complaint->getId(), $request->request->get('_token'))) {
            $entityManager->remove($complaint);
            $entityManager->flush();
            $this->addFlash('success', 'Complaint has been deleted successfully.');
        }

        return $this->redirectToRoute('app_admin_complaints');
    }
} 