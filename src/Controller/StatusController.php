<?php

namespace App\Controller;

use App\Entity\Status;
use App\Form\StatusType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class StatusController extends AbstractController
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    #[Route('/status', name: 'status_index', methods: ['GET', 'POST'])]
    public function index(Request $request): Response
    {
        $status = new Status();
        $form = $this->createForm(StatusType::class, $status);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->persist($status);
            $this->entityManager->flush();
            $this->addFlash('success', 'Status created successfully!');
            return $this->redirectToRoute('status_index');
        }

        $statuses = $this->entityManager->getRepository(Status::class)->findAll();
        return $this->render('status/index.html.twig', [
            'statuses' => $statuses,
            'form' => $form->createView(),
        ]);
    }
    #[Route('/status/new', name: 'status_new', methods: ['GET', 'POST'])]
    public function new(Request $request): Response
    {
        $status = new Status();
        $form = $this->createForm(StatusType::class, $status);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->persist($status);
            $this->entityManager->flush();

            $this->addFlash('success', 'New status added successfully!');
            return $this->redirectToRoute('status_index');
        }

        return $this->render('status/index.html.twig', [
            'form' => $form->createView(),
        ]);
    }
    #[Route('/status/{id}/edit', name: 'status_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Status $status): Response
    {
        if ($status->getIsDefault()) {
            $this->addFlash('error', 'Default statuses cannot be edited.');
            return $this->redirectToRoute('status_index');
        }

        $form = $this->createForm(StatusType::class, $status, ['is_edit' => true]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->flush();
            $this->addFlash('success', 'Status updated successfully!');
            return $this->redirectToRoute('status_index');
        }

        return $this->render('status/edit.html.twig', [
            'status' => $status,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/status/{id}/delete', name: 'status_delete', methods: ['POST'])]
    public function delete(Request $request, Status $status): Response
    {
        if (!$status->getIsDefault() && $this->isCsrfTokenValid('delete' . $status->getId(), $request->request->get('_token'))) {
            $this->entityManager->remove($status);
            $this->entityManager->flush();
            $this->addFlash('success', 'Status deleted successfully!');
        } else {
            $this->addFlash('error', 'Default status cannot be deleted.');
        }

        return $this->redirectToRoute('status_index');
    }
}
