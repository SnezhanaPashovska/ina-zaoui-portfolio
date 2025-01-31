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
use App\Service\AuthenticationService;


class HomeController extends AbstractController
{
    private $entityManager;
    private $userRepository;
    private $authService;

    public function __construct(EntityManagerInterface $entityManager, UserRepository $userRepository, AuthenticationService $authService)
    {
        $this->entityManager = $entityManager;
        $this->userRepository = $userRepository;
        $this->authService = $authService;
    }

    #[Route("/", name: "home")]
    public function home()
    {
        $authenticationData = $this->authService->getAuthenticationData();

        return $this->render('front/home.html.twig', $authenticationData);
    }

    #[Route("/guests", name: "guests")]
    public function guests(Request $request, UserRepository $userRepository)
    {
        $page = max(1, (int) $request->query->get('page', 1));
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
    public function guest(int $id, Request $request, MediaRepository $mediaRepository)
    {
        $guest = $this->entityManager->getRepository(User::class)->find($id);
        if (!$guest) {
            throw $this->createNotFoundException('Guest not found.');
        }

        $page = $request->query->getInt('page', 1);
        $limit = 5;

        $images = $mediaRepository->findPaginatedMediaByUser($id, $page, $limit);

        $authenticationData = $this->authService->getAuthenticationData();

        return $this->render('front/guest.html.twig', array_merge([
            'guest' => $guest,
            'images' => $images,
            'currentPage' => $page,
        ], $authenticationData));
    }




    #[Route("/portfolio/{id}", name: "portfolio")]
    public function portfolio(?int $id = null)
    {

        $albums = $this->entityManager->getRepository(Album::class)->findAll();
        $album = $id ? $this->entityManager->getRepository(Album::class)->find($id) : null;

        if ($album) {
            $medias = $this->entityManager->getRepository(Media::class)->findByAlbum($album);
        } else {
            $medias = $this->entityManager->getRepository(Media::class)->findAll();
        }

        $medias = array_filter($medias, function ($media) {
            return $media->getUser()->isActive(); // Only keep media for active users
        });


        $authenticationData = $this->authService->getAuthenticationData();

        return $this->render('front/portfolio.html.twig', array_merge([
            'albums' => $albums,
            'album' => $album,
            'medias' => $medias
        ], $authenticationData));
    }

    #[Route("/about", name: "about")]
    public function about()
    {
        $authenticationData = $this->authService->getAuthenticationData();
        return $this->render('front/about.html.twig', $authenticationData);
    }

    #[Route('/admin', name: 'admin')]
    public function admin()
    {
        return $this->render('admin.html.twig');
    }
}
