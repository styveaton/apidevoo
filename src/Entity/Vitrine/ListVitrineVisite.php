<?php

namespace App\Entity\Vitrine;

use App\Entity\Vitrine\Vitrine;
use App\Repository\Vitrine\ListVitrineVisiteRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=ListVitrineVisiteRepository::class)
 */
class ListVitrineVisite
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="integer")
     */
    private $clientId;

    /**
     * @ORM\ManyToOne(targetEntity=vitrine::class, inversedBy="listVitrineVisites")
     */
    private $vitrine;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getClientId(): ?int
    {
        return $this->clientId;
    }

    public function setClientId(int $clientId): self
    {
        $this->clientId = $clientId;

        return $this;
    }

    public function getVitrine(): ?Vitrine
    {
        return $this->vitrine;
    }

    public function setVitrine(?Vitrine $vitrine): self
    {
        $this->vitrine = $vitrine;

        return $this;
    }
}
