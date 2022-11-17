<?php

namespace App\Entity\User;


use ApiPlatform\Core\Annotation\ApiResource;
use App\Repository\User\ListProjetClientRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Core\Annotation\ApiFilter;

use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity(repositoryClass=ListProjetClientRepository::class)

 * @ApiResource(
 * collectionOperations = { 
 * "get" = {
 * "normalization_context"={
 *                  "groups"={
 *                      "read:listprojetclient"
 *                  }
 *              },
 * 
 * },
 * "post"={
 *              "denormalization_context"={
 *                  "groups"={
 *                      "create:listprojetclient"
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
 *                      "write:listprojetclient"
 *              },
 *           }  
 * }
 * }),
 * @ApiFilter(
 *     SearchFilter::class,
 *     properties={
 *       
 *      "clientId": "exact"
 *     }
 * )
 *
 */
class ListProjetClient
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     * @Groups({"read:listprojetclient"})
     */
    private $id;

    /**
     * @ORM\Column(type="datetime")
     * @Groups({"read:listprojetclient","create:listprojetclient"})
     */
    private $date;

    /**
     * @ORM\Column(type="integer", nullable=true)
     * @Groups({"read:listprojetclient", "create:listprojetclient"})
     */
    private $clientId;
 
    /**
     * @Groups({"read:listprojetclient"})
     * @ORM\ManyToOne(targetEntity=Projet::class, inversedBy="listProjetClients")
     */
    private $projet;

    public function __construct()
    {
        $this->projets = new ArrayCollection();
        $this->date = new \DateTime();

    }

    public function getId(): ?int
    {
        return $this->id;
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

    
 
    public function getClientId(): ?int
    {
        return $this->clientId;
    }

    public function setClientId(?int $clientId): self
    {
        $this->clientId = $clientId;

        return $this;
    }
 

    public function getProjet(): ?Projet
    {
        return $this->projet;
    }

    public function setProjet(?Projet $projet): self
    {
        $this->projet = $projet;

        return $this;
    }
}
