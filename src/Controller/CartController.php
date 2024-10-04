<?php

// src/Controller/CartController.php

namespace App\Controller;

use App\Entity\Address;
use App\Form\AddressType;
use App\Repository\ProductRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Doctrine\ORM\EntityManagerInterface;

class CartController extends AbstractController
{
    #[Route('/panier', name: 'app_cart')]
    public function index(Request $request, EntityManagerInterface $entityManager): Response
    {
        $session = $request->getSession();
        $cart = $session->get('cart', []);

        // Calcul des totaux
        $totalProducts = array_sum(array_map(function ($item) {
            return $item['price'] * $item['quantity'];
        }, $cart));
        $shippingCost = 4.00;
        $totalPrice = $totalProducts + $shippingCost;

        // Création du formulaire d'adresse de livraison
        $address = new Address();
        $addressForm = $this->createForm(AddressType::class, $address);

        return $this->render('cart/index.html.twig', [
            'cartItems' => $cart,
            'totalProducts' => $totalProducts,
            'shippingCost' => $shippingCost,
            'totalPrice' => $totalPrice,
            'addressForm' => $addressForm->createView(),
        ]);
    }

    #[Route('/checkout', name: 'app_checkout', methods: ['POST'])]
    public function checkout(Request $request, EntityManagerInterface $entityManager, MailerInterface $mailer): Response
    {
        $session = $request->getSession();
        $cart = $session->get('cart', []);

        if (empty($cart)) {
            $this->addFlash('danger', 'Votre panier est vide.');
            return $this->redirectToRoute('app_cart');
        }

        // Gérer l'adresse de livraison soumise dans le formulaire
        $address = new Address();
        $addressForm = $this->createForm(AddressType::class, $address);
        $addressForm->handleRequest($request);

        if ($addressForm->isSubmitted() && $addressForm->isValid()) {
            $entityManager->persist($address);
            $entityManager->flush();

            // Processus de commande
            $totalPrice = array_sum(array_map(function ($item) {
                    return $item['price'] * $item['quantity'];
                }, $cart)) + 4.00;  // Ajouter les frais de livraison

            // Envoi de l'email de confirmation
            $email = (new Email())
                ->from('no-reply@votresite.com')
                ->to($this->getUser()->getEmail())
                ->subject('Confirmation de votre commande')
                ->text("Votre commande a été reçue et sera envoyée à l'adresse suivante :\n\n" .
                    $address->getStreet() . "\n" .
                    $address->getCity() . ", " . $address->getPostalCode() . "\n" .
                    $address->getCountry() . "\n\nTotal : " . $totalPrice . "€");

            $mailer->send($email);

            $this->addFlash('success', 'Votre commande a été validée. Un email de confirmation vous a été envoyé.');
            return $this->redirectToRoute('app_product_index');
        }

        return $this->redirectToRoute('app_cart');
    }

    #[Route('/panier/ajouter/{id}', name: 'app_cart_add', methods: ['POST'])]
    public function addToCart(Request $request, ProductRepository $productRepository, $id): JsonResponse
    {
        $product = $productRepository->find($id);

        if (!$product) {
            return $this->json(['error' => 'Produit non trouvé'], 404);
        }

        $session = $request->getSession();
        $cart = $session->get('cart', []);

        $productExists = false;
        foreach ($cart as &$item) {
            if ($item['id'] == $product->getId()) {
                $item['quantity'] += 1;
                $productExists = true;
                break;
            }
        }

        if (!$productExists) {
            $cart[] = [
                'id' => $product->getId(),
                'name' => $product->getName(),
                'price' => $product->getPrice(),
                'quantity' => 1,
                'image' => $product->getPhotos()->first() ? $product->getPhotos()->first()->getPhotoPath() : 'default.png',
            ];
        }

        $session->set('cart', $cart);

        return $this->json(['cartItemCount' => count($cart)]);
    }


    #[Route('/cart/count', name: 'app_cart_count', methods: ['GET'])]
    public function getCartCount(Request $request): JsonResponse
    {
        $session = $request->getSession();
        $cart = $session->get('cart', []);

        return $this->json(['count' => count($cart)]);
    }

    #[Route('/panier/supprimer/{id}', name: 'app_cart_remove', methods: ['POST'])]
    public function removeFromCart($id, Request $request): JsonResponse
    {
        $session = $request->getSession();
        $cart = $session->get('cart', []);

        // Supprimer l'article du panier
        $cart = array_filter($cart, function ($item) use ($id) {
            return $item['id'] != $id;
        });

        $session->set('cart', $cart);

        return new JsonResponse(['cartItemCount' => count($cart)]);
    }

    #[Route('/panier/update/{id}', name: 'app_cart_update', methods: ['POST'])]
    public function updateCartItem($id, Request $request): JsonResponse
    {
        $session = $request->getSession();
        $cart = $session->get('cart', []);
        $data = json_decode($request->getContent(), true);

        // Mettre à jour la quantité de l'article
        foreach ($cart as &$item) {
            if ($item['id'] == $id) {
                $item['quantity'] = $data['quantity'];
                break;
            }
        }

        $session->set('cart', $cart);

        return new JsonResponse(['success' => true]);
    }

}

