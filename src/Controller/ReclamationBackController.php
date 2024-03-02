<?php

namespace App\Controller;
use App\Entity\Reclamation;
use App\Entity\ReclamationRepository;
use App\Entity\Reponse;
use App\Form\ReclamationType; // Make sure to import ReclamationType
use App\Form\ReponseType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;

class ReclamationBackController extends AbstractController
{
    #[Route('/reclamation/back', name: 'app_reclamation_back')]
    public function index(): Response
    {
        return $this->render('reclamation_back/index.html.twig', [
            'controller_name' => 'ReclamationBackController',
        ]);
    }

    #[Route('/reclamation/back/add', name: 'addreclamationback')]
    public function addReclamationBack(Request $request, ManagerRegistry $doctrine): Response
    {
        $em = $doctrine->getManager();
        $reclamation = new Reclamation();
        $form = $this->createForm(ReclamationType::class, $reclamation);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) 
        {
            $reclamation->setDateRec(new DateTime());
            $em = $this->getDoctrine()->getManager();

            // Ajouter les reponses
            $reponses = $form->get('reponses')->getData();
            foreach ($reponses as $reponse) 
            {
                $reponse->setDateComment(new DateTime());
                $reponse->setPost($reclamation);
                $em->persist($reponse);
            }

            $em->persist($reclamation);
            $em->flush();

            return $this->redirectToRoute('listReclamationBack');
        }

        return $this->render('reclamation_back/addReclamationBack.html.twig', [
            'form' => $form->createView(),
            'reclamation' => $reclamation,
        ]);
    }

    #[Route('/reclamation/back/list', name: 'listReclamationBack')]
    public function listReclamationBack(ManagerRegistry $doctrine): Response
    {
        $reclamations = $doctrine->getRepository(Reclamation::class)->findAll();
        
        // Charger les reponses pour chaque post
        foreach ($reclamations as $reclamation) 
        {
            $reclamation->getReponses();
        }

                // Créer un nouveau formulaire de reponse
                $reponse = new Reponse();
                $form = $this->createForm(ReponseType::class, $reponse);
        
        return $this->render('reclamation_back/listReclamationBack.html.twig', [
            'reclamations' => $reclamations,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/reclamation/back/deleteReclamation/{id}', name: 'listReclamationBack', methods:['DELETE'])]
    public function deleteReclamationBack($id, Request $request, Reclamation $reclamation, EntityManagerInterface $entityManager): Response
    { 
        
        $reclamation = $entityManager->getRepository(Reclamation::class)->find($id);
        $entityManager->remove($reclamation);
        $entityManager->flush();

        return $this->redirectToRoute('listReclamationBack');
    }
}
