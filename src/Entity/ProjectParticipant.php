<?php

namespace App\Entity;

use App\Repository\ProjectParticipantRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ProjectParticipantRepository::class)]
class ProjectParticipant
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Project::class, inversedBy: "participants")]
    private $project;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: "projects")]
    private $user;

    #[ORM\Column(type: "string")]
    private $role;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function __construct(Project $project, User $user, string $role)
    {
        $this->project = $project;
        $this->user = $user;
        $this->role = $role;
    }
}
