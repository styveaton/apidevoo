<?php

namespace App\Entity\Route;


use ApiPlatform\Core\Annotation\ApiResource;
use App\Repository\Route\RouteRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;


use ApiPlatform\Core\Annotation\ApiFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\SearchFilter;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity(repositoryClass=RouteRepository::class)
 * @ApiResource(
 * collectionOperations = { 
 * "get" = {
 * "normalization_context"={
 *                  "groups"={
 *                      "read:route"
 *                  }
 *              },
 * 
 * },
 * "post"={
 *              "denormalization_context"={
 *                  "groups"={
 *                      "create:route"
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
 *                      "write:route"
 *              },
 *           }  
 * }
 * }),
 * 
 * @ApiFilter(
 *    SearchFilter::class, 
 *    properties={ 
 *      "id": "exact",
 *      "pays": "exact"
 * }
 *)
 */
class Route
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     * @Groups({"read:operateur","read:route", "read:pays"})
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     * @Groups({"read:operateur","read:route","write:route","read:senderApi","create:route"})
     */
    private $nom;

    /**
     * @ORM\Column(type="boolean")
     * @Groups({"read:operateur","read:route","write:route","read:senderApi"})
     */
    private $status;

    /**
     * @ORM\Column(type="string", length=255)
     * @Groups({"read:operateur","read:route","write:route","read:senderApi","create:route"})
     */
    private $description;

    /**
     * @ORM\Column(type="float")
     * @Groups({"read:operateur","read:route","write:route","read:senderApi","create:route","read:pays"})
     */
    private $prix;

    /**
     * @ORM\Column(type="float")
     * @Groups({"read:operateur","read:route","write:route","read:senderApi","create:route"})
     */
    private $limite_envois;

    /**
     * @ORM\OneToMany(targetEntity=Operateur::class, mappedBy="route")
     * @Groups({"read:route","write:route","read:senderApi","create:route"})
     */
    private $operateurs;

    /**
     * @ORM\ManyToOne(targetEntity=Pays::class, inversedBy="routes")
     * @Groups({"read:operateur","read:route","write:route","read:senderApi","create:route"})
     */
    private $pays;

    public function __construct()
    {
        $this->operateurs = new ArrayCollection();
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

    public function getStatus(): ?bool
    {
        return $this->status;
    }

    public function setStatus(bool $status): self
    {
        $this->status = $status;

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

    public function getPrix(): ?float
    {
        return $this->prix;
    }

    public function setPrix(float $prix): self
    {
        $this->prix = $prix;

        return $this;
    }

    public function getLimiteEnvois(): ?float
    {
        return $this->limite_envois;
    }

    public function setLimiteEnvois(float $limite_envois): self
    {
        $this->limite_envois = $limite_envois;

        return $this;
    }

    /**
     * @return Collection<int, Operateur>
     */
    public function getOperateurs(): Collection
    {
        return $this->operateurs;
    }

    public function addOperateur(Operateur $operateur): self
    {
        if (!$this->operateurs->contains($operateur)) {
            $this->operateurs[] = $operateur;
            $operateur->setRoute($this);
        }

        return $this;
    }

    public function removeOperateur(Operateur $operateur): self
    {
        if ($this->operateurs->removeElement($operateur)) {
            // set the owning side to null (unless already changed)
            if ($operateur->getRoute() === $this) {
                $operateur->setRoute(null);
            }
        }

        return $this;
    }

    public function getPays(): ?Pays
    {
        return $this->pays;
    }

    public function setPays(?Pays $pays): self
    {
        $this->pays = $pays;

        return $this;
    }
}
