<?php

namespace App\Controller;

use App\Entity\Order;
use App\Entity\OrderItem;
use App\Entity\Address;
use App\Form\AddressType;
use App\Repository\OrderRepository;
use App\Repository\ProductRepository;
use Doctrine\ORM\EntityManagerInterface;
use Stripe\PaymentIntent;
use Stripe\Stripe;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Security\Core\User\UserInterface;

class OrderController extends AbstractController
{
    #[Route('/checkout', name: 'app_checkout')]
    public function checkout(Request $request, ProductRepository $productRepository, EntityManagerInterface $entityManager, MailerInterface $mailer): Response
    {
        $session = $request->getSession();
        $cart = $session->get('cart', []);

        // Initialiser Stripe avec la clé secrète
        Stripe::setApiKey($this->getParameter('stripe_secret_key'));

        // Calculer le montant total de la commande (en centimes)
        $total = 0;

        foreach ($cart as $item) {
            $total += $item['price'] * $item['quantity'];
        }

        $totalInCents = $total * 100; // Montant en centimes pour Stripe

        // Créer un PaymentIntent (Stripe) pour gérer le paiement
        $paymentIntent = PaymentIntent::create([
            'amount' => $totalInCents,
            'currency' => 'eur',
            'metadata' => ['integration_check' => 'accept_a_payment'],
        ]);

        // Rendre la page de checkout avec le client_secret du PaymentIntent
        return $this->render('payment/checkout.html.twig', [
            'cartItems' => $cart,
            'totalPrice' => array_sum(array_column($cart, 'price')),
            'total' => array_sum(array_column($cart, 'price')),
            'stripe_public_key' => $this->getParameter('stripe_public_key'),
            'client_secret' => $paymentIntent->client_secret,
        ]);
    }

    #[Route('/my-orders', name: 'app_my_orders')]
    public function myOrders(OrderRepository $orderRepository, UserInterface $user)
    {
        $orders = $orderRepository->findBy(['customer' => $user]);

        return $this->render('order/index.html.twig', [
            'orders' => $orders,
        ]);
    }
}


