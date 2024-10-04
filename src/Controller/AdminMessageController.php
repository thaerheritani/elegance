<?php

namespace App\Controller;

use App\Entity\Message;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

class AdminMessageController extends AbstractController
{
    #[Route('/admin/messages', name: 'admin_message_index')]
    public function index(EntityManagerInterface $entityManager): Response
    {
        // Récupérer tous les messages (répondus ou non)
        $messages = $entityManager->getRepository(Message::class)->findAll();

        return $this->render('admin_dashboard/messages/index.html.twig', [
            'messages' => $messages,
        ]);
    }

    #[Route('/admin/messages/{id}/reply', name: 'admin_message_reply')]
    public function reply(Request $request, Message $message, MailerInterface $mailer, EntityManagerInterface $entityManager): Response
    {
        if ($request->isMethod('POST')) {
            $replyContent = $request->request->get('reply');

            // Envoi de l'email au client
            $email = (new Email())
                ->from('admin@example.com') // Adresse de l'admin
                ->to($message->getEmail())
                ->subject('Réponse à votre message')
                ->text($replyContent);

            $mailer->send($email);

            // Marquer le message comme répondu
            $message->setAnswered(true);
            $entityManager->flush(); // Utiliser l'entity manager injecté pour sauvegarder

            // Message de succès et redirection
            $this->addFlash('success', 'Réponse envoyée avec succès.');
            return $this->redirectToRoute('admin_message_index');
        }

        return $this->render('admin_dashboard/messages/reply.html.twig', [
            'message' => $message,
        ]);
    }
}
