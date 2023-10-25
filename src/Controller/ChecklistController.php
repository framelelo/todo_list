<?php

namespace App\Controller;

use App\Entity\Checklist;
use App\Form\ChecklistType;
use App\Repository\ChecklistRepository;

use App\Entity\Products;
use App\Form\ProductsType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_USER')]

#[Route('/checklist')]
class ChecklistController extends AbstractController
{
    #[Route('/', name: 'app_checklist_index', methods: ['GET'])]
    public function index(ChecklistRepository $checklistRepository): Response
    {$user = $this->getUser();
        if ($user) {
            $checklists = $checklistRepository->findBy(['user' => $user]);
            return $this->render('checklist/index.html.twig', [
                'checklists' => $checklists
            ]);
        }
    }

    #[Route('/new', name: 'app_checklist_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $checklist = new Checklist();
        $form = $this->createForm(ChecklistType::class, $checklist);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            

            $user = $this->getUser();
            $checklist->setUser($user);

            $entityManager->persist($checklist);
            $entityManager->flush();

            return $this->redirectToRoute('app_checklist_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('checklist/new.html.twig', [
            'checklist' => $checklist,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_checklist_show', methods: ['GET', 'POST'])]
    
    public function show($id, Checklist $checklist, Request $request, EntityManagerInterface $entityManager, ChecklistRepository $checklistRepository): Response
{
        $product = new Products();
        $form = $this->createForm(ProductsType::class, $product);

        $form->handleRequest($request);
        $user = $this->getUser();

        if ($user && $form->isSubmitted() && $form->isValid()) {
            
            $product->setChecklist($checklistRepository->find($id));

            $entityManager->persist($product);
            $entityManager->flush();

            return $this->redirectToRoute('app_checklist_show', ['id' => $checklist->getId()]);
        }

        return $this->render('checklist/show.html.twig', [
            'checklist' => $checklist,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}/edit', name: 'app_checklist_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Checklist $checklist, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(ChecklistType::class, $checklist);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_checklist_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('checklist/edit.html.twig', [
            'checklist' => $checklist,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_checklist_delete', methods: ['POST'])]
    public function delete(Request $request, checklist $checklist, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$checklist->getId(), $request->request->get('_token'))) {
            $entityManager->remove($checklist);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_checklist_index', [], Response::HTTP_SEE_OTHER);
    }
}
