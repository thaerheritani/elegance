<?php

namespace App\Controller;

use App\Entity\Order;
use App\Entity\OrderItem;
use App\Entity\Address;
use App\Form\AddressType;
use App\Repository\ProductRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

class OrderController extends AbstractController
{
    #[Route('/checkout', name: 'app_checkout')]
    public function checkout(Request $request, ProductRepository $productRepository, EntityManagerInterface $entityManager, MailerInterface $mailer): Response
    {
        $session = $request->getSession();
        $cart = $session->get('cart', []);

        // Gestion du formulaire d'adresse
        $address = new Address();
        $addressForm = $this->createForm(AddressType::class, $address);
        $addressForm->handleRequest($request);

        if ($addressForm->isSubmitted() && $addressForm->isValid()) {
            // Créer une nouvelle commande
            $order = new Order();
            $order->setOrderDate(new \DateTime());
            $order->setStatus('En cours');
            $order->setCustomer($this->getUser());
            $total = 0;

            foreach ($cart as $item) {
                $product = $productRepository->find($item['id']);
                if ($product) {
                    $orderItem = new OrderItem();
                    $orderItem->setProduct($product);
                    $orderItem->setQuantity($item['quantity']);
                    $orderItem->setPrice($product->getPrice());

                    $order->addOrderItem($orderItem);
                    $total += $product->getPrice() * $item['quantity'];
                }
            }

            // Ajouter l'adresse à la commande
            $order->setTotal($total);
            $entityManager->persist($order);
            $address->setCustomer($this->getUser());
            $entityManager->persist($address);
            $entityManager->flush();

            // Envoyer un email de confirmation
            $email = (new Email())
                ->from('no-reply@boutique.com')
                ->to($this->getUser()->getEmail())
                ->subject('Confirmation de commande')
                ->text("Merci pour votre commande. Total : " . $total . " €. Livraison à : " . $address->getStreet());

            $mailer->send($email);

            // Vider le panier
            $session->remove('cart');

            return $this->redirectToRoute('success_url');
        }

        return $this->render('order/checkout.html.twig', [
            'cartItems' => $cart,
            'totalPrice' => array_sum(array_column($cart, 'price')),
            'addressForm' => $addressForm->createView(),
        ]);
    }
}


