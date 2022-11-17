<?php

namespace App\Entity\Route;


use ApiPlatform\Core\Annotation\ApiResource;
use App\Repository\Route\SenderApiRepository;
use Doctrine\ORM\Mapping as ORM;

use ApiPlatform\Core\Annotation\ApiFilter;
use ApiPlatform\Core\Annotation\ApiProperty;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\SearchFilter;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity(repositoryClass=SenderApiRepository::class)
 * @ApiResource(
 * collectionOperations = { 
 * "get" = {
 * "normalization_context"={
 *                  "groups"={
 *                      "read:senderApi"
 *                  }
 *              },
 * 
 * },
 * "post"={
 *              "denormalization_context"={
 *                  "groups"={
 *                      "create:senderApi"
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
 *                      "write:senderApi"
 *              },
 *             },
 * }
 * }),
 * 
 * @ApiFilter(
 *    SearchFilter::class, 
 *    properties={ 
 *      "id": "exact",
 *      "operateur": "exact"
 * }
 *)
 */
class SenderApi
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     * @Groups({"read:senderApi"})
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     * @Groups({"read:senderApi","read:route","create:senderApi"})
     */
    private $nom;

    /**
     * @ORM\Column(type="string", length=255)
     * @Groups({"read:senderApi","read:route","create:senderApi"})
     */
    private $api_Link;

    /**
     * @ORM\Column(type="boolean")
     * @Groups({"read:senderApi","read:route","create:senderApi"})
     */
    private $status;

    /**
     * @ORM\ManyToOne(targetEntity=Operateur::class, inversedBy="senderApis")
     * @Groups({"read:senderApi","read:route","create:senderApi"})
     */
    private $operateur;

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

    public function getApiLink(): ?string
    {
        return $this->api_Link;
    }

    public function setApiLink(string $api_Link): self
    {
        $this->api_Link = $api_Link;

        return $this;
    }

    public function getStatus(): ?bool
    {
        return $this->status;
    }

    public function setStatus(bool $status): self
    {
        $this->status = $status;

        return $this;
    }

    public function getOperateur(): ?Operateur
    {
        return $this->operateur;
    }

    public function setOperateur(?Operateur $operateur): self
    {
        $this->operateur = $operateur;

        return $this;
    }
}
