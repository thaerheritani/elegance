<?php

namespace App\Controller;

use App\Entity\Order;
use App\Entity\Product;
use App\Entity\Review;
use App\Entity\User;
use App\Form\OrderStatusType;
use App\Form\ProductType;
use App\Form\ReviewType;
use App\Form\UserType;
use App\Repository\CategoryRepository;
use App\Repository\OrderRepository;
use App\Repository\ProductRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/admin')]
class AdminDashboardController extends AbstractController
{
    #[Route('/dashboard', name: 'app_admin_dashboard')]
    public function index(UserRepository $userRepository, ProductRepository $productRepository, CategoryRepository $categoryRepository): Response
    {
        // Récupération des statistiques à afficher dans le tableau de bord
        $userCount = $userRepository->count([]);
        $productCount = $productRepository->count([]);
        $categoryCount = $categoryRepository->count([]);


        return $this->render('admin_dashboard/index.html.twig', [
            'userCount' => $userCount,
            'productCount' => $productCount,
            'categoryCount' => $categoryCount,

        ]);
    }

    #[Route('/products', name: 'admin_manage_products')]
    public function manageProducts(ProductRepository $productRepository): Response
    {
        $products = $productRepository->findAll();

        return $this->render('admin_dashboard/manage_products.html.twig', [
            'products' => $products,
        ]);
    }

    #[Route('/users', name: 'admin_manage_users')]
    public function manageUsers(UserRepository $userRepository): Response
    {
        $users = $userRepository->findAll();

        return $this->render('admin_dashboard/manage_users.html.twig', [
            'users' => $users,
        ]);
    }



    #[Route('/orders', name: 'admin_manage_orders')]
    public function manageOrders(OrderRepository $orderRepository): Response
    {
        $orders = $orderRepository->findAll();

        return $this->render('admin_dashboard/order/index.html.twig', [
            'orders' => $orders,
        ]);
    }

    #[Route('/orders/{id}/status', name: 'admin_order_status', methods: ['GET', 'POST'])]
    public function updateOrderStatus(Order $order, Request $request, EntityManagerInterface $entityManager): Response
    {
        // Créer et gérer le formulaire pour changer le statut
        $form = $this->createForm(OrderStatusType::class, $order);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush(); // Mettre à jour la base de données

            $this->addFlash('success', 'Statut de la commande mis à jour');
            return $this->redirectToRoute('admin_manage_orders');
        }

        return $this->render('admin_dashboard/order/order_status.html.twig', [
            'order' => $order,
            'form' => $form->createView(),
        ]);
    }


    #[Route('/products', name: 'admin_product_index', methods: ['GET'])]
    public function read(ProductRepository $productRepository): Response
    {
        return $this->render('admin_dashboard/product/new.html.twig', [
            'products' => $productRepository->findAll(),
        ]);
    }

    #[Route('/products/new', name: 'admin_product_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $product = new Product();
        $productForm = $this->createForm(ProductType::class, $product);
        $productForm->handleRequest($request);

        if ($productForm->isSubmitted() && $productForm->isValid()) {
            $entityManager->persist($product);
            $entityManager->flush();

            // Redirection vers la liste des produits d'administration
            return $this->redirectToRoute('admin_product_index');
        }

        return $this->render('admin_dashboard/product/new.html.twig', [
            'product' => $product,
            'productForm' => $productForm,
        ]);
    }

    #[Route('/products/{id}', name: 'admin_product_show', methods: ['GET'])]
    public function show(Product $product, Request $request, EntityManagerInterface $entityManager): Response
    {
        $review = new Review();
        $reviewForm = $this->createForm(ReviewType::class, $review);
        $reviewForm->handleRequest($request);

        if ($reviewForm->isSubmitted() && $reviewForm->isValid()) {
            $review->setProduct($product);
            $review->setCustomer($this->getUser());
            $entityManager->persist($review);
            $entityManager->flush();

            return $this->redirectToRoute('admin_product_show', ['id' => $product->getId()]);
        }

        return $this->render('admin_dashboard/product/show.html.twig', [
            'product' => $product,
            'reviewForm' => $reviewForm->createView(),
            'reviews' => $product->getReviews(),
        ]);
    }

    #[Route('/products/{id}/edit', name: 'admin_product_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Product $product, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(ProductType::class, $product);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('admin_product_index');
        }

        return $this->render('admin_dashboard/product/edit.html.twig', [
            'product' => $product,
            'form' => $form,
        ]);
    }

    #[Route('/products/{id}', name: 'admin_product_delete', methods: ['POST'])]
    public function delete(Request $request, Product $product, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$product->getId(), $request->get('_token'))) {
            $entityManager->remove($product);
            $entityManager->flush();
        }

        return $this->redirectToRoute('admin_product_index');
    }
}
