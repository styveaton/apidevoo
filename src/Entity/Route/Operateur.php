<?php

namespace App\Entity\Route;


use ApiPlatform\Core\Annotation\ApiResource;
use App\Entity\Route\Route;
use App\Repository\Route\OperateurRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use ApiPlatform\Core\Annotation\ApiFilter;
use ApiPlatform\Core\Annotation\ApiProperty;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\SearchFilter;

/**
 * @ORM\Entity(repositoryClass=OperateurRepository::class)
 * @ApiResource(
 * collectionOperations = { 
 * "get" = {
 * "security"="is_granted('IS_AUTHENTICATED_FULLY')",
 * "normalization_context"={
 *                  "groups"={
 *                      "read:operateur"
 *                  }
 *              },
 * 
 * },
 * "post"={
 *              "denormalization_context"={
 *                  "groups"={
 *                      "create:operateur"
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
 *                      "write:operateur"
 *              },
 *             },
 * }
 * })
 * ,
 * 
 * @ApiFilter(
 *    SearchFilter::class, 
 *    properties={ 
 *      "id": "exact",
 *      "route": "exact"
 * }
 *)
 */
class Operateur
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     * @Groups({"read:operateur","read:senderId"})
     * */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     * @Groups({"read:operateur","read:senderId"})
     */
    private $nom;

    /**
     * @ORM\Column(type="string", length=255)
     * @Groups({"read:operateur","read:senderId"})
     */
    private $description;

    /**
     * @ORM\Column(type="string", length=255)
     * @Groups({"read:operateur","read:senderId"})
     */
    private $sigle;

    /**
     * @ORM\Column(type="boolean")
     */
    private $status;

    /**
     * @ORM\ManyToOne(targetEntity=Route::class, inversedBy="operateurs")
     * @Groups({"read:operateur","read:senderId"})
     */
    private $route;

    /**
     * @ORM\OneToMany(targetEntity=SenderApi::class, mappedBy="operateur")
     */
    private $senderApis;

    public function __construct()
    {
        $this->senderApis = new ArrayCollection();
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

    public function getSigle(): ?string
    {
        return $this->sigle;
    }

    public function setSigle(string $sigle): self
    {
        $this->sigle = $sigle;

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

    public function getRoute(): ?Route
    {
        return $this->route;
    }

    public function setRoute(?Route $route): self
    {
        $this->route = $route;

        return $this;
    }

    /**
     * @return Collection<int, SenderId>
     */
    public function getSenderApis(): Collection
    {
        return $this->senderApis;
    }

    public function addSenderId(SenderApi $senderApis): self
    {
        if (!$this->senderApis->contains($senderApis)) {
            $this->senderApis[] = $senderApis;
            $senderApis->setOperateur($this);
        }

        return $this;
    }

    public function removeSenderApi(SenderApi $senderApis): self
    {
        if ($this->senderApis->removeElement($senderApis)) {
            // set the owning side to null (unless already changed)
            if ($senderApis->getOperateur() === $this) {
                $senderApis->setOperateur(null);
            }
        }

        return $this;
    }
}
