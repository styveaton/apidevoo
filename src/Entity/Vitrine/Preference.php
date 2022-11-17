<?php


namespace App\Entity\Vitrine;

use ApiPlatform\Core\Annotation\ApiResource;
use App\Entity\Vitrine\Vitrine;
use App\Repository\Vitrine\PreferenceRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=PreferenceRepository::class)
 * @ApiResource()
 */
class Preference
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
    private $langue;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $theme;

    /**
     * @ORM\ManyToOne(targetEntity=Vitrine::class, inversedBy="preferences")
     */
    private $vitrine;

    /**
     * @ORM\OneToMany(targetEntity=ListClientPreference::class, mappedBy="preference")
     */
    private $listClientPreferences;


    public function __construct()
    {
        $this->listPreferenceVitrines = new ArrayCollection();
        $this->listClientPreferences = new ArrayCollection();
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

    public function getLangue(): ?string
    {
        return $this->langue;
    }

    public function setLangue(string $langue): self
    {
        $this->langue = $langue;

        return $this;
    }

    public function getTheme(): ?string
    {
        return $this->theme;
    }

    public function setTheme(string $theme): self
    {
        $this->theme = $theme;

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
     * @return Collection<int, ListClientPreference>
     */
    public function getListClientPreferences(): Collection
    {
        return $this->listClientPreferences;
    }

    public function addListClientPreference(ListClientPreference $listClientPreference): self
    {
        if (!$this->listClientPreferences->contains($listClientPreference)) {
            $this->listClientPreferences[] = $listClientPreference;
            $listClientPreference->setPreference($this);
        }

        return $this;
    }

    public function removeListClientPreference(ListClientPreference $listClientPreference): self
    {
        if ($this->listClientPreferences->removeElement($listClientPreference)) {
            // set the owning side to null (unless already changed)
            if ($listClientPreference->getPreference() === $this) {
                $listClientPreference->setPreference(null);
            }
        }

        return $this;
    }
}
