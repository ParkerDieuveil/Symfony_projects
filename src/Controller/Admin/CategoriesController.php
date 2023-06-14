<?php

namespace App\Controller;

use App\Entity\Categories;
use App\Repository\ProductsRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;





#[Route('/categories', name: 'categories_')]
class CategoriesController extends AbstractController
{

    #[Route('/{slug}', name: 'list')]
    public function list(string $slug, EntityManagerInterface $entityManager, ProductsRepository $productsRepository, Request $request): Response
    {
        //on va chercher le numéro de page dans l'url
        $page = $request->query->getInt('page', 1);

        $repository = $entityManager->getRepository(Categories::class);
        $category = $repository->findOneBy(['slug' => $slug]);
        $products = $productsRepository->findProductsPaginated($page, $category->getSlug(), 2);


        if ($category === null) {
            throw $this->createNotFoundException('La Categorie est  introuvable');
        }

        // Vous pouvez maintenant utiliser $product pour afficher les détails dans votre vue

        return $this->render('categories/list.html.twig', [
            'category' => $category,
            'products' => $products
        ]);
    }
}
