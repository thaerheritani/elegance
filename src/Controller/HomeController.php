<?php

namespace App\Controller;

use App\Repository\CategoryRepository;
use App\Repository\ProductRepository;
use App\Repository\SliderRepository;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class HomeController extends AbstractController
{
    #[Route('/', name: 'home', methods: ['GET'])]
    public function index(
        ProductRepository $productRepository,
        CategoryRepository $categoryRepository,
        SliderRepository $sliderRepository,
        Request $request,
        PaginatorInterface $paginator
    ): Response {
        $productsWithDiscount = $productRepository->findByDiscount(); // Produits avec réduction
        $productsWithoutDiscountQuery = $productRepository->findWithoutDiscountQuery(); // Query pour les produits sans réduction

        // Utiliser le Paginator pour paginer les produits sans réduction
        $productsWithoutDiscount = $paginator->paginate(
            $productsWithoutDiscountQuery, // Requête ou tableau des produits sans réduction
            $request->query->getInt('page', 1), // Numéro de la page actuelle, 1 par défaut
            20 // Limiter à 20 produits par page
        );

        return $this->render('product/index.html.twig', [
            'productsWithDiscount' => $productsWithDiscount,
            'productsWithoutDiscount' => $productsWithoutDiscount,
        ]);
    }
}
