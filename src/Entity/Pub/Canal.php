<?php

namespace App\Entity\Pub;

use App\Repository\Pub\CanalRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=CanalRepository::class)
 */
class Canal
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="array")
     */
    private $contenu = [];

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getContenu(): ?array
    {
        return $this->contenu;
    }

    public function setContenu(array $contenu): self
    {
        $this->contenu = $contenu;

        return $this;
    }
}
