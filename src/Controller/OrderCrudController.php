<?php

namespace App\Controller;


use App\Repository\OrderRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/admin/orders')]
class OrderCrudController extends AbstractController
{
    #[Route('/', name: 'admin_manage_orders', methods: ['GET'])]
    public function index(OrderRepository $orderRepository): Response
    {
        return $this->render('admin/order/index.html.twig', [
            'orders' => $orderRepository->findAll(),
        ]);
    }
}
