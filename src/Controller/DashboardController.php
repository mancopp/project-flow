<?php

namespace App\Controller;

use App\Entity\Project;
use App\Helper\SidebarHelper;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class DashboardController extends AbstractController
{
    public function __construct(
        private ManagerRegistry $doctrine, 
        private SidebarHelper $sidebarHelper,
        private Security $security
    ) {}

    #[Route('/dashboard', name: 'app_dashboard')]
    public function index(): Response
    {
        return $this->redirectToRoute('dashboard_projects');
    }

    #[Route('/dashboard/projects', name: 'dashboard_projects')]
    public function projects(): Response
    {
        $user = $this->getUser();
        $entityManager = $this->doctrine->getManager();
        $projects = $entityManager->getRepository(Project::class)->findProjectsByUser($user);

        $sidebar = $this->generateControllerSidebar();

        return $this->render('dashboard/projects.html.twig', [
            'projects' => $projects,
            'sidebar' => $sidebar,
        ]);
    }

    #[Route('/dashboard/account-settings', name: 'dashboard_account_settings')]
    public function accountSettings(): Response
    {
      $sidebar = $this->generateControllerSidebar();

        // Render the account settings page
        return $this->render('dashboard/account_settings.html.twig', [
            'sidebar' => $sidebar,
        ]);
    }

    private function generateControllerSidebar() : array {
      $user = $this->security->getUser();
      $nav_buttons = [
        ['text' => 'Project list', 'route' => 'dashboard_projects'], 
        ['text' => 'Account settings', 'route' => 'dashboard_account_settings']
      ];

      return $this->sidebarHelper->generateSidebar($user->getUsername(), $user->getTitle(), $nav_buttons);
    }
}
