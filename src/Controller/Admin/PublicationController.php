<?php

namespace App\Controller\Admin;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use App\Form\Type\PublicationEditType;
use App\Form\Type\PublicationDeleteType;
use App\Form\Type\PublicationType;
use App\Entity\Publication;
use App\Entity\Image;
use Symfony\Component\Form\FormInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @Route("/admin/publication")
 */

class PublicationController extends AbstractController{

        private $entityManager;

        public function __construct(ManagerRegistry $doctrine){
            $this->entityManager = $doctrine->getManager();
        }

        #[Route('/add', name: 'add_publication')]
        public function addPublication(Request $request){

                $type = 'publication';

                if($request->query->get('type') !== null){
                        $type = $request->query->get('type');
                }

                $publication  = new Publication();
                $form = $this->createForm(PublicationType::class, $publication);

                $form->handleRequest($request);
                if ($form->isSubmitted() && $form->isValid()) {
                        // $form->getData() holds the submitted values
                        // but, the original `$task` variable has also been updated
                        $publication = $form->getData();


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
                                $this->getParameter('images_publication_directory'),
                                $newFilename
                        );
                        } catch (FileException $e) {
                                $e->getMessage('Erreur lors de l\'upload de l\'image');
                                // ... handle exception if something happens during file upload
                        }

                        // updates the 'imageFilename' property to store the PDF file name
                        // instead of its contents
                        $image = new Image();
                        $image->setUrl('uploads/images/publications/'.$newFilename);
                        $publication->setImage($image);
                        }


                        // ... perform some action, such as saving the task to the database
                        // for example, if Task is a Doctrine entity, save it!
                        
                        $this->entityManager->persist($publication);
                        $this->entityManager->flush();

                        $request->getSession()->getFlashBag()->add('notice', 'Publication bien enregistrée.');

                        return $this->redirectToRoute('add_publication');
                }

                return $this->render('pages/add_publication.html.twig', [
                        'form' => $form->createView(),
                        'type' => $type
                ]);

    }


        #[Route('/edit/{id}', name: 'edit_publication')]
        public function editPublication($id, Request $request){

                
                $publicationRepository = $this->entityManager->getRepository('App\Entity\Publication');
                $publication = $publicationRepository->find($id);

                $form = $this->createForm(PublicationEditType::class, $publication);

                $form->handleRequest($request);
                if ($form->isSubmitted() && $form->isValid()) {
                    // $form->getData() holds the submitted values
                    // but, the original `$task` variable has also been updated
                    $publication = $form->getData();
            

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
                        $this->getParameter('images_publication_directory'),
                        $newFilename
                        );
                } catch (FileException $e) {
                        $e->getMessage('Erreur lors de l\'upload de l\'image');
                        // ... handle exception if something happens during file upload
                }

                // updates the 'imageFilename' property to store the PDF file name
                // instead of its contents
                $image = new Image();
                $image->setUrl('uploads/images/publications/'.$newFilename);
                $publication->setImage($image);
                }


                    // ... perform some action, such as saving the task to the database
                    // for example, if Task is a Doctrine entity, save it!
                    
                    $this->entityManager->persist($publication);
                    $this->entityManager->flush();

                    $request->getSession()->getFlashBag()->add('notice', 'Publication mis à jour avec succès.');

                    return $this->redirectToRoute('edit_publication', ['id' => $publication->getId()]);
                }

                return $this->render('pages/edit_publication.html.twig', [
                        'form' => $form->createView(),
                        'publication' => $publication
                ]);

        }


        #[Route('/delete', name: 'delete_publication')]
        public function deletePublication(Request $request){

                $type = 'publication';
                $type_name = 'Publication';

                if($request->query->get('type') !== null){
                        $type = $request->query->get('type');
                        if($request->query->get('type') == 'event'){
                                $type_name = "Evênement";
                        }
                }

                
                $publicationRepository = $this->entityManager->getRepository('App\Entity\Publication');
                $publications = $publicationRepository->findBy(
                        ['type' => $type]
                );

                if ($request->isMethod('POST')) {

                        $id_publication = $request->request->get('id_publication');

                        $publication = $publicationRepository->find($id_publication);

                        $this->entityManager->remove($publication);
                        $this->entityManager->flush();
                
                        $request->getSession()->getFlashBag()->add('notice', "La publication a été supprimé avec succès !");
                
                        return $this->redirectToRoute('delete_publication');
                  
                        throw new NotFoundHttpException("Erreur lors de la validation du formulaire");
                        
                }
                return $this->render('pages/delete_publications.html.twig', [
                        'publications' => $publications,
                        'type_name' => $type_name
                ]); 

        }


        #[Route('/menu', name: 'publications_menu')]
        public function menuPublication(Request $request){

                $type = 'publication';
                $type_name = 'Publication';

                if($request->query->get('type') !== null){
                        $type = $request->query->get('type');
                        if($request->query->get('type') == 'event'){
                                $type_name = "Evênement";
                        }
                }

                return $this->render('pages/publications_menu.html.twig', [
                        'type' => $type,
                        'type_name' => $type_name
                ]);

        }


        #[Route('/liste', name: 'liste_publications')]
        public function listePublication(Request $request){

                $type = 'publication';
                $type_name = 'Publication';

                if($request->query->get('type') !== null){
                        $type = $request->query->get('type');
                        if($request->query->get('type') == 'event'){
                                $type_name = "Evênement";
                        }
                }
                
                $publicationRepository = $this->entityManager->getRepository('App\Entity\Publication');

                $publications = $publicationRepository->findBy(
                        ['type' => $type]
                );

                return $this->render('pages/publications.html.twig', [
                        'publications' => $publications,
                        'type_name' => $type_name
                ]); 

        }
        
}