<?php

namespace App\Controller;

use App\Entity\Products;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;





#[Route('/produits', name: 'products_')]
class ProductsController extends AbstractController
{
    #[Route('/', name: 'index')]
    public function index(): Response
    {
        return $this->render(('products/index.html.twig'));
    }
    #[Route('/{slug}', name: 'details')]
    public function details(string $slug, EntityManagerInterface $entityManager): Response
    {
        $repository = $entityManager->getRepository(Products::class);
        $product = $repository->findOneBy(['slug' => $slug]);

        if ($product === null) {
            throw $this->createNotFoundException('Product not found');
        }

        // Vous pouvez maintenant utiliser $product pour afficher les détails dans votre vue

        return $this->render('products/details.html.twig', [
            'product' => $product,
        ]);
    }


}