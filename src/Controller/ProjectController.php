<?php

namespace App\Controller;

use App\Entity\Project;
use App\Entity\Task;
use App\Helper\SidebarHelper;
use App\Form\TaskFormType;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class ProjectController extends AbstractController
{
    public function __construct(
        private ManagerRegistry $doctrine, 
        private SidebarHelper $sidebarHelper,
        private Security $security
    ) {}

    #[Route('/project/{id}', name: 'app_project')]
    public function index(Project $project): Response
    {
      return $this->redirectToRoute('project_board', ['id' => $project->getId()]);
    }

    #[Route('/project/{id}/board', name: 'project_board')]
    public function project_board(Project $project): Response
    {
        $sidebar = $this->generateControllerSidebar($project);

        return $this->render('project/board.html.twig', [
            'sidebar' => $sidebar,
        ]);
    }

    #[Route('/project/{id}/list', name: 'project_list')]
    public function project_list(Project $project): Response
    {
        $sidebar = $this->generateControllerSidebar($project);
        $entityManager = $this->doctrine->getManager();
        $tasks = $entityManager->getRepository(Task::class)->findTasksByProject($project);

        return $this->render('project/list.html.twig', [
            'sidebar' => $sidebar,
            'tasks' => $tasks
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
    public function project_settings(Project $project): Response
    {
        $sidebar = $this->generateControllerSidebar($project);

        return $this->render('project/board.html.twig', [
            'sidebar' => $sidebar,
        ]);
    }

    #[Route('/project/{id}/participants', name: 'project_participants')]
    public function project_participants(Project $project): Response
    {
        $sidebar = $this->generateControllerSidebar($project);

        return $this->render('project/board.html.twig', [
            'sidebar' => $sidebar,
        ]);
    }

    private function generateControllerSidebar(Project $project) : array {
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
