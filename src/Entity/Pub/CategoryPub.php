<?php

namespace App\Entity\Pub;

use App\Repository\Pub\CategoryPubRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use ApiPlatform\Core\Annotation\ApiResource;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity(repositoryClass=CategoryPubRepository::class)
  @ApiResource(
 *     
 *      normalizationContext={"groups"={"category:read"}},
 *      denormalizationContext={"groups"={"category:write"}}
 * )
 */

class CategoryPub
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     * @Groups({"category:read"})
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     * @Groups({"category:read","category:write"})
     * 
     */
    private $title;


    /**
     * @ORM\ManyToOne(targetEntity=CategoryPub::class, inversedBy="categoryPubs")
     *  @Groups({"category:read"})
     */


    /**
     * @ORM\OneToMany(targetEntity=Publication::class, mappedBy="categoryPub")
     *  @Groups({"category:read"})
     */
    private $publications;

    /**
     *  @Groups({"category:read"})
     * @ORM\ManyToOne(targetEntity=CategoryPub::class, inversedBy="sousCategory")
     */
    private $categoryPub;

    /**
     *  @Groups({"category:read"})
     * @ORM\OneToMany(targetEntity=CategoryPub::class, mappedBy="categoryPub")
     */
    private $sousCategory;

   

    public function __construct()
    {
        $this->publications = new ArrayCollection();
        $this->sousCategory = new ArrayCollection();
      
    }
    public function getId(): ?int
    {
        return $this->id;
    }


    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;

        return $this;
    }

    /**
     * @return Collection<int, Publication>
     */
    public function getPublications(): Collection
    {
        return $this->publications;
    }

    public function addPublication(Publication $publication): self
    {
        if (!$this->publications->contains($publication)) {
            $this->publications[] = $publication;
            $publication->setCategoryPub($this);
        }

        return $this;
    }

    public function removePublication(Publication $publication): self
    {
        if ($this->publications->removeElement($publication)) {
            // set the owning side to null (unless already changed)
            if ($publication->getCategoryPub() === $this) {
                $publication->setCategoryPub(null);
            }
        }

        return $this;
    }

    public function getCategoryPub(): ?self
    {
        return $this->categoryPub;
    }

    public function setCategoryPub(?self $categoryPub): self
    {
        $this->categoryPub = $categoryPub;

        return $this;
    }

    /**
     * @return Collection<int, self>
     */
    public function getSousCategory(): Collection
    {
        return $this->sousCategory;
    }

    public function addSousCategory(self $sousCategory): self
    {
        if (!$this->sousCategory->contains($sousCategory)) {
            $this->sousCategory[] = $sousCategory;
            $sousCategory->setCategoryPub($this);
        }

        return $this;
    }

    public function removeSousCategory(self $sousCategory): self
    {
        if ($this->sousCategory->removeElement($sousCategory)) {
            // set the owning side to null (unless already changed)
            if ($sousCategory->getCategoryPub() === $this) {
                $sousCategory->setCategoryPub(null);
            }
        }

        return $this;
    } 

}
