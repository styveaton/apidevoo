<?php

namespace App\Entity\Vitrine;

use App\Repository\Vitrine\ContenuRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=ContenuRepository::class)
 */
class Contenu
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
    private $description;

    /**
     * @ORM\ManyToOne(targetEntity=Section::class, inversedBy="contenus")
     */
    private $section;

    /**
     * @ORM\OneToMany(targetEntity=VitrineObject::class, mappedBy="contenu")
     */
    private $vitrineObjects;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $descriptionImage;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $titleImage;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $nomTemoin;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $posteTemoin;

    /**
     * @ORM\ManyToOne(targetEntity=TypeContenu::class, inversedBy="contenus")
     */
    private $typeContenu;

    /**
     * @ORM\Column(type="string", length=255 ,nullable=true)
     */
    private $titleContenu;

    /**
     * @ORM\Column(type="string", length=255 ,nullable=true)
     */
    private $descriptionContenu;

    /**
     * @ORM\Column(type="string", length=255 ,nullable=true)
     */
    private $lien;


    public function __construct()
    {
        $this->vitrineObjects = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
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

    public function getSection(): ?Section
    {
        return $this->section;
    }

    public function setSection(?Section $section): self
    {
        $this->section = $section;

        return $this;
    }

    /**
     * @return Collection<int, VitrineObject>
     */
    public function getVitrineObjects(): Collection
    {
        return $this->vitrineObjects;
    }

    public function addVitrineObject(VitrineObject $vitrineObject): self
    {
        if (!$this->vitrineObjects->contains($vitrineObject)) {
            $this->vitrineObjects[] = $vitrineObject;
            $vitrineObject->setContenu($this);
        }

        return $this;
    }

    public function removeVitrineObject(VitrineObject $vitrineObject): self
    {
        if ($this->vitrineObjects->removeElement($vitrineObject)) {
            // set the owning side to null (unless already changed)
            if ($vitrineObject->getContenu() === $this) {
                $vitrineObject->setContenu(null);
            }
        }

        return $this;
    }

    public function getDescriptionImage(): ?string
    {
        return $this->descriptionImage;
    }

    public function setDescriptionImage(string $descriptionImage): self
    {
        $this->descriptionImage = $descriptionImage;

        return $this;
    }

    public function getTitleImage(): ?string
    {
        return $this->titleImage;
    }

    public function setTitleImage(string $titleImage): self
    {
        $this->titleImage = $titleImage;

        return $this;
    }

    public function getNomTemoin(): ?string
    {
        return $this->nomTemoin;
    }

    public function setNomTemoin(?string $nomTemoin): self
    {
        $this->nomTemoin = $nomTemoin;

        return $this;
    }

    public function getPosteTemoin(): ?string
    {
        return $this->posteTemoin;
    }

    public function setPosteTemoin(?string $posteTemoin): self
    {
        $this->posteTemoin = $posteTemoin;

        return $this;
    }

    public function getTypeContenu(): ?TypeContenu
    {
        return $this->typeContenu;
    }

    public function setTypeContenu(?TypeContenu $typeContenu): self
    {
        $this->typeContenu = $typeContenu;

        return $this;
    }

    public function getTitleContenu(): ?string
    {
        return $this->titleContenu;
    }

    public function setTitleContenu(string $titleContenu): self
    {
        $this->titleContenu = $titleContenu;

        return $this;
    }

    public function getDescriptionContenu(): ?string
    {
        return $this->descriptionContenu;
    }

    public function setDescriptionContenu(string $descriptionContenu): self
    {
        $this->descriptionContenu = $descriptionContenu;

        return $this;
    }

    public function getLien(): ?string
    {
        return $this->lien;
    }

    public function setLien(string $lien): self
    {
        $this->lien = $lien;

        return $this;
    }
}
