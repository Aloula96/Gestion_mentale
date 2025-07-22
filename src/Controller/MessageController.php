<?php

namespace App\Controller;

use DateTime;
use App\Entity\User;
use App\Entity\Dossier;
use App\Entity\Message;
use App\Repository\UserRepository;
use App\Repository\DossierRepository;
use App\Repository\MessageRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route('/api')]
final class MessageController extends AbstractController
{

    //tous les messages
    #[Route('/message', name: 'app_message', methods: ['GET'])]
    public function index(MessageRepository $messageRepository,): JsonResponse
    {
        $messages = $messageRepository->findAll();


        $data = [];
        foreach ($messages as $message) {
            $data[] = [
                'id' => $message->getId(),
                'object' => $message->getObject(),
                'content' => $message->getContent(),
                'createdAt' => $message->getCreatedAt()->format('Y-m-d H:i:s'),
                'user' => [
                    'id' => $message->getUser()->getId(),
                    'last_name' => $message->getUser()->getLastname(),
                    'first_name' => $message->getUser()->getFirstname()
                ],


            ];
            //est ce que j inclus le dossier ici?            
        }
        return $this->json($data, Response::HTTP_OK);
    }


    //voir un message
    #[Route('/message/{id}', name: 'show_message', methods: ['GET'])]
    public function showMessage($id, MessageRepository $messageRepository): JsonResponse
    {
        $messages = $messageRepository->find($id);

        if (!$messages) {
            return $this->json(['message' => 'Message not found'], Response::HTTP_NOT_FOUND);
        }

        $data = [
            'id' => $messages->getId(),
            'object' => $messages->getObject(),
            'content' => $messages->getContent(),
            'createdAt' => $messages->getCreatedAt()->format('Y-m-d H:i:s'),
            'user' => [
                'id' => $messages->getUser()->getId(),
                'last_name' => $messages->getUser()->getLastName(),
                'first_name' => $messages->getUser()->getFirstName()
            ],
        ];

        return $this->json($data, Response::HTTP_OK);
    }

    // créer un message
    #[Route('/message/add', name: 'add_message', methods: ['POST'])]
    public function addMessage(Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (!isset($data['object'], $data['content'], $data['dossier_id'], $data['user_id'])) {
            return $this->json(['message' => 'Données manquantes'], Response::HTTP_BAD_REQUEST);
        }

        $user = $entityManager->getRepository(User::class)->find($data['user_id']);
        if (!$user) {
            return $this->json(['message' => 'Utilisateur introuvable'], Response::HTTP_NOT_FOUND);
        }

        $dossier = $entityManager->getRepository(Dossier::class)->find($data['dossier_id']);
        if (!$dossier) {
            return $this->json(['message' => 'Dossier introuvable'], Response::HTTP_NOT_FOUND);
        }

        $message = new Message();
        $message->setObject($data['object']);
        $message->setContent($data['content']);
        $message->setCreatedAt(new \DateTime());
        $message->setUser($user);
        $message->setDossier($dossier);

        $entityManager->persist($message);
        $entityManager->flush();

        return $this->json(['message' => 'Message envoyé avec succès'], Response::HTTP_CREATED);
    }


    // supprimer un message
    #[Route('/message/delete/{id}', name: 'delete_message', methods: ['DELETE'])]
    public function deleteMessage($id, MessageRepository $messageRepository, EntityManagerInterface $entityManager): JsonResponse
    {
        $message = $messageRepository->find($id);
        if (!$message) {
            return $this->json(['message' => 'Message not found'], Response::HTTP_NOT_FOUND);
        }
        $entityManager->remove($message);
        $entityManager->flush();
        return $this->json(['message' => 'Message supprimé avec succès'], Response::HTTP_OK);
    }
}
