<?php

namespace App\Controller;

use App\Repository\OrderRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/delivery')]
class DeliveryController extends AbstractController
{
    #[Route('/orders', name: 'delivery_manage_orders')]
    public function manageOrders(OrderRepository $orderRepository): Response
    {
        $orders = $orderRepository->findBy(['status' => 'En cours']);

        return $this->render('delivery/manage_orders.html.twig', [
            'orders' => $orders,
        ]);
    }

    #[Route('/orders/{id}/update-status', name: 'delivery_order_status', methods: ['POST'])]
    public function updateOrderStatus($id, Request $request, EntityManagerInterface $entityManager, OrderRepository $orderRepository): Response
    {
        $order = $orderRepository->find($id);

        if (!$order) {
            throw $this->createNotFoundException('Commande non trouvée');
        }

        $newStatus = $request->request->get('status');

        $order->setStatus($newStatus);
        $entityManager->flush();

        return $this->redirectToRoute('delivery_manage_orders');
    }
}

