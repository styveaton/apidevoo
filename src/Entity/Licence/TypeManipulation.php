<?php

namespace App\Entity\Licence;

use App\Repository\Licence\TypeManipulationRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use ApiPlatform\Core\Annotation\ApiResource;
use Symfony\Component\Serializer\Annotation\Groups;

use ApiPlatform\Core\Annotation\ApiFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\SearchFilter;



/**
 * @ORM\Entity(repositoryClass=TypeManipulationRepository::class)
 *    @ApiResource(
 *   itemOperations={"get", "patch", "delete"},
 *   normalizationContext={"groups"={"tm:read"}},
 *   denormalizationContext={"groups"={"tm:write"}}
 * ),
 * @ApiFilter(
 *     SearchFilter::class,
 *     properties={
 *    "clientId": "exact",
 * "emetteur.facture.clientId": "exact",
 *   "recepteur.facture.clientId": "exact",

 *     }
 * )
 */
class TypeManipulation
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     *  @Groups({"tm:read"})
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     *  @Groups({"tm:read"})
     */
    private $description;

    /**
     * @ORM\OneToMany(targetEntity=ListSmsManipule::class, mappedBy="typemanipulation")
     *  @Groups({"tm:read"})
     */
    private $listSmsManipules;

    public function __construct()
    {
        $this->listSmsManipules = new ArrayCollection();
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

    /**
     * @return Collection<int, ListSmsManipule>
     */
    public function getListSmsManipules(): Collection
    {
        return $this->listSmsManipules;
    }

    public function addListSmsManipule(ListSmsManipule $listSmsManipule): self
    {
        if (!$this->listSmsManipules->contains($listSmsManipule)) {
            $this->listSmsManipules[] = $listSmsManipule;
            $listSmsManipule->setTypemanipulation($this);
        }

        return $this;
    }

    public function removeListSmsManipule(ListSmsManipule $listSmsManipule): self
    {
        if ($this->listSmsManipules->removeElement($listSmsManipule)) {
            // set the owning side to null (unless already changed)
            if ($listSmsManipule->getTypemanipulation() === $this) {
                $listSmsManipule->setTypemanipulation(null);
            }
        }

        return $this;
    }
}
