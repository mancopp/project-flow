<?php

namespace App\Controller;

use App\Entity\Project;
use App\Entity\User;
use App\Form\AccountSettingsFormType;
use App\Helper\SidebarHelper;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
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
    public function accountSettings(Request $request, UserPasswordHasherInterface $passwordHasher, EntityManagerInterface $entityManager): Response
    {

        $sidebar = $this->generateControllerSidebar();

        $user = $this->getUser();
        
        if (!$user instanceof User) {
            throw new \LogicException('The user is not authenticated.');
        }

        $form = $this->createForm(AccountSettingsFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if ($form->get('plainPassword')->getData()) {
                $user->setPassword(
                    $passwordHasher->hashPassword(
                        $user,
                        $form->get('plainPassword')->getData()
                    )
                );
            }

            $entityManager->flush();

            $this->addFlash('success', 'Your account settings have been updated.');

            return $this->redirectToRoute('dashboard_account_settings');
        }

        return $this->render('dashboard/account_settings.html.twig', [
            'accountSettingsForm' => $form->createView(),
            'sidebar' => $sidebar,
        ]);
    }

    private function generateControllerSidebar() : array {
      $user = $this->security->getUser();
      $nav_buttons = [
        ['text' => 'Project list', 'route' => 'dashboard_projects'], 
        ['text' => 'Account settings', 'route' => 'dashboard_account_settings', 'icon' => 'ic:round-settings'],
        ['text' => 'Log out', 'route' => 'app_logout', 'icon' => 'ic:baseline-logout']
      ];

      return $this->sidebarHelper->generateSidebar($user->getUsername(), $user->getTitle(), $nav_buttons);
    }
}
