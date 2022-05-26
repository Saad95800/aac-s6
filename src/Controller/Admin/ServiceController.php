<?php

namespace App\Controller\Admin;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use App\Form\Type\ServiceEditType;
use App\Form\Type\ServiceType;
use App\Entity\Service;
use App\Entity\Image;
use Symfony\Component\Form\FormInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @Route("/admin/service")
 */

class ServiceController extends AbstractController{

        private $entityManager;

        public function __construct(ManagerRegistry $doctrine){
            $this->entityManager = $doctrine->getManager();
        }
        
        #[Route('/add', name: 'add_service')]
        public function addServices(Request $request){

            // 
            // $serviceRepository = $this->entityManager->getRepository('App\Entity\Service');
            // $services = $serviceRepository->findAll();

            $service  = new Service();
            $form = $this->createForm(ServiceType::class, $service);

            $form->handleRequest($request);
            if ($form->isSubmitted() && $form->isValid()) {
                // $form->getData() holds the submitted values
                // but, the original `$task` variable has also been updated
                $service = $form->getData();
        

            /** @var UploadedFile $brochureFile */
            $imageFile = $form->get('image')->getData();

            // this condition is needed because the 'brochure' field is not required
            // so the PDF file must be processed only when a file is uploaded
            if ($imageFile) {
                $originalFilename = pathinfo($imageFile->getClientOriginalName(), PATHINFO_FILENAME);
                // this is needed to safely include the file name as part of the URL
                $safeFilename = transliterator_transliterate('Any-Latin; Latin-ASCII; [^A-Za-z0-9_] remove; Lower()', $originalFilename);
                $newFilename = $safeFilename.'-'.uniqid().'.'.$imageFile->guessExtension();

                // Move the file to the directory where brochures are stored
                try {
                        $imageFile->move(
                        $this->getParameter('images_service_directory'),
                        $newFilename
                        );
                } catch (FileException $e) {
                        $e->getMessage('Erreur lors de l\'upload de l\'image');
                        // ... handle exception if something happens during file upload
                }

                // updates the 'imageFilename' property to store the PDF file name
                // instead of its contents
                $image = new Image();
                $image->setUrl('uploads/images/services/'.$newFilename);
                $service->setImage($image);
            }


                // ... perform some action, such as saving the task to the database
                // for example, if Task is a Doctrine entity, save it!
                
                $this->entityManager->persist($service);
                $this->entityManager->flush();

                $request->getSession()->getFlashBag()->add('notice', 'Service bien enregistrée.');

                return $this->redirectToRoute('add_service');
            }

            return $this->render('pages/add_service.html.twig', [
                    'form' => $form->createView(),
            ]);

    }

        /**
         * @Route("/edit/{id}", name="edit_service")
         */
        #[Route('/edit/{id}', name: 'edit_service')]
        public function editService($id, Request $request){

                
                $serviceRepository = $this->entityManager->getRepository('App\Entity\Service');
                $service = $serviceRepository->find($id);

                $form = $this->createForm(ServiceEditType::class, $service);

                $form->handleRequest($request);
                if ($form->isSubmitted() && $form->isValid()) {
                    // $form->getData() holds the submitted values
                    // but, the original `$task` variable has also been updated
                    $service = $form->getData();
            

                /** @var UploadedFile $brochureFile */
                $imageFile = $form->get('image')->getData();

                // this condition is needed because the 'brochure' field is not required
                // so the PDF file must be processed only when a file is uploaded
                if ($imageFile) {
                $originalFilename = pathinfo($imageFile->getClientOriginalName(), PATHINFO_FILENAME);
                // this is needed to safely include the file name as part of the URL
                $safeFilename = transliterator_transliterate('Any-Latin; Latin-ASCII; [^A-Za-z0-9_] remove; Lower()', $originalFilename);
                $newFilename = $safeFilename.'-'.uniqid().'.'.$imageFile->guessExtension();

                // Move the file to the directory where brochures are stored
                try {
                        $imageFile->move(
                        $this->getParameter('images_service_directory'),
                        $newFilename
                        );
                } catch (FileException $e) {
                        $e->getMessage('Erreur lors de l\'upload de l\'image');
                        // ... handle exception if something happens during file upload
                }

                // updates the 'imageFilename' property to store the PDF file name
                // instead of its contents
                $image = new Image();
                $image->setUrl('uploads/images/services/'.$newFilename);
                $service->setImage($image);
                }


                    // ... perform some action, such as saving the task to the database
                    // for example, if Task is a Doctrine entity, save it!
                    
                    $this->entityManager->persist($service);
                    $this->entityManager->flush();

                    $request->getSession()->getFlashBag()->add('notice', 'Service mis à jour avec succès.');

                    return $this->redirectToRoute('edit_service', ['id' => $service->getId()]);
                }

                return $this->render('pages/edit_service.html.twig', [
                        'form' => $form->createView(),
                        'service' => $service
                ]);

        }


        #[Route('/delete', name: 'delete_services')]
        public function deleteServices(Request $request){

                
                $serviceRepository = $this->entityManager->getRepository('App\Entity\Service');
                $services = $serviceRepository->findAll();
                    
                $services = $serviceRepository->findAll();

                if ($request->isMethod('POST')) {

                        $id_service = $request->request->get('id_service');

                        $service = $serviceRepository->find($id_service);

                            $this->entityManager->remove($service);
                            $this->entityManager->flush();
                  
                            $request->getSession()->getFlashBag()->add('notice', "Le service a été supprimé avec succès !");
                  
                            return $this->redirectToRoute('delete_services');
                  
                        throw new NotFoundHttpException("Erreur lors de la validation du formulaire");
                        
                }

                return $this->render('pages/delete_services.html.twig', [
                        'services' => $services
                ]); 

        }


        #[Route('/menu', name: 'services_menu')]
        public function menuServices(Request $request){

                return $this->render('pages/services_menu.html.twig'); 

        }


        #[Route('/liste', name: 'liste_services')]
        public function listeServices(Request $request){

                
                $serviceRepository = $this->entityManager->getRepository('App\Entity\Service');
                $services = $serviceRepository->findAll();

                return $this->render('pages/services.html.twig', [
                        'services' => $services
                ]); 

        }
        
}