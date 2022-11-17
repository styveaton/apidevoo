<?php


namespace App\Entity\Vitrine;

use ApiPlatform\Core\Annotation\ApiResource;
use App\Repository\Vitrine\VitrineRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=VitrineRepository::class)
 * @ApiResource()
 */
class Vitrine
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255, unique =true)
     */
    private $nom;

    /**
     * @ORM\Column(type="string", length=255, nullable=true) 
     */
    private $description;

    /**
     * @ORM\Column(type="integer")
     */
    private $status = true;

    /**
     * @ORM\OneToMany(targetEntity=Preference::class, mappedBy="vitrine")
     */
    private $preferences;

    /**
     * @ORM\OneToMany(targetEntity=ListVitrineVisite::class, mappedBy="vitrine")
     */
    private $listVitrineVisites;

    /**
     * @ORM\OneToMany(targetEntity=Page::class, mappedBy="vitrine")
     */
    private $pages;

    /**
     * @ORM\ManyToOne(targetEntity=TypeVitrine::class, inversedBy="vitrines")
     */
    private $typeVitrine;

    /**
     * @ORM\Column(type="integer")
     */
    private $clientId;


    /**
     * @ORM\Column(type="datetime",nullable=true) 
     */
    private $dateCreated;

    /**
     * @ORM\Column(type="integer")
     */
    private $proprietaire;

    /**
     * @ORM\OneToMany(targetEntity=Section::class, mappedBy="vitrine")
     */
    private $sections;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $metaDescription;

    /**
     * @ORM\OneToMany(targetEntity=VitrineObject::class, mappedBy="Vitrine")
     */
    private $vitrineObjects;

    /**
     * @ORM\Column(type="string", length=255,nullable=true)
     */
    private $metaKey;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $titreSite;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $recordId1;
    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $recordId2;

    /**
     * @ORM\ManyToOne(targetEntity=Theme::class, inversedBy="vitrines")
     */
    private $theme;



    public function __construct()
    {
        $this->listPreferenceVitrines = new ArrayCollection();
        $this->preferences = new ArrayCollection();
        $this->listVitrineVisites = new ArrayCollection();
        $this->pages = new ArrayCollection();
        $this->dateCreated = new \DateTime();
        $this->sections = new ArrayCollection();
        $this->vitrineObjects = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getRecordId1(): ?string
    {
        return $this->recordId1;
    }

    public function setRecordId1(string $recordId): self
    {
        $this->recordId1 = $recordId;

        return $this;
    }
    public function getRecordId2(): ?string
    {
        return $this->recordId2;
    }

    public function setRecordId2(string $recordId): self
    {
        $this->recordId2 = $recordId;

        return $this;
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
     * @return Collection<int, preference>
     */
    public function getPreferences(): Collection
    {
        return $this->preferences;
    }

    public function addPreference(preference $preference): self
    {
        if (!$this->preferences->contains($preference)) {
            $this->preferences[] = $preference;
            $preference->setVitrine($this);
        }

        return $this;
    }

    public function removePreference(preference $preference): self
    {
        if ($this->preferences->removeElement($preference)) {
            // set the owning side to null (unless already changed)
            if ($preference->getVitrine() === $this) {
                $preference->setVitrine(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, ListVitrineVisite>
     */
    public function getListVitrineVisites(): Collection
    {
        return $this->listVitrineVisites;
    }

    public function addListVitrineVisite(ListVitrineVisite $listVitrineVisite): self
    {
        if (!$this->listVitrineVisites->contains($listVitrineVisite)) {
            $this->listVitrineVisites[] = $listVitrineVisite;
            $listVitrineVisite->setVitrine($this);
        }

        return $this;
    }

    public function removeListVitrineVisite(ListVitrineVisite $listVitrineVisite): self
    {
        if ($this->listVitrineVisites->removeElement($listVitrineVisite)) {
            // set the owning side to null (unless already changed)
            if ($listVitrineVisite->getVitrine() === $this) {
                $listVitrineVisite->setVitrine(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Page>
     */
    public function getPages(): Collection
    {
        return $this->pages;
    }

    public function addPage(Page $page): self
    {
        if (!$this->pages->contains($page)) {
            $this->pages[] = $page;
            $page->setVitrine($this);
        }

        return $this;
    }

    public function removePage(Page $page): self
    {
        if ($this->pages->removeElement($page)) {
            // set the owning side to null (unless already changed)
            if ($page->getVitrine() === $this) {
                $page->setVitrine(null);
            }
        }

        return $this;
    }

    public function getTypeVitrine(): ?TypeVitrine
    {
        return $this->typeVitrine;
    }

    public function setTypeVitrine(?TypeVitrine $typeVitrine): self
    {
        $this->typeVitrine = $typeVitrine;

        return $this;
    }

    public function getClientId(): ?int
    {
        return $this->clientId;
    }

    public function setClientId(int $clientId): self
    {
        $this->clientId = $clientId;

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

    public function getProprietaire(): ?int
    {
        return $this->proprietaire;
    }

    public function setProprietaire(int $proprietaire): self
    {
        $this->proprietaire = $proprietaire;

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
            $section->setVitrine($this);
        }

        return $this;
    }

    public function removeSection(Section $section): self
    {
        if ($this->sections->removeElement($section)) {
            // set the owning side to null (unless already changed)
            if ($section->getVitrine() === $this) {
                $section->setVitrine(null);
            }
        }

        return $this;
    }

    public function getMetaDescription(): ?string
    {
        return $this->metaDescription;
    }

    public function setMetaDescription(?string $metaDescription): self
    {
        $this->metaDescription = $metaDescription;

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
            $vitrineObject->setVitrine($this);
        }

        return $this;
    }

    public function removeVitrineObject(VitrineObject $vitrineObject): self
    {
        if ($this->vitrineObjects->removeElement($vitrineObject)) {
            // set the owning side to null (unless already changed)
            if ($vitrineObject->getVitrine() === $this) {
                $vitrineObject->setVitrine(null);
            }
        }

        return $this;
    }

    public function getMetaKey(): ?string
    {
        return $this->metaKey;
    }

    public function setMetaKey(string $metaKey): self
    {
        $this->metaKey = $metaKey;

        return $this;
    }

    public function getTitreSite(): ?string
    {
        return $this->titreSite;
    }

    public function setTitreSite(string $titreSite): self
    {
        $this->titreSite = $titreSite;

        return $this;
    }

    public function getTheme(): ?Theme
    {
        return $this->theme;
    }

    public function setTheme(?Theme $theme): self
    {
        $this->theme = $theme;

        return $this;
    }
}
