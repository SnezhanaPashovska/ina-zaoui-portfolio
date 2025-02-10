<?php

namespace App\Controller\Admin;

use App\Entity\User;
use App\Form\UserType;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;

final class GuestController extends AbstractController
{

    private UserRepository $userRepository;

    public function __construct(UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    #[Route('admin/guest', name: 'admin_list_guest')]
    public function guestList(Request $request, UserRepository $userRepository): Response
    {
        $page = (int) $request->query->get('page', '1');
        $limit = 5;
        $user = $this->getUser();
        $isAdmin = $user !== null && in_array('ROLE_ADMIN', $user->getRoles(), true);

        if ($isAdmin) {
            $paginator = $userRepository->findAllPaginated($page, $limit);
        } else {
            $paginator = $userRepository->findActiveUsersPaginated($page, $limit);
        }

        $guests = iterator_to_array($paginator->getIterator());
        $totalCount = count($paginator);
        $totalPages = ceil($totalCount / $limit);

        return $this->render('guests-list.html.twig', [
            'guests' => $guests,
            'currentPage' => $page,
            'totalPages' => $totalPages,
        ]);
    }

    #[Route('admin/guest/add', name: 'admin_add_guest')]
    public function addGuest(Request $request, UserPasswordHasherInterface $passwordHasher): Response
    {
        $user = new User();

        $user->setRoles(['ROLE_USER']);

        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $plainPassword = $user->getPassword() ?? '';
            $hashedPassword = $passwordHasher->hashPassword($user, $plainPassword);
            $user->setPassword($hashedPassword);

            $this->userRepository->save($user);

            return $this->redirectToRoute('admin_list_guest');
        }

        $allGuests = $this->userRepository->findBy(['admin' => false]);
        return $this->render('admin\guest-add.html.twig', [
            'form' => $form->createView(),
            'guests' => $allGuests
        ]);
    }

    #[Route('admin/guest/{id}/toggle', name: 'admin_access_guest', methods: ['POST'])]
    public function guestAccess(int $id, UserRepository $userRepository, EntityManagerInterface $entityManager): Response
    {
        $guest = $userRepository->find($id);

        if ($guest === null) {
            throw $this->createNotFoundException('Utilisateur non trouvé');
        }

        $guest->setIsActive(!$guest->isActive());

        $entityManager->persist($guest);

        $entityManager->flush();

        $this->addFlash(
            'success',
            'Le statut de l\'invité a été mis à jour avec succès.'
        );

        return $this->redirectToRoute('admin_list_guest');
    }

    #[Route('admin/guest/delete/{id}', name: 'admin_delete_guest', methods: ['POST'])]
    public function guestDelete(int $id, UserRepository $userRepository, EntityManagerInterface $entityManager): Response
    {
        $guest = $userRepository->find($id);
        if ($guest === null) {
            $this->addFlash('error', 'Invité introuvable.');
            return $this->redirectToRoute('admin_list_guest');
        }

        $entityManager->remove($guest);
        $entityManager->flush();

        $this->addFlash('delete', 'Invité supprimé avec succès.');

        return $this->redirectToRoute('admin_list_guest');
    }
}
