<?php

namespace App\Controller;

use App\Entity\Reponse;
use App\Entity\ReclamationType;
use App\Form\ReponseType;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Reclamation;
use App\Form\ReclamationType as FormReclamationType;
use Symfony\Component\HttpFoundation\JsonResponse;

class ReponseController extends AbstractController
{


    #[Route('/reponse', name: 'app_reponse')]
    public function index(): Response
    {
        return $this->render('reponse/index.html.twig', [
            'controller_name' => 'ReponseController',
        ]);
    }

    #[Route('reponse/reponse/add', name: 'addReponse')]
    public function addReponse(Request $request, ManagerRegistry $doctrine): Response
    {
        $em = $doctrine->getManager();

        // Get the reclamation ID from the request
        $reclamationId = $request->request->get('reclamationId');
        $reclamation = $em->getRepository(Reclamation::class)->find($reclamationId);

        // Create a new Comment entity
        $reponse = new Reponse();
        $reponse->setContenuRep($request->request->get('contenuRep'));
        $reponse->setDateRep(new \DateTime());
        $reponse->setReclamation($reclamation);

        // Persist and flush the new reponse entity
        $em->persist($reponse);
        $em->flush();

        return $this->redirectToRoute('listReclamationBack');

    }

    
    #[Route('/reponse/listReponse', name: 'listReponse')]
    public function listReponse(ManagerRegistry $doctrine): Response
    {     
        $manager = $doctrine->getManager();

        $ReponseRepository=$doctrine->getRepository(Reponse::class);
        $reponses=$ReponseRepository->findAll();

        return $this->render('reponse/listReponse.html.twig', [
            'reponses' => $reponses,  // Corrected variable name
        ]);

        // Récupérer les réponses depuis la base de données
        /*$reponses = $this->getDoctrine()->getRepository(Reponse::class)->findAll();
        
        // Charger les commentaires pour chaque post
        foreach ($reponses as $reponse) 
        {
            $reponse->getReclamation();
        }
        // Créer un nouveau formulaire de reclamations
        $reclamation = new Reclamation();
        $form = $this->createForm(FormReclamationType::class, $reclamation);

        // Passer les réponses à la vue Twig
        return $this->render('reponse/listReponse.html.twig', [
            'reponses' => $reponses,  // Corrected variable name
            'form' => $form->createView(),
        ]);*/
    }

    #[Route('/reponse/deleteReponse/{id}', name: 'deleteReponse', methods:['DELETE'])]
    public function deleteReponse($id, Request $request, EntityManagerInterface $entityManager): Response
    { 
        // Récupérer l'objet Reponse par son identifiant
        $reponse = $entityManager->getRepository(Reponse::class)->find($id);
        
        // Vérifier si l'objet existe
        if (!$reponse) {
            throw $this->createNotFoundException('Aucune réponse trouvée pour l\'identifiant '.$id);
        }

        // Supprimer l'objet Reponse
        $entityManager->remove($reponse);
        $entityManager->flush();

        // Rediriger vers une autre page après la suppression
        return $this->redirectToRoute('listReclamationBack');
    }

    #[Route('/reponse/editReponse/{id}', name: 'editReponse', methods:['POST'])]
    public function editReponse(Request $request, $id, EntityManagerInterface $entityManager): Response{
        {
            $reponse = $entityManager->getRepository(Reponse::class)->find($id);
            $form = $this->createForm(ReponseType::class, $reponse);
            $form->handleRequest($request);


            if ($form->isSubmitted() && $form->isValid()) 
            {
                $em = $this->getDoctrine()->getManager();
                $em->flush();
                return $this->redirectToRoute('listReclamationBack');
        }

            return $this->render('reponse/editReponse.html.twig', [
                'form' => $form->createView(),
                'reponse' => $reponse,
            ]);
    }
    
    }

    public function getReponsesByReclamation($reclamationId)
    {
        $reponses = $this->getDoctrine()->getRepository(Reponse::class)->findBy(['reclamation' => $reclamationId]);

        $data = [];
        foreach ($reponses as $reponse) {
            $data[] = [
                'id' => $reponse->getId(),
                'content' => $reponse->getContent(),
                // Ajoutez d'autres champs de commentaire si nécessaire
            ];
        }

        return new JsonResponse($data);
    }
}
