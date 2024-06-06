<?php

namespace App\Controller;

use App\Entity\Project;
use App\Helper\SidebarHelper;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
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
    public function projects(Project $project): Response
    {
        $sidebar = $this->generateControllerSidebar($project);

        return $this->render('project/board.html.twig', [
            'sidebar' => $sidebar,
        ]);
    }

    private function generateControllerSidebar(Project $project) : array {
      $nav_buttons = [
        ['text' => 'Board', 'route' => 'app_project', 'params' => ['id' => $project->getId()]], 
        ['text' => 'List', 'route' => 'app_project', 'params' => ['id' => $project->getId()]],
        ['text' => 'Project Settings', 'route' => 'app_project', 'params' => ['id' => $project->getId()]],
        ['text' => 'Participants', 'route' => 'app_project', 'params' => ['id' => $project->getId()]],
        ['text' => 'Back to dashboard', 'route' => 'app_dashboard'],
      ];

      return $this->sidebarHelper->generateSidebar($project->getTitle(), $project->getSubtitle(), $nav_buttons);
    }
}
