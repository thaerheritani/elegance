<?php

namespace App\Controller;

use Stripe\Stripe;
use Stripe\PaymentIntent;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class PaymentController extends AbstractController
{
    #[Route('/checkout', name: 'checkout', methods: ['GET', 'POST'])]
    public function checkout(Request $request): Response
    {
        // Vérifier si l'utilisateur est connecté
        if (!$this->getUser()) {
            $this->addFlash('warning', 'Vous devez être connecté pour passer une commande.');
            return $this->redirectToRoute('app_login');
        }

        // Récupérer le panier de la session
        $cart = $request->getSession()->get('cart', []);
        $total = 0;
        $shippingCost = 4.00; // Coût fixe de livraison

        // Calculer le total pour les articles dans le panier
        foreach ($cart as $item) {
            $total += $item['price'] * $item['quantity'];
        }

        // Ajouter les frais de livraison
        $total += $shippingCost;

        // Convertir le total en centimes pour Stripe
        $totalInCents = $total * 100;

        // Initialiser Stripe avec la clé secrète
        Stripe::setApiKey($this->getParameter('stripe_secret_key'));

        // Créer un PaymentIntent pour Stripe
        $paymentIntent = PaymentIntent::create([
            'amount' => $totalInCents,
            'currency' => 'eur',
            'metadata' => ['integration_check' => 'accept_a_payment'],
        ]);

        // Rendre la page de checkout avec le total correct et le client_secret du PaymentIntent
        return $this->render('payment/checkout.html.twig', [
            'stripe_public_key' => $this->getParameter('stripe_public_key'),
            'client_secret' => $paymentIntent->client_secret,
            'total' => $total, // Envoyer le total correct à la vue
            'shippingCost' => $shippingCost, // Envoyer aussi les frais de livraison si nécessaire
        ]);
    }

    #[Route('/success', name: 'success_url')]
    public function success(): Response
    {
        return $this->render('payment/success.html.twig', [
            'message' => 'Paiement réussi !',
        ]);
    }

    #[Route('/cancel', name: 'cancel_url')]
    public function cancel(): Response
    {
        return $this->render('payment/cancel.html.twig', [
            'message' => 'Le paiement a été annulé.',
        ]);
    }
}
