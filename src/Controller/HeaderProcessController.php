<?php

namespace App\Controller;

use App\Entity\HeaderProcess;
use Doctrine\ORM\EntityManagerInterface; // Add this line
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

class HeaderProcessController extends AbstractController
{
    #[Route('/header/process', name: 'app_header_process')]
    public function saveHeaderProcess(EntityManagerInterface $entityManager): JsonResponse
    {
        // Create a new HeaderProcess instance
        $headerProcess = new HeaderProcess();
       
        // Persist the user entity
        $entityManager->persist($headerProcess);
        // Flush the changes to the database
        $entityManager->flush();
    
        // Return a JSON response indicating success
        return new JsonResponse(['message' => 'Header Process saved successfully']);
    }
}
