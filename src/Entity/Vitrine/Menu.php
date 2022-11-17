<?php


namespace App\Entity\Vitrine;

use ApiPlatform\Core\Annotation\ApiResource;
use App\Repository\Vitrine\MenuRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

use Symfony\Component\Serializer\Annotation\Groups;
use ApiPlatform\Core\Annotation\ApiFilter;
use ApiPlatform\Core\Annotation\ApiProperty;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\SearchFilter;

/**
 * @ORM\Entity(repositoryClass=MenuRepository::class)
 * @ApiResource(
 * collectionOperations = { 
 * "get" = {
 * "normalization_context"={
 *                  "groups"={
 *                      "read:menu"
 *                  }
 *              },
 * 
 * },
 * "post"={
 *              "denormalization_context"={
 *                  "groups"={
 *                      "create:menu"
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
 *                      "write:menu"
 *              },
 *             },
 * }
 * })
 * 
 * @ApiFilter(
 *    SearchFilter::class, 
 *    properties={ 
 *      "id": "exact",
 *      "section": "exact"
 * })
 */
class Menu
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     * @Groups({"read:menu"})
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     * @Groups({"read:menu"})
     * 
     */
    private $nom;

    /**
     * @Groups({"read:menu"})
     * @ORM\Column(type="string", length=255)
     */
    private $description;

    /**
     * @Groups({"read:menu"})
     * @ORM\OneToMany(targetEntity=Lien::class, mappedBy="menu")
     */
    private $liens;

    /**
     * @ORM\ManyToOne(targetEntity=Section::class, inversedBy="menus")* @Groups({"read:menu"})
     */
    private $section;


    public function __construct()
    {
        $this->liens = new ArrayCollection();
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

    /**
     * @return Collection<int, Lien>
     */
    public function getLiens(): Collection
    {
        return $this->liens;
    }

    public function addLien(Lien $lien): self
    {
        if (!$this->liens->contains($lien)) {
            $this->liens[] = $lien;
            $lien->setMenu($this);
        }

        return $this;
    }

    public function removeLien(Lien $lien): self
    {
        if ($this->liens->removeElement($lien)) {
            // set the owning side to null (unless already changed)
            if ($lien->getMenu() === $this) {
                $lien->setMenu(null);
            }
        }

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
}
