<?php

namespace App\Controller;

use App\Entity\User;
use App\Enum\Role;
use App\Form\UserType;
use App\Repository\UserRepository;
use App\Repository\ToolRepository;
use App\Repository\IORepository;
use App\Repository\MeasureRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin')]
#[IsGranted('ROLE_ADMIN')]
class AdminController extends AbstractController
{
    #[Route('/', name: 'app_admin_dashboard')]
    public function dashboard(UserRepository $userRepository, ToolRepository $toolRepository, MeasureRepository $measureRepository, IORepository $ioRepository): Response
    {
        $users = $userRepository->findAll();
        $tools = $toolRepository->findAll();
        $measures = $measureRepository->findAll();
        $ios = $ioRepository->findAll();

        $usersByRole = [
            'ROLE_MAINTENANCE' => 0,
            'ROLE_ADMIN' => 0,
            'ROLE_SUPER_ADMIN' => 0,
        ];
        
        foreach ($users as $user) {
            $role = $user->getRole()->value;
            if (isset($usersByRole[$role])) {
                $usersByRole[$role]++;
            }
        }
        
        // Get tool data for charts
        $toolsByManufacturer = $toolRepository->countByManufacturer();
        $toolsByDescription = $toolRepository->countByDescriptionType();
        
        // Get IO data for charts
        $iosByStatus = $ioRepository->countByStatus();
        $iosByDate = $ioRepository->countByDate();
        $iosByManufacturerAndStatus = $ioRepository->countByManufacturerAndStatus();
        $iosByDescriptionAndStatus = $ioRepository->countByDescriptionAndStatus();
        
        // Get current month's data
        $currentMonth = (int)(new \DateTime())->format('m');
        $currentYear = (int)(new \DateTime())->format('Y');
        $monthlyData = $ioRepository->countByMonth($currentMonth, $currentYear);
        
        // Get measure data for charts
        $averageMeasurements = $measureRepository->getAverageMeasurements();
        $measuresByDate = $measureRepository->countByDate();
        $measuresBySection = $measureRepository->getMeasurementsBySection();
        
        $totalUsers = count($users);
        $totalTools = count($tools);
        $totalMeasures = count($measures);
        $totalios = count($ios);
        $totalTickets = 0;
       
        return $this->render('admin/dashboard.html.twig', [
            'totalUsers' => $totalUsers,
            'usersByRole' => $usersByRole,
            'totalTools' => $totalTools,
            'totalMeasures' => $totalMeasures,
            'totalios' => $totalios,
            'totalTickets' => $totalTickets,
            'users' => $users,
            'toolsByManufacturer' => $toolsByManufacturer,
            'toolsByDescription' => $toolsByDescription,
            'iosByStatus' => $iosByStatus,
            'iosByDate' => $iosByDate,
            'averageMeasurements' => $averageMeasurements,
            'measuresByDate' => $measuresByDate,
            'measuresBySection' => $measuresBySection,
            'iosByManufacturerAndStatus' => $iosByManufacturerAndStatus,
            'iosByDescriptionAndStatus' => $iosByDescriptionAndStatus,
            'monthlyData' => $monthlyData,
        ]);
    }
    
    #[Route('/users', name: 'app_admin_users')]
    public function usersList(UserRepository $userRepository): Response
    {
        $users = $userRepository->findAll();
        
        return $this->render('admin/users/index.html.twig', [
            'users' => $users,
        ]);
    }
    
    #[Route('/users/new', name: 'app_admin_users_new')]
    public function newUser(Request $request, EntityManagerInterface $entityManager, UserPasswordHasherInterface $passwordHasher): Response
    {
        $user = new User();
        $form = $this->createForm(UserType::class, $user, [
            'require_password' => true,
        ]);
        $form->handleRequest($request);
        
        if ($form->isSubmitted() && $form->isValid()) {
            $hashedPassword = $passwordHasher->hashPassword(
                $user,
                $user->getPassword()
            );
            $user->setPassword($hashedPassword);
            
            $entityManager->persist($user);
            $entityManager->flush();
            
            $this->addFlash('success', 'User created successfully.');
            
            return $this->redirectToRoute('app_admin_users');
        }
        
        return $this->render('admin/users/new.html.twig', [
            'user' => $user,
            'form' => $form->createView(),
        ]);
    }
    
    #[Route('/users/{id}/edit', name: 'app_admin_users_edit')]
    public function editUser(Request $request, User $user, EntityManagerInterface $entityManager, UserPasswordHasherInterface $passwordHasher): Response
    {
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);
        
        if ($form->isSubmitted() && $form->isValid()) {
            $plainPassword = $form->get('plainPassword')->getData();
            if ($plainPassword) {
                $hashedPassword = $passwordHasher->hashPassword(
                    $user,
                    $plainPassword
                );
                $user->setPassword($hashedPassword);
            }
            
            $entityManager->flush();
            
            $this->addFlash('success', 'User updated successfully.');
            
            return $this->redirectToRoute('app_admin_users');
        }
        
        return $this->render('admin/users/edit.html.twig', [
            'user' => $user,
            'form' => $form->createView(),
        ]);
    }
    
    #[Route('/users/{id}/delete', name: 'app_admin_users_delete', methods: ['POST'])]
    public function deleteUser(Request $request, User $user, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$user->getId(), $request->request->get('_token'))) {
            if ($this->getUser() === $user) {
                if ($request->isXmlHttpRequest()) {
                    return new JsonResponse([
                        'success' => false,
                        'message' => 'You cannot delete your own account.'
                    ], 403);
                }
                
                $this->addFlash('danger', 'You cannot delete your own account.');
                return $this->redirectToRoute('app_admin_users');
            }
            
            $entityManager->remove($user);
            $entityManager->flush();
            
            if ($request->isXmlHttpRequest()) {
                return new JsonResponse([
                    'success' => true,
                    'message' => sprintf('User "%s" has been deleted.', $user->getFirstName() . ' ' . $user->getLastName())
                ]);
            }
            
            $this->addFlash('success', 'User deleted successfully.');
        } else {
            if ($request->isXmlHttpRequest()) {
                return new JsonResponse([
                    'success' => false,
                    'message' => 'Invalid CSRF token.'
                ], 400);
            }
        }
        
        return $this->redirectToRoute('app_admin_users');
    }
    
    #[Route('/users/{id}/block', name: 'app_admin_users_block', methods: ['POST'])]
    public function blockUser(Request $request, User $user, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('block'.$user->getId(), $request->request->get('_token'))) {
            if ($this->getUser() === $user) {
                if ($request->isXmlHttpRequest()) {
                    return new JsonResponse([
                        'success' => false,
                        'message' => 'You cannot block your own account.'
                    ], 403);
                }
                
                $this->addFlash('danger', 'You cannot block your own account.');
                return $this->redirectToRoute('app_admin_users');
            }
            
            $user->setIsBlocked(true);
            $entityManager->flush();
            
            if ($request->isXmlHttpRequest()) {
                return new JsonResponse([
                    'success' => true,
                    'message' => sprintf('User "%s" has been blocked.', $user->getFirstName() . ' ' . $user->getLastName())
                ]);
            }
            
            $this->addFlash('warning', sprintf('User "%s" has been blocked.', $user->getFirstName() . ' ' . $user->getLastName()));
            return $this->redirectToRoute('app_admin_users');
        }
        
        if ($request->isXmlHttpRequest()) {
            return new JsonResponse([
                'success' => false,
                'message' => 'Invalid CSRF token.'
            ], 400);
        }
        
        return $this->redirectToRoute('app_admin_users');
    }
    
    #[Route('/users/{id}/unblock', name: 'app_admin_users_unblock', methods: ['POST'])]
    public function unblockUser(Request $request, User $user, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('unblock'.$user->getId(), $request->request->get('_token'))) {
            $user->setIsBlocked(false);
            $entityManager->flush();
            
            if ($request->isXmlHttpRequest()) {
                return new JsonResponse([
                    'success' => true,
                    'message' => sprintf('User "%s" has been unblocked.', $user->getFirstName() . ' ' . $user->getLastName())
                ]);
            }
            
            $this->addFlash('success', sprintf('User "%s" has been unblocked.', $user->getFirstName() . ' ' . $user->getLastName()));
            return $this->redirectToRoute('app_admin_users');
        }
        
        if ($request->isXmlHttpRequest()) {
            return new JsonResponse([
                'success' => false,
                'message' => 'Invalid CSRF token.'
            ], 400);
        }
        
        return $this->redirectToRoute('app_admin_users');
    }
} 