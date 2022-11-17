<?php


namespace App\Entity\Vitrine;

use ApiPlatform\Core\Annotation\ApiResource;
use App\Entity\Vitrine\Vitrine;
use App\Repository\Vitrine\PageRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=PageRepository::class)
 * @ApiResource()
 */
class Page
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
    private $nom;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $description;

    /**
     * @ORM\Column(type="integer")
     */
    private $status;

    /**
     * @ORM\OneToMany(targetEntity=MetaDescription::class, mappedBy="page")
     */
    private $metaDescriptions;

    /**
     * @ORM\ManyToOne(targetEntity=Vitrine::class, inversedBy="pages")
     */
    private $vitrine;

    /**
     * @ORM\OneToMany(targetEntity=Section::class, mappedBy="page")
     */
    private $sections;

    public function __construct()
    {
        $this->metaDescriptions = new ArrayCollection();
        $this->sections = new ArrayCollection();
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

    public function getStatus(): ?int
    {
        return $this->status;
    }

    public function setStatus(int $status): self
    {
        $this->status = $status;

        return $this;
    }

    /**
     * @return Collection<int, MetaDescription>
     */
    public function getMetaDescriptions(): Collection
    {
        return $this->metaDescriptions;
    }

    public function addMetaDescription(MetaDescription $metaDescription): self
    {
        if (!$this->metaDescriptions->contains($metaDescription)) {
            $this->metaDescriptions[] = $metaDescription;
            $metaDescription->setPage($this);
        }

        return $this;
    }

    public function removeMetaDescription(MetaDescription $metaDescription): self
    {
        if ($this->metaDescriptions->removeElement($metaDescription)) {
            // set the owning side to null (unless already changed)
            if ($metaDescription->getPage() === $this) {
                $metaDescription->setPage(null);
            }
        }

        return $this;
    }

    public function getVitrine(): ?Vitrine
    {
        return $this->vitrine;
    }

    public function setVitrine(?Vitrine $vitrine): self
    {
        $this->vitrine = $vitrine;

        return $this;
    }

    /**
     * @return Collection<int, Section>
     */
    public function getSections(): Collection
    {
        return $this->sections;
    }

    public function addSection(Section $section): self
    {
        if (!$this->sections->contains($section)) {
            $this->sections[] = $section;
            $section->setPage($this);
        }

        return $this;
    }

    public function removeSection(Section $section): self
    {
        if ($this->sections->removeElement($section)) {
            // set the owning side to null (unless already changed)
            if ($section->getPage() === $this) {
                $section->setPage(null);
            }
        }

        return $this;
    }
}
