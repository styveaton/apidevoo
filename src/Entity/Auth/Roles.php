<?php

namespace App\Entity\Auth;

use App\Repository\Auth\RolesRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use ApiPlatform\Core\Annotation\ApiResource;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ApiResource()
 * @ORM\Entity(repositoryClass=RolesRepository::class)
 */
class Roles
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     * @Groups({"read:fonction"})
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     * @Groups({"read:fonction"})
     * 
     */
    private $nom;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $description;

    /**
     * @ORM\Column(type="datetime")
     */
    private $date;

    /**
     * @Groups({"read:client", "update:client"})
     * @ORM\OneToMany(targetEntity=ListRoleFonctions::class, mappedBy="role")
     */
    private $listRoleFonctions;

    /**
     * @ORM\OneToMany(targetEntity=Client::class, mappedBy="role")
     */
    private $clients;

    public function __construct()
    {
        $this->date = new \DateTime();
        $this->listRoleFonctions = new ArrayCollection();
        $this->clients = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNom(): ?string
    {
        return $this->nom;
    }

    public function setNom(string $nom): self
    {
        $this->nom = $nom;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getDate(): ?\DateTimeInterface
    {
        return $this->date;
    }

    public function setDate(\DateTimeInterface $date): self
    {
        $this->date = $date;

        return $this;
    }

    /**
     * @return Collection<int, ListRoleFonctions>
     */
    public function getListRoleFonctions(): Collection
    {
        return $this->listRoleFonctions;
    }

    public function addListRoleFonction(ListRoleFonctions $listRoleFonction): self
    {
        if (!$this->listRoleFonctions->contains($listRoleFonction)) {
            $this->listRoleFonctions[] = $listRoleFonction;
            $listRoleFonction->setRole($this);
        }

        return $this;
    }

    public function removeListRoleFonction(ListRoleFonctions $listRoleFonction): self
    {
        if ($this->listRoleFonctions->removeElement($listRoleFonction)) {
            // set the owning side to null (unless already changed)
            if ($listRoleFonction->getRole() === $this) {
                $listRoleFonction->setRole(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Client>
     */
    public function getClients(): Collection
    {
        return $this->clients;
    }

    public function addClient(Client $client): self
    {
        if (!$this->clients->contains($client)) {
            $this->clients[] = $client;
            $client->setRole($this);
        }

        return $this;
    }

    public function removeClient(Client $client): self
    {
        if ($this->clients->removeElement($client)) {
            // set the owning side to null (unless already changed)
            if ($client->getRole() === $this) {
                $client->setRole(null);
            }
        }

        return $this;
    }
}
