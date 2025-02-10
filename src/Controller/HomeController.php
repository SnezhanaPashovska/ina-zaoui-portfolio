<?php

namespace App\Controller;

use App\Entity\Album;
use App\Entity\Media;
use App\Entity\User;
use App\Repository\MediaRepository;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Service\AuthenticationService;


class HomeController extends AbstractController
{
    private EntityManagerInterface $entityManager;
    private AuthenticationService $authService;

    public function __construct(EntityManagerInterface $entityManager, AuthenticationService $authService)
    {
        $this->entityManager = $entityManager;
        $this->authService = $authService;
    }

    #[Route("/", name: "home")]
    public function home(): Response

    {
        $authenticationData = $this->authService->getAuthenticationData();

        return $this->render('front/home.html.twig', $authenticationData);
    }

    #[Route("/guests", name: "guests")]
    public function guests(Request $request, UserRepository $userRepository): Response

    {
        $page = max(1, (int) $request->query->get('page', '1'));
        $limit = 5;

        $paginator = $userRepository->findActiveUsersPaginated($page, $limit);

        $authenticationData = $this->authService->getAuthenticationData();


        return $this->render('front/guests.html.twig', array_merge([
            'guests' => $paginator,
            'currentPage' => $page,
            'totalPages' => ceil(count($paginator) / $limit),
        ], $authenticationData));
    }


    #[Route("/guest/{id}", name: "guest")]
    public function guest(int $id, Request $request, MediaRepository $mediaRepository): Response

    {
        $guest = $this->entityManager->getRepository(User::class)->find($id);
        if ($guest === null) {
            throw $this->createNotFoundException('Guest not found.');
        }

        $page = $request->query->getInt('page', 1);
        $limit = 9;

        $images = $mediaRepository->findPaginatedMediaByUser($id, $page, $limit);

        $totalImages = count($mediaRepository->findBy(['user' => $id]));

        $totalPages = ceil($totalImages / $limit);

        $authenticationData = $this->authService->getAuthenticationData();

        return $this->render('front/guest.html.twig', array_merge([
            'guest' => $guest,
            'images' => $images,
            'currentPage' => $page,
            'totalPages' => $totalPages,
        ], $authenticationData));
    }

    #[Route("/portfolio", name: "portfolio_all")]
    #[Route("/portfolio/{id}", name: "portfolio")]
    public function portfolio(?int $id = null, Request $request): Response

    {

        $albums = $this->entityManager->getRepository(Album::class)->findAll();
        $album = $id !== null ? $this->entityManager->getRepository(Album::class)->find($id) : null;

        $page = $request->query->getInt('page', 1);
        $limit = 9;

        if ($album !== null) {
            $medias = $this->entityManager->getRepository(Media::class)->findByAlbum($album);
        } else {
            $medias = $this->entityManager->getRepository(Media::class)->findAll();
        }

        $medias = array_filter($medias, function ($media) {
            $user = $media->getUser();
            return $user !== null && $user->isActive();
        });

        $totalMedias = count($medias);
        $totalPages = ceil($totalMedias / $limit);


        $medias = array_slice($medias, ($page - 1) * $limit, $limit);
        $authenticationData = $this->authService->getAuthenticationData();

        return $this->render('front/portfolio.html.twig', array_merge([
            'albums' => $albums,
            'album' => $album,
            'medias' => $medias,
            'currentPage' => $page,
            'totalPages' => $totalPages,
        ], $authenticationData));
    }

    #[Route("/about", name: "about")]
    public function about(): Response

    {
        $authenticationData = $this->authService->getAuthenticationData();
        return $this->render('front/about.html.twig', $authenticationData);
    }

    #[Route('/admin', name: 'admin')]
    public function admin(): Response

    {
        return $this->render('admin.html.twig');
    }
}
