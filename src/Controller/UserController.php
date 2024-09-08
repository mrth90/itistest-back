<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\User;

class UserController extends AbstractController
{
    #[Route('/user', name: 'app_user')]
    //create a function to save a user to the database using EntityManagerInterface
    // ...

    public function saveUser(EntityManagerInterface $entityManager, User $user): JsonResponse
    {
       
        // Persist the user entity
        $entityManager->persist($user);
        // Flush the changes to the database
        $entityManager->flush();

        // Return a JSON response indicating success
        return new JsonResponse(['message' => 'User saved successfully']);
    }
}