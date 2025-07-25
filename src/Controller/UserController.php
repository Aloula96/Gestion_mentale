<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\Dossier;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Entity\Role;    
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route('/api/users')]
class UserController extends AbstractController
{
    #[Route('', methods: ['GET'])]
    public function index(EntityManagerInterface $em): JsonResponse
    {
        $users = $em->getRepository(User::class)->findAll();
        $data = [];

        foreach ($users as $user) {
            $data[] = [
                'id' => $user->getId(),
                'email' => $user->getEmail(),
                'roles' => $user->getRoles(),
                'first_name' => $user->getFirstName(),
                'last_name' => $user->getLastName(),
                'phone_number' => $user->getPhoneNumber(),
                'created_at' => $user->getCreatedAt()->format('Y-m-d H:i:s'),
            ];
        }

        return $this->json($data);
    }

    #[Route('/{id}', methods: ['GET'])]
    public function show(EntityManagerInterface $em, int $id): JsonResponse
    {
        $user = $em->getRepository(User::class)->find($id);

        if (!$user) {
            return $this->json(['message' => 'User not found'], 404);
        }

        $data = [
            'id' => $user->getId(),
            'email' => $user->getEmail(),
            'roles' => $user->getRoles(),
        
            'first_name' => $user->getFirstName(),
            'last_name' => $user->getLastName(),
            'phone_number' => $user->getPhoneNumber(),
            'created_at' => $user->getCreatedAt()->format('Y-m-d H:i:s'),
        ];

        return $this->json($data);
    }

    #[Route('/create', methods: ['POST'])]
public function create(Request $request, EntityManagerInterface $em): JsonResponse
{
    $data = json_decode($request->getContent(), true);

    // Récupération du rôle (on récupère bien un objet Role, pas User)
    $role = $em->getRepository(Role::class)->find($data['role_id']);
    if (!$role) {
        return $this->json(['message' => 'Rôle introuvable'], Response::HTTP_NOT_FOUND);
    }

    // Récupération du dossier
    $dossier = $em->getRepository(Dossier::class)->find($data['dossier_id']);
    if (!$dossier) {
        return $this->json(['message' => 'Dossier introuvable'], Response::HTTP_NOT_FOUND);
    }

    // Création de l'utilisateur
    $user = new User();
    $user->setEmail($data['email']);
    $user->setRoles($data['roles']); // Tableau de rôles Symfony
    $user->setPassword(password_hash($data['password'], PASSWORD_DEFAULT));
    $user->setFirstName($data['first_name']);
    $user->setLastName($data['last_name']);
    $user->setPhoneNumber($data['phone_number']);
    $user->setCreatedAt(new \DateTime());
    $user->setRole($role);
    $user->setDossier($dossier);

    $em->persist($user);
    $em->flush();

    return $this->json([
        'message' => 'Utilisateur créé avec succès',
        'id' => $user->getId()
    ], Response::HTTP_CREATED);
}

    #[Route('/edit/{id}', methods: ['PUT'])]
    public function update(Request $request, EntityManagerInterface $em, int $id): JsonResponse
    {
        $user = $em->getRepository(User::class)->find($id);

        if (!$user) {
            return $this->json(['message' => 'User not found'], 404);
        }

        $data = json_decode($request->getContent(), true);

        $user->setEmail($data['email'] ?? $user->getEmail());
        $user->setRoles($data['roles'] ?? $user->getRoles());
        if (isset($data['password'])) {
            $user->setPassword(password_hash($data['password'], PASSWORD_DEFAULT));
        }
   
        $user->setFirstName($data['first_name'] ?? $user->getFirstName());
        $user->setLastName($data['last_name'] ?? $user->getLastName());
        $user->setPhoneNumber($data['phone_number'] ?? $user->getPhoneNumber());

        $em->flush();

        return $this->json(['message' => 'User updated']);
    }

    #[Route('delete/{id}', methods: ['DELETE'])]
    public function delete(EntityManagerInterface $em, int $id): JsonResponse
    {
        $user = $em->getRepository(User::class)->find($id);

        if (!$user) {
            return $this->json(['message' => 'User not found'], 404);
        }

        $em->remove($user);
        $em->flush();

        return $this->json(['message' => 'User deleted']);
    }
}
