<?php

namespace App\Controller;

use App\Entity\Project;
use App\Entity\ProjectParticipant;
use App\Entity\Task;
use App\Helper\SidebarHelper;
use App\Form\TaskFormType;
use App\Form\ProjectFormType;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use App\Repository\TaskRepository;
use App\Repository\StatusRepository;

class ProjectController extends AbstractController
{
    private $entityManager;
    public function __construct(
        private ManagerRegistry $doctrine,
        private SidebarHelper $sidebarHelper,
        private Security $security,
        EntityManagerInterface $entityManager
    ) {
        $this->entityManager = $entityManager;
    }

    #[Route('/project/{id}', name: 'app_project')]
    public function index(Project $project): Response
    {
        return $this->redirectToRoute('project_board', ['id' => $project->getId()]);
    }

    #[Route('/project/{id}/board', name: 'project_board')]
    public function project_board(Project $project, TaskRepository $taskRepository, StatusRepository $statusRepository): Response
    {
        $sidebar = $this->generateControllerSidebar($project);
        $statuses = $statusRepository->findAll();
        $tasks = $taskRepository->findBy(['project' => $project]);

        $tasksByStatus = [];
        foreach ($statuses as $status) {
            $tasksByStatus[$status->getName()] = array_filter($tasks, function($task) use ($status) {
                return $task->getStatus() === $status;
            });
        }

        return $this->render('project/board.html.twig', [
            'sidebar' => $sidebar,
            'project' => $project,
            'statuses' => $statuses,
            'tasksByStatus' => $tasksByStatus,
        ]);
    }

    #[Route('/project/{id}/update-task-status', name: 'update_task_status', methods: ['POST'])]
    public function updateTaskStatus(Request $request, TaskRepository $taskRepository, StatusRepository $statusRepository): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $taskId = $data['taskId'];
        $statusId = $data['statusId'];

        $task = $taskRepository->find($taskId);
        $status = $statusRepository->find($statusId);

        if ($task && $status) {
            $task->setStatus($status);
            $this->entityManager->flush();

            return new JsonResponse(['status' => 'success']);
        }

        return new JsonResponse(['status' => 'error', 'message' => 'Invalid task or status'], 400);
    }

    #[Route('/project/{id}/list', name: 'project_list')]
    public function project_list(Project $project, Request $request, TaskRepository $taskRepository, StatusRepository $statusRepository): Response
    {
        $sidebar = $this->generateControllerSidebar($project);
        $sort = $request->query->get('sort', 'name');
        $direction = $request->query->get('direction', 'asc');
        $search = $request->query->get('search', '');
        $statusId = $request->query->get('status', '');

        $tasks = $taskRepository->findByProjectAndCriteria($project, $sort, $direction, $search, $statusId);
        $statuses = $statusRepository->findAll();

        return $this->render('project/list.html.twig', [
            'sidebar' => $sidebar,
            'tasks' => $tasks,
            'sort' => $sort,
            'direction' => $direction,
            'search' => $search,
            'statuses' => $statuses,
            'statusId' => $statusId,
        ]);
    }

    #[Route('/project/{id}/add-task', name: 'project_add_task')]
    public function project_add_task(Request $request, Project $project): Response
    {
        $sidebar = $this->generateControllerSidebar($project);
        $entityManager = $this->doctrine->getManager();

        $task = new Task();
        $task->setProject($project);

        $form = $this->createForm(TaskFormType::class, $task);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($task);
            $entityManager->flush();

            return $this->redirectToRoute('project_list', ['id' => $project->getId()]);
        }

        return $this->render('project/add_task.html.twig', [
            'sidebar' => $sidebar,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/project/{id}/settings', name: 'project_settings')]
    public function project_settings(Project $project, Request $request): Response
    {
        $form = $this->createForm(ProjectFormType::class, $project);

        // Handle form submission
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $this->doctrine->getManager()->flush();
            $this->addFlash('success', 'Project settings updated successfully.');
            return $this->redirectToRoute('project_settings', ['id' => $project->getId()]);
        }

        $sidebar = $this->generateControllerSidebar($project);

        return $this->render('project/settings.html.twig', [
            'sidebar' => $sidebar,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/project/{id}/participants', name: 'project_participants')]
    public function project_participants(Project $project): Response
    {
        $sidebar = $this->generateControllerSidebar($project);

        // Fetch project participants and their roles
        $participants = $this->doctrine->getRepository(ProjectParticipant::class)->findBy(['project' => $project]);

        $users = [];
        foreach ($participants as $participant) {
            $users[] = [
                'username' => $participant->getUser()->getUsername(),
                'email' => $participant->getUser()->getEmail(),
                'role' => $participant->getRole()->getName()
            ];
        }


        return $this->render('project/participants.html.twig', [
            'sidebar' => $sidebar,
            'users' => $users
        ]);
    }

    private function generateControllerSidebar(Project $project): array
    {
        $nav_buttons = [
            ['text' => 'Board', 'route' => 'project_board', 'params' => ['id' => $project->getId()], 'icon' => 'mi:board'],
            ['text' => 'List', 'route' => 'project_list', 'params' => ['id' => $project->getId()]],
            ['text' => 'Add Task', 'route' => 'project_add_task', 'params' => ['id' => $project->getId()], 'icon' => 'material-symbols:add-ad'],
            ['text' => 'Project Settings', 'route' => 'project_settings', 'params' => ['id' => $project->getId()], 'icon' => 'ic:round-settings'],
            ['text' => 'Participants', 'route' => 'project_participants', 'params' => ['id' => $project->getId()], 'icon' => 'mdi:users'],
            ['text' => 'Back to dashboard', 'route' => 'app_dashboard', 'icon' => 'ion:arrow-back-outline'],
        ];

        return $this->sidebarHelper->generateSidebar($project->getTitle(), $project->getSubtitle(), $nav_buttons);
    }
}
