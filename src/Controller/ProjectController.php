<?php

namespace App\Controller;

use App\Entity\Project;
use App\Entity\ProjectParticipant;
use App\Entity\Task;
use App\Entity\User;
use App\Entity\Role;
use App\Helper\SidebarHelper;
use App\Form\TaskFormType;
use App\Form\ProjectFormType;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
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
    public function project_participants(Project $project, Security $security): Response
    {
        $currentUser = $this->getUser();

        $currentParticipant = $this->doctrine->getRepository(ProjectParticipant::class)->findOneBy([
            'project' => $project,
            'user' => $currentUser
        ]);

        if (!$currentParticipant) {
            throw new AccessDeniedException('You do not have permission to access this page.');
        }

        $currentRole = $currentParticipant->getRole()->getName();

        if ($currentRole === 'ROLE_USER') {
            throw new AccessDeniedException('You do not have permission to access this page.');
        }

        $sidebar = $this->generateControllerSidebar($project);

        // Fetch project participants and their roles
        $participants = $this->doctrine->getRepository(ProjectParticipant::class)->findBy(['project' => $project]);

        $users = [];
        foreach ($participants as $participant) {
            $users[] = [
                'id' => $participant->getUser()->getId(),  // Add user ID here
                'username' => $participant->getUser()->getUsername(),
                'title' => $participant->getUser()->getTitle(),
                'email' => $participant->getUser()->getEmail(),
                'role' => $participant->getRole()->getName()
            ];
        }

        $availableRoles = ['ROLE_USER', 'ROLE_MANAGER', 'ROLE_MAINTAINER', 'ROLE_ADMIN'];  // FIXME: Hardcode

        $canEditParticipants = $currentRole === 'ROLE_MANAGER' || $currentRole === 'ROLE_ADMIN';
        $canEditRoles = $currentRole === 'ROLE_ADMIN';

        return $this->render('project/participants.html.twig', [
            'sidebar' => $sidebar,
            'users' => $users,
            'canEditParticipants' => $canEditParticipants,
            'canEditRoles' => $canEditRoles,
            'availableRoles' => $availableRoles,
            'project' => $project
        ]);
    }

    #[Route('/project/{projectId}/add-participant', name: 'add_participant')]
    public function addParticipant(Request $request, int $projectId): RedirectResponse
    {
        $project = $this->doctrine->getRepository(Project::class)->find($projectId);
        $email = $request->request->get('email');
        $user = $this->doctrine->getRepository(User::class)->findOneBy(['email' => $email]);

        if ($project && $user) {
            $existingParticipant = $this->doctrine->getRepository(ProjectParticipant::class)->findOneBy([
                'project' => $project,
                'user' => $user
            ]);

            if (!$existingParticipant) {
                $role = $this->doctrine->getRepository(Role::class)->findOneBy(['name' => 'ROLE_USER']);
                $participant = new ProjectParticipant($project, $user, $role);

                $entityManager = $this->doctrine->getManager();
                $entityManager->persist($participant);
                $entityManager->flush();

                $this->addFlash('success', sprintf('User "%s" successfully added as a participant.', $user->getUsername()));
            } else {
                $this->addFlash('warning', sprintf('User "%s" is already a participant.', $user->getUsername()));
            }
        } else {
            $this->addFlash('error', 'No user found with this email.');
        }

        return $this->redirectToRoute('project_participants', ['id' => $projectId]);
    }

    #[Route('/project/{projectId}/remove-participant/{userId}', name: 'remove_participant')]
    public function removeParticipant(int $projectId, string $userId): RedirectResponse
    {
        $project = $this->doctrine->getRepository(Project::class)->find($projectId);
        $user = $this->doctrine->getRepository(User::class)->find($userId);

        if ($project && $user) {
            $participant = $this->doctrine->getRepository(ProjectParticipant::class)->findOneBy([
                'project' => $project,
                'user' => $user
            ]);

            if ($participant) {
                $entityManager = $this->doctrine->getManager();
                $entityManager->remove($participant);
                $entityManager->flush();

                $this->addFlash('success', 'Participant removed successfully.');
            }
        }

        return $this->redirectToRoute('project_participants', ['id' => $projectId]);
    }

    #[Route('/project/{projectId}/change-role/{userId}', name: 'change_role')]
    public function changeRole(Request $request, int $projectId, string $userId): RedirectResponse
    {
        $project = $this->doctrine->getRepository(Project::class)->find($projectId);
        $user = $this->doctrine->getRepository(User::class)->find($userId);
        $newRole = $request->request->get('newRole');

        if ($project && $user && $newRole) {
            $participant = $this->doctrine->getRepository(ProjectParticipant::class)->findOneBy([
                'project' => $project,
                'user' => $user
            ]);

            if ($participant) {
                $role = $this->doctrine->getRepository(Role::class)->findOneBy(['name' => $newRole]);
                if ($role) {
                    $participant->setRole($role);
                    $entityManager = $this->doctrine->getManager();
                    $entityManager->flush();

                    $this->addFlash('success', 'Role updated successfully.');
                }
            }
        }

        return $this->redirectToRoute('project_participants', ['id' => $projectId]);
    }

    private function generateControllerSidebar(Project $project): array
    {
        $currentParticipant = $this->doctrine->getRepository(ProjectParticipant::class)->findOneBy([
            'project' => $project,
            'user' => $this->getUser()
        ]);

        $currentRole = $currentParticipant->getRole()->getName();
        
        $nav_buttons = [
            ['text' => 'Board', 'route' => 'project_board', 'params' => ['id' => $project->getId()], 'icon' => 'mi:board'],
            ['text' => 'List', 'route' => 'project_list', 'params' => ['id' => $project->getId()]],
            ['text' => 'Add Task', 'route' => 'project_add_task', 'params' => ['id' => $project->getId()], 'icon' => 'material-symbols:add-ad'],
            ['text' => 'Project Settings', 'route' => 'project_settings', 'params' => ['id' => $project->getId()], 'icon' => 'ic:round-settings'],
        ];

        if($currentRole != 'ROLE_USER') {
            array_push($nav_buttons, ['text' => 'Participants', 'route' => 'project_participants', 'params' => ['id' => $project->getId()], 'icon' => 'mdi:users']);
        }

        array_push($nav_buttons, ['text' => 'Back to dashboard', 'route' => 'app_dashboard', 'icon' => 'ion:arrow-back-outline']);

        return $this->sidebarHelper->generateSidebar($project->getTitle(), $project->getSubtitle(), $nav_buttons);
    }
}
