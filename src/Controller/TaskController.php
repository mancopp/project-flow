<?php

namespace App\Controller;

use App\Entity\Task;
use App\Form\TaskType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class TaskController extends AbstractController
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    #[Route('/tasks', name: 'task_index', methods: ['GET', 'POST'])]
    public function index(): Response
    {
        // Form creation logic
        $task = new Task();
        $form = $this->createForm(TaskType::class, $task);
        // Handle form in a similar way as the 'new' action, but do not redirect or create another view.

        $tasks = $this->entityManager->getRepository(Task::class)->findAll();
        return $this->render('tasks/index.html.twig', [
            'tasks' => $tasks,
            'form' => $form->createView(),  // Pass the form to the template
        ]);
    }

    #[Route('/tasks/{id}', name: 'task_view', methods: ['GET'])]
    public function view(Request $request, Task $task): Response
    {
        return $this->render('tasks/view.html.twig', [
            'task' => $task,
        ]);
    }

    #[Route('/tasks/new', name: 'task_new', methods: ['GET', 'POST'])]
    public function new(Request $request): Response
    {
        $task = new Task();
        $form = $this->createForm(TaskType::class, $task);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->persist($task);
            $this->entityManager->flush();

            $this->addFlash('success', 'Task created successfully!');
            return $this->redirectToRoute('task_index');
        }

        // Return to a route that can show the form errors or log them
        return $this->render('tasks/index.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/tasks/{id}/edit', name: 'task_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Task $task): Response
    {
        // Create the form and handle the request
        $form = $this->createForm(TaskType::class, $task);
        $form->handleRequest($request);

        // Check if form is submitted and valid
        if ($form->isSubmitted() && $form->isValid()) {
            // If form is valid, flush changes to database
            $this->entityManager->flush();
            $this->addFlash('success', 'Task updated successfully');
            return $this->redirectToRoute('task_index');
        }

        // If the request is an AJAX request, return only the form HTML
        if ($request->isXmlHttpRequest()) {
            return $this->render('tasks/_form.html.twig', [
                'form' => $form->createView(),
                'task' => $task,
            ]);
        }

        // If it's a regular request, render the entire page with the form
        return $this->render('tasks/edit.html.twig', [
            'form' => $form->createView(),
            'task' => $task,
        ]);
    }

    #[Route('/tasks/{id}/delete', name: 'task_delete', methods: ['POST'])]
    public function delete(Request $request, Task $task): Response
    {
        if ($this->isCsrfTokenValid('delete' . $task->getId(), $request->request->get('_token'))) {
            $this->entityManager->remove($task);
            $this->entityManager->flush();

            $this->addFlash('success', 'Task deleted successfully!');
        }

        return $this->redirectToRoute('task_index');
    }
}
