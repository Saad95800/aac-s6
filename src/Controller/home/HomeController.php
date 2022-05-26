<?php

namespace App\Controller\home;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use App\Form\Type\ServiceType;
use App\Entity\Service;
use App\Entity\Image;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @Route("/")
 */

class HomeController extends AbstractController{


        private $entityManager;

        public function __construct(ManagerRegistry $doctrine){
            $this->entityManager = $doctrine->getManager();
        }

        #[Route('/', name: 'index')]
        public function index(){

                $serviceRepository = $this->entityManager->getRepository('App\Entity\Service');
                $services = $serviceRepository->findAll();

                $publicationRepository = $this->entityManager->getRepository('App\Entity\Publication');
                $publications = $publicationRepository->findBy(
                        ['type' => 'publication']
                );

                $events = $publicationRepository->findBy(
                        ['type' => 'event']
                );
                
                return $this->render('home/index.html.twig', [
                        'services' => $services,
                        'publications' => $publications,
                        'events' => $events
                ]);

        }


        #[Route('/prestation/{id}', name: 'prestation')]
        public function prestation($id, Request $request){

                

                $serviceRepository = $this->entityManager->getRepository('App\Entity\Service');
                $service = $serviceRepository->find($id);
                
                return $this->render('home/service.html.twig', [
                        'service' => $service
                ]);

        }


        #[Route('/publication/{id}', name: 'publication')]
        public function publication($id, Request $request){

                

                $publicationRepository = $this->entityManager->getRepository('App\Entity\Publication');
                $publication = $publicationRepository->find($id);
                
                return $this->render('home/publication.html.twig', [
                        'publication' => $publication
                ]);

        }


        #[Route('/evenement/{id}', name: 'evenement')]
        public function evenement($id, Request $request){

                

                $evenementRepository = $this->entityManager->getRepository('App\Entity\Event');
                $evenement = $evenementRepository->find($id);
                
                return $this->render('home/evenement.html.twig', [
                        'evenement' => $evenement
                ]);

        }

}