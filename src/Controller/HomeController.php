<?php

namespace App\Controller;

use App\Repository\CategoryRepository;
use App\Repository\ProductRepository;
use App\Repository\SizeRepository;
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
        SizeRepository $sizeRepository, // Ajout du SizeRepository
        SliderRepository $sliderRepository,
        Request $request,
        PaginatorInterface $paginator
    ): Response {
        $productsWithDiscount = $productRepository->findByDiscount(); // Produits avec réduction
        $productsWithoutDiscountQuery = $productRepository->findWithoutDiscountQuery(); // Query pour les produits sans réduction

        // Utiliser le Paginator pour paginer les produits sans réduction
        $productsWithoutDiscount = $paginator->paginate(
            $productsWithoutDiscountQuery,
            $request->query->getInt('page', 1),
            20
        );

        // Récupérer les catégories et les tailles pour la recherche
        $productCategories = $categoryRepository->findBy(['type' => 'product_type']);
        $targetCategories = $categoryRepository->findBy(['type' => 'target']);
        $sizes = $sizeRepository->findAll();

        return $this->render('product/index.html.twig', [
            'productsWithDiscount' => $productsWithDiscount,
            'productsWithoutDiscount' => $productsWithoutDiscount,
            'productCategories' => $productCategories, // Catégories de produits
            'targetCategories' => $targetCategories, // Cibles (hommes, femmes, enfants)
            'sizes' => $sizes, // Tailles disponibles
        ]);
    }
}
