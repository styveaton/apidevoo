<?php

namespace App\Entity\Vitrine;

use ApiPlatform\Core\Annotation\ApiResource;
use App\Repository\Vitrine\TypeVitrineRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=TypeVitrineRepository::class)
 * @ApiResource()
 */
class TypeVitrine
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $libelle;

    /**
     * @ORM\OneToMany(targetEntity=Vitrine::class, mappedBy="typeVitrine")
     */
    private $vitrines;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $link;

    public function __construct()
    {
        $this->vitrines = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getLibelle(): ?string
    {
        return $this->libelle;
    }

    public function setLibelle(string $libelle): self
    {
        $this->libelle = $libelle;

        return $this;
    }

    /**
     * @return Collection<int, Vitrine>
     */
    public function getVitrines(): Collection
    {
        return $this->vitrines;
    }

    public function addVitrine(Vitrine $vitrine): self
    {
        if (!$this->vitrines->contains($vitrine)) {
            $this->vitrines[] = $vitrine;
            $vitrine->setTypeVitrine($this);
        }

        return $this;
    }

    public function removeVitrine(Vitrine $vitrine): self
    {
        if ($this->vitrines->removeElement($vitrine)) {
            // set the owning side to null (unless already changed)
            if ($vitrine->getTypeVitrine() === $this) {
                $vitrine->setTypeVitrine(null);
            }
        }

        return $this;
    }

    public function getLink(): ?string
    {
        return $this->link;
    }

    public function setLink(?string $link): self
    {
        $this->link = $link;

        return $this;
    }
}
