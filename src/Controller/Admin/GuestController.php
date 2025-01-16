<?php

namespace App\Controller\Admin;

use App\Entity\User;
use App\Form\UserType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request as HttpFoundationRequest;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;

final class GuestController extends AbstractController
{
    #[Route('admin/guest/add', name: 'admin_add_guest')]
    public function addGuest(HttpFoundationRequest $request, EntityManagerInterface $entityManager, UserPasswordHasherInterface $passwordHasher)
    {
        $user = new User();

        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $hashedPassword = $passwordHasher->hashPassword($user, $user->getPassword());
            $user->setPassword($hashedPassword);
            $entityManager->persist($user);
            $entityManager->flush();

            return $this->redirectToRoute('guests');
        }
        return $this->render('front/guest-add.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('admin/guest/{id}', name: 'admin_access_guest')]
    public function guestAccess(): Response
    {
        return $this->render('front/guest.html.twig', [
            'controller_name' => 'GuestController',
        ]);
    }

    #[Route('admin/guest/delete', name: 'admin_delete_guest')]
    public function guestDelete(): Response
    {
        return $this->render('front/guest.html.twig', [
            'controller_name' => 'GuestController',
        ]);
    }
}
