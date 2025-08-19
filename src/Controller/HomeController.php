<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\User\UserInterface;

class HomeController extends AbstractController
{
    #[Route('/', name: 'app_home')]
    public function index(): Response
    {
        // Make sure the user is authenticated
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        
        /** @var UserInterface $user */
        $user = $this->getUser();
        
        return $this->render('home/index.html.twig', [
            'user' => $user,
        ]);
    }
} 