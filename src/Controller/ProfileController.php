<?php

namespace App\Controller;

use App\Form\ProfileContactType;
use App\Form\ChangePasswordType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Csrf\CsrfToken;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Regex;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/profile')]
class ProfileController extends AbstractController
{
    #[Route('/', name: 'app_profile')]
    public function index(): Response
    {
        $user = $this->getUser();
        if (!$user) {
            throw new AccessDeniedException('You must be logged in to access this page');
        }

        return $this->render('profile/index.html.twig');
    }
    
    #[Route('/update-contact', name: 'app_profile_update_contact', methods: ['POST'])]
    public function updateContact(Request $request, EntityManagerInterface $entityManager, ValidatorInterface $validator, CsrfTokenManagerInterface $csrfTokenManager): JsonResponse
    {
        try {
            // Check if it's an AJAX request
            if (!$request->headers->has('X-Requested-With') || $request->headers->get('X-Requested-With') !== 'XMLHttpRequest') {
                return $this->json([
                    'success' => false,
                    'message' => 'Only AJAX requests are allowed',
                ], Response::HTTP_BAD_REQUEST);
            }
            
            $user = $this->getUser();
            if (!$user) {
                return $this->json([
                    'success' => false,
                    'message' => 'You must be logged in to update your profile',
                ], Response::HTTP_UNAUTHORIZED);
            }
            
            // Validate CSRF token
            $token = $request->request->get('_token');
            if (!$token || !$csrfTokenManager->isTokenValid(new CsrfToken('profile_contact', $token))) {
                return $this->json([
                    'success' => false,
                    'message' => 'Invalid CSRF token',
                ], Response::HTTP_BAD_REQUEST);
            }
            
            // Get data from request - directly access form fields
            $tel = $request->request->get('profile_contact_tel');
            $address = $request->request->get('profile_contact_address');
            
            // Validate phone number
            $errors = [];
            if ($tel) {
                $telConstraints = [
                    new Regex([
                        'pattern' => '/^[0-9]{8,15}$/',
                        'message' => 'Phone number should contain only digits and be between 8 and 15 characters long',
                    ]),
                ];
                
                $telViolations = $validator->validate($tel, $telConstraints);
                if (count($telViolations) > 0) {
                    $errors['tel'] = $telViolations[0]->getMessage();
                }
            }
            
            // Validate address
            if ($address) {
                $addressConstraints = [
                    new Length([
                        'max' => 255,
                        'maxMessage' => 'Address cannot be longer than {{ limit }} characters',
                    ]),
                ];
                
                $addressViolations = $validator->validate($address, $addressConstraints);
                if (count($addressViolations) > 0) {
                    $errors['address'] = $addressViolations[0]->getMessage();
                }
            }
            
            // If there are errors, return them
            if (count($errors) > 0) {
                return $this->json([
                    'success' => false,
                    'message' => 'There were errors in your submission',
                    'errors' => $errors,
                ], Response::HTTP_BAD_REQUEST);
            }
            
            // Update user
            $user->setTel($tel);
            $user->setAddress($address);
            
            $entityManager->flush();
            
            return $this->json([
                'success' => true,
                'message' => 'Your contact information has been updated successfully',
                'tel' => $tel,
                'address' => $address,
            ]);
        } catch (\Exception $e) {
            return $this->json([
                'success' => false,
                'message' => 'An error occurred while processing your request: ' . $e->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
    
    #[Route('/change-password', name: 'app_profile_change_password', methods: ['POST'])]
    public function changePassword(Request $request, EntityManagerInterface $entityManager, UserPasswordHasherInterface $passwordHasher, ValidatorInterface $validator, CsrfTokenManagerInterface $csrfTokenManager): JsonResponse
    {
        try {
            // Check if it's an AJAX request
            if (!$request->headers->has('X-Requested-With') || $request->headers->get('X-Requested-With') !== 'XMLHttpRequest') {
                return $this->json([
                    'success' => false,
                    'message' => 'Only AJAX requests are allowed',
                ], Response::HTTP_BAD_REQUEST);
            }
            
            $user = $this->getUser();
            if (!$user) {
                return $this->json([
                    'success' => false,
                    'message' => 'You must be logged in to change your password',
                ], Response::HTTP_UNAUTHORIZED);
            }
            
            // Validate CSRF token
            $token = $request->request->get('_token');
            if (!$token || !$csrfTokenManager->isTokenValid(new CsrfToken('change_password', $token))) {
                return $this->json([
                    'success' => false,
                    'message' => 'Invalid CSRF token',
                ], Response::HTTP_BAD_REQUEST);
            }
            
            // Get data from request - directly access form fields
            $currentPassword = $request->request->get('change_password_currentPassword');
            $newPassword = $request->request->get('change_password_newPassword_first');
            $confirmPassword = $request->request->get('change_password_newPassword_second');
            
            // Validate inputs
            $errors = [];
            
            // Check if current password is provided
            if (!$currentPassword) {
                $errors['currentPassword'] = 'Please enter your current password';
            } else if (!$passwordHasher->isPasswordValid($user, $currentPassword)) {
                $errors['currentPassword'] = 'Current password is incorrect';
            }
            
            // Validate new password
            if (!$newPassword) {
                $errors['newPassword'] = 'Please enter a new password';
            } else {
                $passwordConstraints = [
                    new NotBlank(['message' => 'Please enter a password']),
                    new Length([
                        'min' => 8,
                        'minMessage' => 'Your password should be at least {{ limit }} characters',
                        'max' => 4096,
                    ]),
                    new Regex([
                        'pattern' => '/[a-z]+/',
                        'message' => 'Your password should contain at least one lowercase letter',
                    ]),
                    new Regex([
                        'pattern' => '/[A-Z]+/',
                        'message' => 'Your password should contain at least one uppercase letter',
                    ]),
                    new Regex([
                        'pattern' => '/[0-9]+/',
                        'message' => 'Your password should contain at least one number',
                    ]),
                ];
                
                $passwordViolations = $validator->validate($newPassword, $passwordConstraints);
                if (count($passwordViolations) > 0) {
                    $errors['newPassword'] = $passwordViolations[0]->getMessage();
                }
                
                // Check if new password is the same as current password
                if ($currentPassword && $newPassword && $passwordHasher->isPasswordValid($user, $newPassword)) {
                    $errors['newPassword'] = 'Your new password cannot be the same as your current password';
                }
            }
            
            // Check if passwords match
            if ($newPassword !== $confirmPassword) {
                $errors['confirmPassword'] = 'The password fields must match';
            }
            
            // If there are errors, return them
            if (count($errors) > 0) {
                return $this->json([
                    'success' => false,
                    'message' => 'There were errors in your submission',
                    'errors' => $errors,
                ], Response::HTTP_BAD_REQUEST);
            }
            
            // Update password
            $hashedPassword = $passwordHasher->hashPassword($user, $newPassword);
            $user->setPassword($hashedPassword);
            
            $entityManager->flush();
            
            return $this->json([
                'success' => true,
                'message' => 'Your password has been changed successfully',
            ]);
        } catch (\Exception $e) {
            return $this->json([
                'success' => false,
                'message' => 'An error occurred while processing your request: ' . $e->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
} 