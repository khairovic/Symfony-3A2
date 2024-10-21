<?php

namespace App\Controller;

use App\Entity\Author;
use App\Form\AuthorType;
use App\Repository\AuthorRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class AuthorController extends AbstractController
{
    #[Route('/author', name: 'app_author')]
    public function index(): Response
    {
        return $this->render('author/index.html.twig', [
            'controller_name' => 'AuthorController',
        ]);
    }
    #[Route('/list', name: 'list')]
    public function listAuth(AuthorRepository $authorRepo, Request $request): Response
    {
        //$authorRepo = $doctrine->getRepository(Author::class);
        $authors = $authorRepo->findAuthorByName();

        return $this->render('author/list.html.twig', [
            'authors' => $authors
        ]);
    }
    #[Route('/show/{id}', name: 'showauth')]
    public function ShowAuth(ManagerRegistry $doctrine, $id): Response
    {
        $authorRepo = $doctrine->getRepository(Author::class);
        $author = $authorRepo->find($id);

        if (!$author) {
            throw $this->createNotFoundException('Author not found');
        }

        return $this->render('author/showAuthor.html.twig', [
            'author' => $author,
            'books' => $author->getBooks(),
        ]);
    }

    #[Route('/delete/{id}', name: 'deleteauth')]
    public function deleteAuth(ManagerRegistry $doctrine, $id): Response
    {
        $authorRepo = $doctrine->getRepository(Author::class);
        $author = $authorRepo->find($id);

        if (!$author) {
            $this->addFlash('error', "The author with id $id does not exist.");
            return $this->redirectToRoute('list');
        }

        $em = $doctrine->getManager();

        foreach ($author->getBooks() as $book) {
            $em->remove($book);
        }

        $em->remove($author);
        $em->flush();

        $this->addFlash('success', 'Author and associated books successfully deleted.');

        return $this->redirectToRoute('list');
    }


    #[Route('/addauthor', name: 'add_author')]
    public function addAuthor(ManagerRegistry $doctrine, Request $request): Response
    {
        $author = new Author(); // data init
        $form = $this->createForm(AuthorType::class, $author); //form create
        $form->add('Save', SubmitType::class); //import submit
        $form->handleRequest($request);
        if ($form->isSubmitted()) {
            //// data persistence ///
            $em = $doctrine->getManager();
            $em->persist($author);
            $em->flush();
            return $this->redirectToRoute('list');
        }
        return $this->render('author/addauthor.html.twig', [
            'authform' => $form->createView(),
        ]);
    }
    #[Route('/updateauth/{id}', name: 'update_author')]
    public function updateAuthor(ManagerRegistry $doctrine, Request $request, $id): Response
    {
        $authorRepo = $doctrine->getRepository(Author::class);
        $author = $authorRepo->find($id);
        $form = $this->createForm(AuthorType::class, $author); //form create
        $form->add('Update', SubmitType::class); //import submit
        $form->handleRequest($request);
        if ($form->isSubmitted()) {
            //// data persistence ///
            $em = $doctrine->getManager();
            $em->persist($author);
            $em->flush();
            return $this->redirectToRoute('list');
        }
        return $this->render('author/addauthor.html.twig', [
            'authform' => $form->createView(),
        ]);
    }
}
