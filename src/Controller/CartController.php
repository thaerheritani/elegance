<?php

namespace App\Controller;

use App\Entity\Address;
use App\Form\AddressType;
use App\Repository\ProductRepository;
use App\Repository\SizeRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Routing\Annotation\Route;
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

    #[Route('/commande', name: 'app_place_order', methods: ['POST'])]
    public function placeOrder(Request $request, EntityManagerInterface $entityManager, MailerInterface $mailer): Response
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

        $session->set('address', $address);

        if ($addressForm->isSubmitted() && $addressForm->isValid()) {
            $address->setCustomer($this->getUser());

            $entityManager->persist($address);
            $entityManager->flush();

            // Redirection vers la page de paiement Stripe
            return $this->redirectToRoute('checkout');
        }

        return $this->redirectToRoute('app_cart');
    }

    #[Route('/panier/ajouter/{id}', name: 'app_cart_add', methods: ['POST'])]
    public function addToCart(Request $request, ProductRepository $productRepository, SizeRepository $sizeRepository, $id): JsonResponse
    {
        $product = $productRepository->find($id);

        // Get the size from the request body
        $content = json_decode($request->getContent(), true);
        $selectedSizeId = $content['size'] ?? null;

        if (!$product) {
            return $this->json(['error' => 'Produit non trouvé'], 404);
        }

        if (!$selectedSizeId) {
            return $this->json(['error' => 'Taille non sélectionnée'], 400);
        }

        // Retrieve the selected size
        $size = $sizeRepository->find($selectedSizeId);
        if (!$size) {
            return $this->json(['error' => 'Taille non trouvée'], 404);
        }

        $session = $request->getSession();
        $cart = $session->get('cart', []);

        $productExists = false;
        foreach ($cart as &$item) {
            if ($item['id'] == $product->getId() && $item['size']['id'] == $size->getId()) {
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
                'size' => ['id' => $size->getId(), 'name' => $size->getName()], // Add selected size
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
