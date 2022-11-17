<?php

namespace App\Entity\Licence;

use App\Repository\Licence\ListSMSAchetteRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use ApiPlatform\Core\Annotation\ApiResource;
use Symfony\Component\Serializer\Annotation\Groups;

use ApiPlatform\Core\Annotation\ApiFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\SearchFilter;

/**
 * @ORM\Entity(repositoryClass=ListSMSAchetteRepository::class)
 * @ApiResource(
 *   itemOperations={"get", "patch", "delete"},
 *   normalizationContext={"groups"={"lsa:read"}},
 *   denormalizationContext={"groups"={"lsa:write"}}
 * ),
 * @ApiFilter(
 *     SearchFilter::class,
 *     properties={
 *    "facture.clientId": "exact",
 * "facture.licence": "exact",
 * "routeId":"exact",
 *     }
 * )
 */
class ListSMSAchette
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     * @Groups("lsa:read")
     */
    private $id;

    /**
     * @ORM\Column(type="datetime")
     * @Groups({"lsa:read","licence:read"})
     */
    private $date;

    /**
     * @ORM\Column(type="boolean")
     * @Groups({"lsa:read"})
     */
    private $status;

    /**
     * @ORM\Column(type="integer", nullable=true)
     * @Groups({"lsa:read","lsa:write"})
     */
    private $quantite;

    /**
     * @ORM\Column(type="integer", nullable=true)
     * @Groups({"lsa:read", "lsa:write"})
     */
    private $routeId;

    /**
     * @ORM\ManyToOne(targetEntity=Facture::class, inversedBy="listSMSAchettes")
     * @Groups({"lsa:read", "lsa:write"})
     */
    private $facture;

    /**
     * @ORM\OneToMany(targetEntity=ListSmsManipule::class, mappedBy="emetteur")
     */
    private $listSmsManipules;

    public function __construct()
    {

        $this->date = new \DateTime();
        $this->status = false;
        $this->listSmsManipules = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
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

    public function getStatus(): ?bool
    {
        return $this->status;
    }

    public function setStatus(bool $status): self
    {
        $this->status = $status;

        return $this;
    }

    public function getQuantite(): ?int
    {
        return $this->quantite;
    }

    public function setQuantite(int $quantite): self
    {
        $this->quantite = $quantite;

        return $this;
    }

    public function getRouteId(): ?int
    {
        return $this->routeId;
    }

    public function setRouteId(int $routeId): self
    {
        $this->routeId = $routeId;

        return $this;
    }

    public function getFacture(): ?Facture
    {
        return $this->facture;
    }

    public function setFacture(?Facture $Facture): self
    {
        $this->facture = $Facture;

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
            $listSmsManipule->setEmetteur($this);
        }

        return $this;
    }

    public function removeListSmsManipule(ListSmsManipule $listSmsManipule): self
    {
        if ($this->listSmsManipules->removeElement($listSmsManipule)) {
            // set the owning side to null (unless already changed)
            if ($listSmsManipule->getEmetteur() === $this) {
                $listSmsManipule->setEmetteur(null);
            }
        }

        return $this;
    }
}
