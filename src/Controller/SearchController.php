<?php

namespace App\Controller;

use App\Form\ProductSearchType;
use App\Repository\ProductRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class SearchController extends AbstractController
{
    #[Route('/search', name: 'app_search', methods: ['GET'])]
    public function search(Request $request, ProductRepository $productRepository): Response
    {
        // Créer le formulaire de recherche
        $form = $this->createForm(ProductSearchType::class, null, ['method' => 'GET']);
        $form->handleRequest($request);

        // Initialiser un tableau vide pour les résultats de produits
        $products = null;

        // Si le formulaire est soumis et valide, on fait la requête
        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            dump($data);

            // Initialiser la requête pour les produits
            $query = $productRepository->createQueryBuilder('p');

            // Filtrer par type de produit
            if ($data['productType']) {
                $query->andWhere('p.category = :productType')
                    ->setParameter('productType', $data['productType']);
            }

            // Filtrer par cible
            if ($data['target']) {
                $query->andWhere('p.targetAudience = :target')
                    ->setParameter('target', $data['target']);
            }

            // Filtrer par taille
            if ($data['size']) {
                $query->join('p.size', 's')
                    ->andWhere('s.id = :size')
                    ->setParameter('size', $data['size']);
            }

            // Récupérer les produits filtrés
            $products = $query->getQuery()->getResult();
        }
        dump($products);

        // Afficher la vue avec le formulaire et les résultats (si présents)
        return $this->render('search/index.html.twig', [
            'searchForm' => $form->createView(),
            'products' => $products,
        ]);
    }
}
