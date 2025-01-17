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

    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    #[Route('admin/guest', name: 'admin_list_guest')]
    public function guestList(EntityManagerInterface $entityManager)
    {
        $allGuests = $this->entityManager->getRepository(User::class)->findBy([
            'admin' => false,
        ]);

        return $this->render('guests-list.html.twig', [
            'guests' => $allGuests
        ]);
    }

    #[Route('admin/guest/add', name: 'admin_add_guest')]
    public function addGuest(HttpFoundationRequest $request, EntityManagerInterface $entityManager, UserPasswordHasherInterface $passwordHasher)
    {
        $user = new User();

        $user->setRoles(['ROLE_USER']);

        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $hashedPassword = $passwordHasher->hashPassword($user, $user->getPassword());
            $user->setPassword($hashedPassword);
            $entityManager->persist($user);
            $entityManager->flush();

            return $this->redirectToRoute('guests');
        }
        return $this->render('guests-list.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('admin/guest/{id}/toggle', name: 'admin_access_guest', methods: ['POST'])]
    public function guestAccess(int $id): Response
    {
        $guest = $this->entityManager->getRepository(User::class)->find($id);

        if (!$guest) {
            throw $this->createNotFoundException('Utilisateur non trouvé');
        }
        $guest->setIsActive(!$guest->isActive());

        $this->entityManager->flush();

        $this->addFlash(
            'success',
            'Le statut de l\'invité a été mis à jour avec succès.'
        );

        return $this->redirectToRoute('admin_list_guest');
    }

    #[Route('admin/guest/delete/{id}', name: 'admin_delete_guest', methods: ['POST'])]
    public function guestDelete(int $id): Response
    {
        $guest = $this->entityManager->getRepository(User::class)->find($id);
        if (!$guest) {
            $this->addFlash('error', 'Invité introuvable.');
        }

        $this->entityManager->remove($guest);
        $this->entityManager->flush();

        $this->addFlash('delete', 'Invité supprimé avec succès.');

        return $this->redirectToRoute('admin_list_guest');
    }
}
