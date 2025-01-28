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

class HomeController extends AbstractController
{
    private $entityManager;
    private $userRepository;

    public function __construct(EntityManagerInterface $entityManager, UserRepository $userRepository)
    {
        $this->entityManager = $entityManager;
        $this->userRepository = $userRepository;
    }

    #[Route("/", name: "home")]
    public function home()
    {
        return $this->render('front/home.html.twig');
    }

    #[Route("/guests", name: "guests")]
    public function guests(Request $request, UserRepository $userRepository)
    {
        $page = (int) $request->query->get('page', 1);
        $limit = 5;
        $paginator = $userRepository->findActiveUsersPaginated($page, $limit);

        return $this->render('front/guests.html.twig', [
            'guests' => $paginator,
            'currentPage' => $page,
            'totalPages' => ceil($paginator->count() / $limit),
        ]);
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
        return $this->render('front/guest.html.twig', [
            'guest' => $guest,
            'images' => $images,
            'currentPage' => $page,
        ]);
    }

    #[Route("/portfolio/{id}", name: "portfolio")]
    public function portfolio(?int $id = null)
    {
        $albums = $this->entityManager->getRepository(Album::class)->findAll();
        $album = $id ? $this->entityManager->getRepository(Album::class)->find($id) : null;
        $user = $this->entityManager->getRepository(User::class)->findOneByAdmin(true);

        $medias = $album
            ? $this->entityManager->getRepository(Media::class)->findByAlbum($album)
            : $this->entityManager->getRepository(Media::class)->findByUser($user);
        return $this->render('front/portfolio.html.twig', [
            'albums' => $albums,
            'album' => $album,
            'medias' => $medias
        ]);
    }

    #[Route("/about", name: "about")]
    public function about()
    {
        return $this->render('front/about.html.twig');
    }

    #[Route('/admin', name: 'admin')]
    public function admin()
    {
        return $this->render('admin.html.twig');
    }
}
