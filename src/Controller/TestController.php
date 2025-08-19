<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class TestController extends AbstractController
{
    #[Route('/test', name: 'app_test')]
    public function index(): Response
    {
        // This route requires authentication
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        
        $user = $this->getUser();
        $roles = $user->getRoles();
        
        return $this->render('test/index.html.twig', [
            'user' => $user,
            'roles' => $roles,
        ]);
    }
    
    #[Route('/test/public', name: 'app_test_public')]
    public function publicPage(): Response
    {
        // This route is public
        return new Response('This is a public page that anyone can access.');
    }
} 