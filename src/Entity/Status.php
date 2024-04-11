<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'status')]
class Status
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(type: 'string', length: 255)]
    private ?string $name = null;

    #[ORM\OneToMany(mappedBy: 'status', targetEntity: Task::class)]
    private Collection $tasks;

    public function __construct()
    {
        $this->tasks = new ArrayCollection();
    }

    // Getter for ID
    public function getId(): ?int
    {
        return $this->id;
    }

    // Setter for ID
    public function setId(?int $id): self
    {
        $this->id = $id;

        return $this;
    }

    // Getter for name
    public function getName(): ?string
    {
        return $this->name;
    }

    // Setter for name
    public function setName(?string $name): self
    {
        $this->name = $name;

        return $this;
    }

    // Getter for tasks
    public function getTasks(): Collection
    {
        return $this->tasks;
    }

    // Add a task to the collection
    public function addTask(Task $task): self
    {
        if (!$this->tasks->contains($task)) {
            $this->tasks[] = $task;
            $task->setStatus($this);
        }

        return $this;
    }

    // Remove a task from the collection
    public function removeTask(Task $task): self
    {
        if ($this->tasks->removeElement($task)) {
            // set the owning side to null (unless already changed)
            if ($task->getStatus() === $this) {
                $task->setStatus(null);
            }
        }

        return $this;
    }
}
