<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: UserRepository::class)]
class User
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;
    /**
     * @ORM\Column(type="string", length=255, unique=true)
     */
    private $username;
    /**
     * @ORM\Column(type="string", length=255, unique=true)
     */
    private $password;

    public function getId(): ?int
    {
        return $this->id;
    }
    public function getUsername(): ?string
    {
        return $this->username;
    }
    public function getPassword(): ?string
    {
        return $this->password;
    }
}
