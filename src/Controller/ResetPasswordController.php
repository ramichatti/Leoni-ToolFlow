<?php

namespace App\Controller;

use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\UserNotFoundException;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class ResetPasswordController extends AbstractController
{
    #[Route('/reset-password', name: 'app_reset_password', methods: ['GET'])]
    public function requestReset(): Response
    {
        return $this->render('reset_password/request.html.twig');
    }

    #[Route('/reset-password/send', name: 'app_reset_password_send', methods: ['POST'])]
    public function sendResetEmail(Request $request, UserRepository $userRepository, MailerInterface $mailer, SessionInterface $session): Response
    {
        $emailAddress = $request->request->get('email');
        $user = $userRepository->findOneBy(['email' => $emailAddress]);

        if (!$user) {
            $this->addFlash('error', 'No account found with this email.');
            return $this->redirectToRoute('app_reset_password');
        }

        // Generate a random 6-digit code
        $code = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        
        // Store the code and its expiration time in session
        $session->set('reset_code', [
            'code' => $code,
            'email' => $emailAddress,
            'expires' => time() + (5 * 60) // Valid for 5 minutes
        ]);

        try {
            // Create and send email
            $email = (new Email())
                ->from('ramichatti14@gmail.com')
                ->to($user->getEmail())
                ->subject('LEONI ToolFlow - Password Reset Verification Code')
                ->priority(Email::PRIORITY_HIGH)
                ->html($this->renderView('reset_password/email.html.twig', [
                    'code' => $code,
                    'userEmail' => $user->getEmail()
                ]));

            try {
                $mailer->send($email);
                // Success message removed
            } catch (\Symfony\Component\Mailer\Exception\TransportExceptionInterface $e) {
                // Log the actual error for debugging
                error_log('Mailer Error: ' . $e->getMessage());
                throw $e;
            }
        } catch (\Exception $e) {
            $this->addFlash('error', 'Mail Error: ' . $e->getMessage());
            return $this->redirectToRoute('app_reset_password');
        }

        return $this->redirectToRoute('app_reset_password_verify');

        return $this->redirectToRoute('app_reset_password_verify');
    }

    #[Route('/reset-password/verify', name: 'app_reset_password_verify')]
    public function verifyCode(): Response
    {
        return $this->render('reset_password/verify.html.twig');
    }

    #[Route('/reset-password/validate', name: 'app_reset_password_validate', methods: ['POST'])]
    public function validateCode(
        Request $request, 
        SessionInterface $session, 
        UserRepository $userRepository
    ): Response {
        $submittedCode = $request->request->get('code');
        $resetData = $session->get('reset_code');
        $currentTime = time();

        if (!$resetData) {
            $this->addFlash('error', 'No verification code found. Please request a new one.');
            return $this->redirectToRoute('app_reset_password');
        }

        if ($currentTime > $resetData['expires']) {
            $session->remove('reset_code'); // Clear expired code
            $this->addFlash('error', 'Verification code has expired. Please request a new one.');
            return $this->redirectToRoute('app_reset_password');
        }

        if ($submittedCode !== $resetData['code']) {
            $this->addFlash('error', 'Invalid verification code. Please try again.');
            return $this->redirectToRoute('app_reset_password_verify');
        }

        // Add remaining time check
        $remainingTime = $resetData['expires'] - $currentTime;
        if ($remainingTime <= 0) {
            $session->remove('reset_code');
            $this->addFlash('error', 'Verification code has expired. Please request a new one.');
            return $this->redirectToRoute('app_reset_password');
        }

        return $this->redirectToRoute('app_reset_password_change');
    }

    #[Route('/reset-password/change', name: 'app_reset_password_change')]
    public function changePassword(): Response
    {
        return $this->render('reset_password/change.html.twig');
    }

    #[Route('/reset-password/update', name: 'app_reset_password_update', methods: ['POST'])]
    public function updatePassword(
        Request $request,
        SessionInterface $session,
        UserRepository $userRepository,
        UserPasswordHasherInterface $passwordHasher
    ): Response {
        $resetData = $session->get('reset_code');
        if (!$resetData) {
            return $this->redirectToRoute('app_reset_password');
        }

        $user = $userRepository->findOneBy(['email' => $resetData['email']]);
        if (!$user) {
            throw new UserNotFoundException();
        }

        $password = $request->request->get('password');
        $hashedPassword = $passwordHasher->hashPassword($user, $password);
        $user->setPassword($hashedPassword);
        $userRepository->save($user, true);

        $session->remove('reset_code');
        // Success message removed
        return $this->redirectToRoute('app_login');
    }
}
