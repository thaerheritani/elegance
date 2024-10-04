<?php

namespace App\Repository;

use App\Entity\Product;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Product>
 *
 * @method Product|null find($id, $lockMode = null, $lockVersion = null)
 * @method Product|null findOneBy(array $criteria, array $orderBy = null)
 * @method Product[]    findAll()
 * @method Product[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ProductRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Product::class);
    }

//    /**
//     * @return Product[] Returns an array of Product objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('p')
//            ->andWhere('p.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('p.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Product
//    {
//        return $this->createQueryBuilder('p')
//            ->andWhere('p.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }

    public function findByFilters(array $filters)
    {
        $qb = $this->createQueryBuilder('p');

        // Filtrer par catégorie
        if (!empty($filters['category'])) {
            $qb->andWhere('p.category = :category')
                ->setParameter('category', $filters['category']);
        }

        // Filtrer par taille
        if (!empty($filters['size'])) {
            $qb->andWhere('p.size = :size')
                ->setParameter('size', $filters['size']);
        }

        // Filtrer par couleur
        if (!empty($filters['color'])) {
            $qb->andWhere('p.color = :color')
                ->setParameter('color', $filters['color']);
        }

        // Filtrer par plage de prix
        if (!empty($filters['priceRange'])) {
            [$minPrice, $maxPrice] = explode('-', $filters['priceRange']);
            $qb->andWhere('p.price >= :minPrice')
                ->setParameter('minPrice', $minPrice);
            if ($maxPrice) {
                $qb->andWhere('p.price <= :maxPrice')
                    ->setParameter('maxPrice', $maxPrice);
            }
        }

        return $qb->getQuery()->getResult();
    }
    public function findByDiscount(): array
    {
        return $this->createQueryBuilder('p')
            ->where('p.discount IS NOT NULL')
            ->andWhere('p.discount > 0')
            ->getQuery()
            ->getResult();
    }

    public function findWithoutDiscountQuery()
    {
        return $this->createQueryBuilder('p')
            ->where('p.discount IS NULL')
            ->getQuery();
    }


}


