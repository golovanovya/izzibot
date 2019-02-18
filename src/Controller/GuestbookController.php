<?php

namespace App\Controller;

use App\Entity\Guestbook;
use App\Form\GuestbookType;
use App\Repository\GuestbookRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Knp\Component\Pager\PaginatorInterface;

/**
 * @Route("/guestbook")
 */
class GuestbookController extends AbstractController
{
    /**
     * @Route("/", name="guestbook_index", methods="GET")
     */
    public function index(Request $request, GuestbookRepository $guestbookRepository, PaginatorInterface $paginator): Response
    {
        $pagination = $paginator->paginate(
            $guestbookRepository
                ->createQueryBuilder('guestbook')
                ->getQuery(),
            $request->query->getInt('page', 1),
            25
        );
        return $this->render('guestbook/index.html.twig', ['pagination' => $pagination,]);
    }

    /**
     * @Route("/new", name="guestbook_new", methods="GET|POST")
     */
    public function new(Request $request): Response
    {
        $guestbook = new Guestbook();
        $guestbook->setIp($request->getClientIp());
        $guestbook->setUseragent($request->headers->get('User-Agent'));
        $form = $this->createForm(GuestbookType::class, $guestbook);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($guestbook);
            $em->flush();

            return $this->redirectToRoute('guestbook_index');
        }

        return $this->render('guestbook/new.html.twig', [
            'admin-guestbook' => $guestbook,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="guestbook_show", methods="GET")
     */
    public function show(Guestbook $guestbook): Response
    {
        return $this->render('guestbook/show.html.twig', ['guestbook' => $guestbook]);
    }

    /**
     * @Route("/{id}/edit", name="guestbook_edit", methods="GET|POST")
     */
    public function edit(Request $request, Guestbook $guestbook): Response
    {
        $form = $this->createForm(GuestbookType::class, $guestbook);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('guestbook_index', ['id' => $guestbook->getId()]);
        }

        return $this->render('guestbook/edit.html.twig', [
            'guestbook' => $guestbook,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="guestbook_delete", methods="DELETE")
     */
    public function delete(Request $request, Guestbook $guestbook): Response
    {
        if ($this->isCsrfTokenValid('delete'.$guestbook->getId(), $request->request->get('_token'))) {
            $em = $this->getDoctrine()->getManager();
            $em->remove($guestbook);
            $em->flush();
        }

        return $this->redirectToRoute('guestbook_index');
    }
}
