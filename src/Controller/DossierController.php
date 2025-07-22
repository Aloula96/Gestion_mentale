<?php

namespace App\Controller;

use App\Entity\Dossier;
use App\Repository\DossierRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route('/api')]
class DossierController extends AbstractController
{
    // GET pour afficher tout les dossier
    #[Route('/dossiers', name: 'api_dossier_index', methods: ['GET'])]
    public function index(DossierRepository $repo): JsonResponse
    {
        $dossiers = $repo->findAll();

        $data = [];
        foreach ($dossiers as $dossier) {
            $data[] = [
                'id' => $dossier->getId(),
                'title' => $dossier->getTitle(),
                'image' => $dossier->getImage(),
                'description' => $dossier->getDescription(),
            ];
        }

        return $this->json($data);
    }

    // GET  pour Afficher un seul dossier
    #[Route('/dossiers/{id}', name: 'api_dossier_show', methods: ['GET'])]
    public function show(Dossier $dossier): JsonResponse
    {
        $data = [
            'id' => $dossier->getId(),
            'title' => $dossier->getTitle(),
            'image' => $dossier->getImage(),
            'description' => $dossier->getDescription(),

        ];

        return $this->json($data);
    }

    // POST - Créer un nouveau dossier
    #[Route('/dossiers/add', name: 'api_dossier_create', methods: ['POST'])]
    public function create(Request $request, EntityManagerInterface $em): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $dossier = new Dossier();
        $dossier->setTitle($data['title'] ?? '');
        $dossier->setImage($data['image'] ?? '');
        $dossier->setDescription($data['description'] ?? '');
        $dossier->setCreatedAt(new \DateTime());

        $em->persist($dossier);
        $em->flush();

        return $this->json([
            'message' => 'Famille ajoutée avec succès',
            'id' => $dossier->getId(),
        ], 201);
    }

    // PUT - Modifier un dossier
    #[Route('/dossiers/edit/{id}', name: 'api_dossier_update', methods: ['PUT'])]
    public function update(Request $request, Dossier $dossier, EntityManagerInterface $em): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $dossier->setTitle($data['title'] ?? $dossier->getTitle());
        $dossier->setImage($data['image'] ?? $dossier->getImage());
        $dossier->setDescription($data['description'] ?? $dossier->getDescription());
        $dossier->setCreatedAt(new \DateTime());

        $em->flush();

        return $this->json([
            'message' => 'Famille modifiée avec succès',
        ]);
    }

    // DELETE - Supprimer un dossier
    #[Route('/dossiers/{id}', name: 'api_dossier_delete', methods: ['DELETE'])]
    public function delete(Dossier $dossier, EntityManagerInterface $em): JsonResponse
    {
        $em->remove($dossier);
        $em->flush();

        return $this->json([
            'message' => 'Famille supprimée avec succès',
        ]);
    }
}
