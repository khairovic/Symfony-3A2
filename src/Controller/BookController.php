<?php

namespace App\Controller;

use App\Entity\Book;
use App\Form\BookType;
use App\Repository\BookRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class BookController extends AbstractController
{
    #[Route('/book', name: 'app_book')]
    public function index(): Response
    {
        return $this->render('book/index.html.twig', [
            'controller_name' => 'BookController',
        ]);
    }

    #[Route('/booklist', name: 'booklist')]
    public function listBook(ManagerRegistry $doctrine, Request $request): Response
    {
        $bookRepo = $doctrine->getRepository(Book::class);
        $books = $bookRepo->findAll();

        return $this->render('book/list.html.twig', [
            'books' => $books
        ]);
    }
    #[Route('/showbook/{id}', name: 'showbook')]
    public function ShowBook(BookRepository $bookRepo, $id): Response
    {
        //$bookRepo = $doctrine->getRepository(Book::class);
        $book = $bookRepo->showAllBooksByAuthor($id);

        return $this->render('book/showBook.html.twig', [
            'book' => $book,
        ]);
    }
    #[Route('/delete/{id}', name: 'deletebook')]
    public function deleteBook(ManagerRegistry $doctrine, $id): Response
    {
        $bookRepo = $doctrine->getRepository(Book::class);
        $book = $bookRepo->find($id);

        if (!$book) {
            $this->addFlash('error', 'Book not found!');
            return $this->redirectToRoute('booklist');
        }

        $em = $doctrine->getManager();
        $em->remove($book);
        $em->flush();

        return $this->redirectToRoute('booklist');
    }

    #[Route('/addbook', name: 'add_book')]
    public function addBook(ManagerRegistry $doctrine, Request $request): Response
    {
        $book = new Book(); // data init
        $form = $this->createForm(BookType::class, $book); //form create
        $form->add('Save', SubmitType::class); //import submit
        $form->handleRequest($request);
        if ($form->isSubmitted()) {
            //// data persistence ///
            $em = $doctrine->getManager();
            $em->persist($book);
            $em->flush();
            return $this->redirectToRoute('booklist');
        }
        return $this->render('book/addbook.html.twig', [
            'bookform' => $form->createView(),
        ]);
    }
    #[Route('/updatebook/{id}', name: 'update_book')]
    public function updateBook(ManagerRegistry $doctrine, Request $request, $id): Response
    {
        $bookRepo = $doctrine->getRepository(Book::class);
        $book = $bookRepo->find($id);
        $form = $this->createForm(BookType::class, $book); //form create
        $form->add('Update', SubmitType::class); //import submit
        $form->handleRequest($request);
        if ($form->isSubmitted()) {
            //// data persistence ///
            $em = $doctrine->getManager();
            $em->persist($book);
            $em->flush();
            return $this->redirectToRoute('booklist');
        }
        return $this->render('book/addbook.html.twig', [
            'bookform' => $form->createView(),
        ]);
    }
}
