<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\Project;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class DashboardController extends AbstractController
{
    public function __construct(private ManagerRegistry $doctrine) {}

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

        $sidebar = [
            'color' => 'blue',
            'title' => 'Username',
            'subtitle' => 'User title',
            'nav_buttons' => [
                [
                    'text' => 'Project list',
                    'link' => $this->generateUrl('dashboard_projects'),
                    'is_selected' => true,
                ],
                [
                    'text' => 'Account settings',
                    'link' => $this->generateUrl('dashboard_account_settings'),
                    'is_selected' => false,
                ],
                [
                    'text' => 'Logout',
                    'link' => 'logout_route',
                    'is_selected' => false,
                ],
            ],
        ];

        return $this->render('dashboard/projects.html.twig', [
            'projects' => $projects,
            'sidebar' => $sidebar,
        ]);
    }

    #[Route('/dashboard/account-settings', name: 'dashboard_account_settings')]
    public function accountSettings(): Response
    {
        // Render the account settings page
        return $this->render('dashboard/account_settings.html.twig');
    }
}
