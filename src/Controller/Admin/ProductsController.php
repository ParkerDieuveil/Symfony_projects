<?php

namespace App\Controller\Admin;


use App\Entity\Images;
use App\Entity\Products;
use App\Form\ProductsFormType;
use App\Service\PictureService;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\SluggerInterface;

#[Route('/admin/produits', name: 'admin_products_')]

class ProductsController extends AbstractController
{
    #[Route('/', name: 'index')]
    public function index(): Response
    {
        return $this->render('admin/products/index.html.twig');
    }


    #[Route('/ajout', name: 'add')]
    public function add(Request $request, EntityManagerInterface $em, SluggerInterface $slugger, PictureService $pictureService): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        // on crée un nouveau produit

        $product = new Products();

        // on crée le formulaire

        $productForm = $this->createForm(ProductsFormType::class, $product);

        // On traite le formulaire
        $productForm = $this->createForm(ProductsFormType::class, $product);

        $productForm->handleRequest($request);

        // on vérifie si le formulaire est soumis et valide

        if ($productForm->isSubmitted() && $productForm->isValid()) {

            // on récupère les images
            $images = $productForm->get('images')->getData();

            foreach ($images as $image) {
                // on définit le dossier de destination
                $folder = 'products';

                // on appelle le service d'ajout
                $fichier = $pictureService->add($image, $folder, 300, 300);


                $img = new Images();
                $img->setName($fichier);
                $product->addImage($img);
            }

            //on génère le Slug
            $slug = $slugger->slug($product->getName());


            $product->setSlug($slug);

            // on arrondi le prix

            $prix = $product->getPrix() * 100;
            $product->setPrix($prix);
            //on stocke
            $em->persist($product);
            $em->flush();

            $this->addFlash('success', 'Produit ajouté avec success');
            //on redirectionne
            return  $this->redirectToRoute('admin_products_index');
        }





        //return $this->render('admin/products/add.html.twig', ['productForm' => $productForm->createView()]);

        return $this->renderForm('admin/products/add.html.twig', compact('productForm'));
    }

    #[Route('/edition/{id}', name: 'edit')]
    public function edit(int $id, Products $product, Request $request, EntityManagerInterface $em, SluggerInterface $slugger, PictureService $pictureService): Response
    {
        //on vérifie si l'utilisateur peut éditer à partir du voter 
        $this->denyAccessUnlessGranted('PRODUCT_EDIT', $product);

        // Charger le produit à partir de la base de données

        $product = $em->getRepository(Products::class)->find($id);

        if (!$product) {
            throw $this->createNotFoundException('Produit non trouvé');
        }
        //on divise le prix par 100
        $prix = $product->getPrix() / 100;
        $product->setPrix($prix);

        // On traite le formulaire
        $productForm = $this->createForm(ProductsFormType::class, $product);

        $productForm->handleRequest($request);

        // on vérifie si le formulaire est soumis et valide

        if ($productForm->isSubmitted() && $productForm->isValid()) {


            // on récupère les images
            $images = $productForm->get('images')->getData();

            foreach ($images as $image) {
                // on définit le dossier de destination
                $folder = 'products';

                // on appelle le service d'ajout
                $fichier = $pictureService->add($image, $folder, 300, 300);


                $img = new Images();
                $img->setName($fichier);
                $product->addImage($img);
            }
            //on génère le Slug
            $slug = $slugger->slug($product->getName());
            $product->setSlug($slug);

            // on arrondi le prix



            //on stocke
            $em->persist($product);
            $em->flush();

            $this->addFlash('success', 'Produit modifié avec success');
            //on redirectionne
            return  $this->redirectToRoute('admin_products_index');
        }

        return $this->render('admin/products/edit.html.twig', [
            'productForm' => $productForm->createView(),
            'product' => $product
        ]);
    }

    #[Route('/suppression/{id}', name: 'delete')]
    public function delete(Products $product): Response
    {
        //on vérifie si l'utilisateur peut supprimer à partir du voter
        $this->denyAccessUnlessGranted('PRODUCT_DELETE', $product);


        return $this->render('admin/products/index.html.twig');
    }
}
