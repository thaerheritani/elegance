<?php

namespace App\Controller;

use App\Entity\Message;
use App\Form\MessageType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

class ContactController extends AbstractController
{
    #[Route('/contact', name: 'app_contact')]
    public function contact(Request $request, EntityManagerInterface $entityManager, MailerInterface $mailer): Response
    {
        $message = new Message();
        $form = $this->createForm(MessageType::class, $message);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $message->setSentAt(new \DateTime());
            $message->setAnswered(false);
            $entityManager->persist($message);
            $entityManager->flush();

            // Envoi d'un email à l'admin
            $email = (new Email())
                ->from($message->getEmail())
                ->to('sairhretane64@gmail.com') // Adresse de l'admin
                ->subject('Nouveau message de contact')
                ->text("Nom: " . $message->getName() . "\n\nMessage: " . $message->getContent());

            $mailer->send($email);

            $this->addFlash('success', 'Votre message a été envoyé avec succès.');
            return $this->redirectToRoute('app_contact');
        }

        return $this->render('contact/index.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
