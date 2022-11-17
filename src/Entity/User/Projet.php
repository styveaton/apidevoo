<?php

namespace App\Entity\User;


use ApiPlatform\Core\Annotation\ApiResource;
use App\Repository\User\ProjetRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity(repositoryClass=ProjetRepository::class)
 * @ApiResource(
 * collectionOperations = { 
 * "get" = {
 * "normalization_context"={
 *                  "groups"={
 *                      "read:projet"
 *                  }
 *              },
 * 
 * },
 * "post"={
 *              "denormalization_context"={
 *                  "groups"={
 *                      "create:projet"
 *                  }
 *              },
 *            
 *           
 *          }},
 * 
 * itemOperations={
 *          "get"={},
 * "delete"={},
 *          "patch"={
 *              "denormalization_context"={
 *                  "groups"={
 *                      "write:projet"
 *              },
 *             },
 * }
 * })
 * 
 * 
 */
class Projet
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     * @Groups({"read:projet","read:listprojetclient"})
     */
    private $id;
    /**
     * @ORM\Column(type="string", length=255)
     * @Groups({"read:projet","create:projet","write:projet","read:listprojetclient"})
     */
    private $nomProjet;

    /**
     * @ORM\Column(type="string", length=255)
     * @Groups({"read:projet","read:listprojetclient" })
     */
    private $apiKey;

    /**
     * @ORM\Column(type="float")
     * @Groups({"read:projet","create:projet","write:projet","read:listprojetclient"})
     */
    private $soldeSms;


    /**
     * 
     * @ORM\ManyToOne(targetEntity=Projet::class, inversedBy="projets")
     */
    private $projet;

    /**
     * @ORM\OneToMany(targetEntity=Projet::class, mappedBy="projet")
     */
    private $projets;

    /**
     * @ORM\OneToMany(targetEntity=ListProjetClient::class, mappedBy="projet")
     */
    private $listProjetClients;

    /**
     * 
     * @ORM\Column(type="integer")
     */
    private $licenceId;

    /**
     * @Groups({"read:listprojetclient"})
     * @ORM\Column(type="string", length=255)
     */
    private $apiLink;

    /**
     * @Groups({"read:listprojetclient"})
     * 
     * @ORM\Column(type="text")
     */
    private $descriptionApiLink;


    /**
     * @ORM\Column(type="datetime",nullable=true) 
     */
    private $dateCreated;
    public function __construct()
    {

        $this->dateCreated = new \DateTime();
        $this->projets = new ArrayCollection();
        $this->listProjetClients = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNomProjet(): ?string
    {
        return $this->nomProjet;
    }

    public function setNomProjet(string $nomProjet): self
    {
        $this->nomProjet = $nomProjet;

        return $this;
    }


    public function getApiKey(): ?string
    {
        return $this->apiKey;
    }

    public function setApiKey(string $apiKey): self
    {
        $this->apiKey = $apiKey;

        return $this;
    }

    public function getSoldeSms(): ?float
    {
        return $this->soldeSms;
    }

    public function setSoldeSms(float $soldeSms): self
    {
        $this->soldeSms = $soldeSms;

        return $this;
    }



    public function getProjet(): ?self
    {
        return $this->projet;
    }

    public function setProjet(?self $projet): self
    {
        $this->projet = $projet;

        return $this;
    }

    /**
     * @return Collection<int, self>
     */
    public function getProjets(): Collection
    {
        return $this->projets;
    }

    public function addProjet(self $projet): self
    {
        if (!$this->projets->contains($projet)) {
            $this->projets[] = $projet;
            $projet->setProjet($this);
        }

        return $this;
    }

    public function removeProjet(self $projet): self
    {
        if ($this->projets->removeElement($projet)) {
            // set the owning side to null (unless already changed)
            if ($projet->getProjet() === $this) {
                $projet->setProjet(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, ListProjetClient>
     */
    public function getListProjetClients(): Collection
    {
        return $this->listProjetClients;
    }

    public function addListProjetClient(ListProjetClient $listProjetClient): self
    {
        if (!$this->listProjetClients->contains($listProjetClient)) {
            $this->listProjetClients[] = $listProjetClient;
            $listProjetClient->setProjet($this);
        }

        return $this;
    }

    public function removeListProjetClient(ListProjetClient $listProjetClient): self
    {
        if ($this->listProjetClients->removeElement($listProjetClient)) {
            // set the owning side to null (unless already changed)
            if ($listProjetClient->getProjet() === $this) {
                $listProjetClient->setProjet(null);
            }
        }

        return $this;
    }

    public function getLicenceId(): ?int
    {
        return $this->licenceId;
    }

    public function setLicenceId(int $licenceId): self
    {
        $this->licenceId = $licenceId;

        return $this;
    }

    public function getApiLink(): ?string
    {
        return $this->apiLink;
    }

    public function setApiLink(string $apiLink): self
    {
        $this->apiLink = $apiLink;

        return $this;
    }

    public function getDescriptionApiLink(): ?string
    {
        return $this->descriptionApiLink;
    }

    public function setDescriptionApiLink(string $descriptionApiLink): self
    {
        $this->descriptionApiLink = $descriptionApiLink;

        return $this;
    }
    public function getDateCreated(): ?\DateTimeInterface
    {
        return $this->dateCreated;
    }

    public function setDateCreated(\DateTimeInterface $dateCreated): self
    {
        $this->dateCreated = $dateCreated;

        return $this;
    }
}
