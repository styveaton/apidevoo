<?php

namespace App\Entity\Auth;

use ApiPlatform\Core\Annotation\ApiResource;
use App\Repository\Auth\FonctionsRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use ApiPlatform\Core\Annotation\ApiFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\SearchFilter;
use Symfony\Component\Serializer\Annotation\Groups;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\BooleanFilter;

use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\ExistsFilter;


/**
 * @ApiResource(  normalizationContext={
 *          "groups"={
 *              "read:fonction"
 *          }
 *     }, collectionOperations={
 *          "get"={
 *           "normalization_context"={
 *                  "groups"={
 *                       "read:fonction"
 *                  }
 *              },
 *          },
 *         
 *     },)
 * @ORM\Entity(repositoryClass=FonctionsRepository::class)
 
 
 * @ApiFilter(
 *     BooleanFilter::class,
 *     properties={
 *     
 *      "listRoleFonctions.status": "exact"
 *     }
 * )
 * 
 * @ApiFilter(
 *     SearchFilter::class,
 *     properties={
 *     "listRoleFonctions.role.id": "exact"

 *     }
 * )
 *
 */
class Fonctions
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     * @Groups({"read:fonction"})
     * 
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     * @Groups({"read:fonction"})
     */
    private $nom;

    /**
     * @ORM\Column(type="string", length=255)
     * @Groups({"read:fonction"})
     */
    private $description;

    /**
     * @ORM\Column(type="datetime")
     * 
     */
    private $date;

    /**
     * @ORM\OneToMany(targetEntity=ListRoleFonctions::class, mappedBy="fonction")
     * @Groups({"read:fonction"})
     */
    private $listRoleFonctions;

    /**
     * @Groups({"read:fonction"})
     * 
     * @ORM\ManyToOne(targetEntity=Module::class, inversedBy="fonctions")
     */
    private $module;

    public function __construct()
    {
        $this->date  = new \DateTime();
        $this->listRoleFonctions = new ArrayCollection();
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
            $listRoleFonction->setFonction($this);
        }

        return $this;
    }

    public function removeListRoleFonction(ListRoleFonctions $listRoleFonction): self
    {
        if ($this->listRoleFonctions->removeElement($listRoleFonction)) {
            // set the owning side to null (unless already changed)
            if ($listRoleFonction->getFonction() === $this) {
                $listRoleFonction->setFonction(null);
            }
        }

        return $this;
    }

    public function getModule(): ?Module
    {
        return $this->module;
    }

    public function setModule(?Module $module): self
    {
        $this->module = $module;

        return $this;
    }
}
