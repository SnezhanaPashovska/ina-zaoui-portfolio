<?php

namespace App\Controller\Admin;

use App\Entity\Album;
use App\Entity\Media;
use App\Form\AlbumType;
use App\Form\MediaType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Response;

class AlbumController extends AbstractController
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    #[Route("/admin/album", name: "admin_album_index")]
    public function index(): Response
    {
        $albums = $this->entityManager->getRepository(Album::class)->findAll();

        return $this->render('admin/album/index.html.twig', ['albums' => $albums]);
    }

    #[Route("/admin/album/add", name: "admin_album_add")]
    public function add(Request $request): Response
    {
        $album = new Album();
        $form = $this->createForm(AlbumType::class, $album);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->persist($album);
            $this->entityManager->flush();

            return $this->redirectToRoute('admin_album_index');
        }

        return $this->render('admin/album/add.html.twig', ['form' => $form->createView()]);
    }

    #[Route("/admin/album/update/{id}", name: "admin_album_update")]
    public function update(Request $request, int $id): Response
    {
        $album = $this->entityManager->getRepository(Album::class)->find($id);

        if ($album === null) {
            throw $this->createNotFoundException('Album not found');
        }

        $form = $this->createForm(AlbumType::class, $album);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->flush();

            return $this->redirectToRoute('admin_album_index');
        }

        return $this->render('admin/album/update.html.twig', ['form' => $form->createView()]);
    }

    #[Route("/admin/album/delete/{id}", name: "admin_album_delete")]
    public function delete(int $id): Response
    {
        $album = $this->entityManager->getRepository(Album::class)->find($id);

        if ($album !== null) {
            $this->entityManager->remove($album);
            $this->entityManager->flush();
        }

        return $this->redirectToRoute('admin_album_index');
    }
}
