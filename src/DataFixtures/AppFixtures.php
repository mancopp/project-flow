<?php

namespace App\DataFixtures;

use App\Entity\Role;
use App\Entity\User;
use App\Entity\Project;
use App\Entity\ProjectParticipant;
use App\Entity\Task;
use App\Entity\Status;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Psr\Log\LoggerInterface;

class AppFixtures extends Fixture
{
    public function __construct(private UserPasswordHasherInterface $userPasswordHasher){}

    public function load(ObjectManager $manager)
    {
        // Create roles
        $roles = ['ROLE_ADMIN', 'ROLE_MANAGER', 'ROLE_MAINTAINER', 'ROLE_CLIENT'];

        foreach ($roles as $roleName) {
            $role = new Role();
            $role->setName($roleName);
            $manager->persist($role);
        }

        $statuses = ['To Do', 'In Progress', 'Review', 'Done'];

        foreach ($statuses as $statusName) {
            $status = new Status();
            $status->setName($statusName);
            $manager->persist($status);
        }

        $manager->flush();

        // Create users
        $users = [];
        for ($i = 1; $i <= 12; $i++) {
            $user = new User();
            $user->setUsername("user$i");
            $user->setTitle("Software Enginner");
            $user->setEmail("user$i@example.com");
            $user->setPassword(
              $this->userPasswordHasher->hashPassword($user, 'test')
            );
            $manager->persist($user);
            $users[] = $user;
        }

        // Create ProjectFlow project
        $project = new Project();
        $project->setTitle('ProjectFlow');
        $project->setSubtitle('Software Project');
        $manager->persist($project);

        // Create ProjectParticipant for each user in ProjectFlow project
        foreach ($users as $user) {
            $role = $manager->getRepository(Role::class)->findOneBy(['name' => 'ROLE_MAINTAINER']);
            $participant = new ProjectParticipant($project, $user, $role);
            $manager->persist($participant);
        }

        // Create statuses
        $statuses = $manager->getRepository(Status::class)->findAll();

        for ($i = 1; $i <= 30; $i++) {
            $task = new Task();
            $task->setName("Task $i");
            $task->setDescription("Description for Task $i");
            $randomStatus = $statuses[array_rand($statuses)];
            $task->setStatus($randomStatus); // Assign a random status
            $task->setProject($project);
            $manager->persist($task);
        }

        $manager->flush();
    }
}
