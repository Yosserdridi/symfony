<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Reclamation;
use App\Entity\Reponse;
use App\Form\ReclamationType; // Make sure to import ReclamationType
use App\Form\ReponseType;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Form\FormTypeInterface; // Add missing semicolon and import statement
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class ReclamationController extends AbstractController
{
    #[Route('/reclamation', name: 'app_reclamation')]
    public function index(): Response
    {
        return $this->render('reclamation/index.html.twig', [
            'controller_name' => 'ReclamationController',
        ]);
    }

    #[Route('/reclamation/add', name: 'addreclamation')]
    public function addReclamation(ManagerRegistry $doctrine, Request $req): Response
    {
        $em = $doctrine->getManager();
        $reclamation = new Reclamation;
        $form = $this->createForm(ReclamationType::class, $reclamation);
        $form->handleRequest($req);

        if ($form->isSubmitted() && $form->isValid()) 
        {
            $reclamation->setDateRec(new DateTime());
            $em = $this->getDoctrine()->getManager();

            $em->persist($reclamation);
            $em->flush();
            return $this->redirectToRoute('listReclamation');
        }

        return $this->render('reclamation/addReclamation.html.twig', [
            'reclamation' => $reclamation,
            'form' => $form->createView(),
        ]);
    }


    #[Route('/reclamation/listReclamation', name: 'listReclamation')]
    public function listReclamation(ManagerRegistry $doctrine): Response
    {     
        // Récupérer les reclamations depuis la base de données
        $reclamations = $this->getDoctrine()->getRepository(Reclamation::class)->findAll();
        
        // Charger les reponses pour chaque reclamation
        foreach ($reclamations as $reclamation) 
        {
            $reclamation->getReponses();
        }

        // Créer un nouveau formulaire de reponse
        $reponse = new Reponse();
        $form = $this->createForm(ReponseType::class, $reponse);

        // Passer les reclamations à la vue Twig
        return $this->render('reclamation/listReclamation.html.twig', [
            'reclamations' => $reclamations,
            'form' => $form->createView(),
        ]);
    }
    
   

    #[Route('/reclamation/deleteReclamation/{id}', name: 'deleteReclamation', methods:['DELETE'])]
            public function deleteReclamation($id, Request $request, Reclamation $reclamation, EntityManagerInterface $entityManager): Response
            {
                $reclamation = $entityManager->getRepository(Reclamation::class)->find($id);
                $entityManager->remove($reclamation);
                $entityManager->flush();

                return $this->redirectToRoute('listReclamation');
            }

        #[Route('/reclamation/editReclamation/{id}', name: 'editReclamation', methods:['POST'])]
        public function editReclamation(Request $request, $id, EntityManagerInterface $entityManager): Response{
            {
                $reclamation = $entityManager->getRepository(Reclamation::class)->find($id);
                $form = $this->createForm(ReclamationType::class, $reclamation);
                $form->handleRequest($request);


                if ($form->isSubmitted() && $form->isValid()) 
                {
                    $em = $this->getDoctrine()->getManager();
                    $em->flush();
                    return $this->redirectToRoute('listReclamation');
            }

                return $this->render('reclamation/editReclamation.html.twig', [
                    'form' => $form->createView(),
                    'reclamation' => $reclamation,
                ]);
        }
        
        }
}