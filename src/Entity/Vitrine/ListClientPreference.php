<?php

namespace App\Entity\Vitrine;

use App\Entity\Vitrine\Preference;
use App\Repository\Vitrine\ListClientPreferenceRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=ListClientPreferenceRepository::class)
 */
class ListClientPreference
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity=Preference::class, inversedBy="listClientPreferences")
     */
    private $preference;

    /**
     * @ORM\Column(type="integer")
     */
    private $clientId;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPreference(): ?Preference
    {
        return $this->preference;
    }

    public function setPreference(?Preference $preference): self
    {
        $this->preference = $preference;

        return $this;
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
}
