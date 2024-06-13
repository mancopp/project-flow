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

    #[ORM\ManyToOne(targetEntity: Role::class)]
    private $role;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function __construct(Project $project, User $user, Role $role)
    {
        $this->project = $project;
        $this->user = $user;
        $this->role = $role;
    }

    // Getters and setters
    public function getProject(): ?Project
    {
        return $this->project;
    }

    public function setProject(?Project $project): self
    {
        $this->project = $project;
        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;
        return $this;
    }

    public function getRole(): ?Role
    {
        return $this->role;
    }

    public function setRole(?Role $role): self
    {
        $this->role = $role;
        return $this;
    }
}
