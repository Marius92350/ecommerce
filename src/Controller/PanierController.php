<?php

namespace App\Controller;

use App\Entity\ContenuPanier;
use App\Entity\Panier;
use App\Repository\ProduitRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class PanierController extends AbstractController
{
    /**
     * @Route("/panier", name="panier")
     */
    public function index(Request $request, ProduitRepository $produitRepository): Response
    {
        // On récupère session user
        $session = $request->getSession();
        $panier = $session->get('panier');
        $panierWithData = [];
        // Incrémentation panier
        if(!empty($panier)){
            foreach($panier as $id => $quantity){
                $panierWithData[] = ["product" => $produitRepository->find($id), 'quantity' => $quantity];
            }
        }
        $this->addInDatabase();
        
        return $this->render('panier/index.html.twig', [
            'items' => $panierWithData,
            'controller_name' => 'PanierController',
        ]);
    }

    public function addInDatabase(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $panier = new Panier();
        $em->persist($panier);
        $em->flush();
    }

    /**
     * @Route("/panier/add/{id}", name="panier_add", priority=1)
     */
    public function add($id, Request $request, ProduitRepository $produitRepository)
    {
        $produit = $produitRepository->find($id);
        // On récupère la session de l'utilisateur
        $session = $request->getSession();

        // On récupère ou créer le panier
        $panier = $session->get('panier', []);

        // Si on ajoute plusieurs fois un produit
        if(!empty($panier)){
            $panier[$id] ++;
        }else{
            // Ajout du produit au panier
            $panier[$id] = 1;
        }
        $session->set('panier',$panier);
        return $this->redirectToRoute('panier');
    }

    /**
     * @Route("/panier/validate",name="panier_validate")
     */
    public function validate(Request $request, ProduitRepository $produitRepository)
    {
        $session = $request->getSession();

        $panier = new Panier();
        // Puis insertion en BDD (persist, flush .. mais pas le temps !)
    }

    /**
     * @Route("/panier/delete/{id}", name="panier_remove")
     */
    public function delete($id, Request $request, ProduitRepository $produitRepository){
        $produit = $produitRepository->find($id);
        // On récupère la session de l'utilisateur
        $session = $request->getSession();
        // // On récupère ou créer le panier
        $panier = $session->get('panier', []);

        if(!empty($panier[$id])){
            unset($panier[$id]);
        }
        $session->set('panier',$panier);
        return $this->redirectToRoute('panier');
    }
}
