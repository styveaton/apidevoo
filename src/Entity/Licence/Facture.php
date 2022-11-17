<?php

namespace App\Entity\Licence;

use App\Repository\Licence\FactureRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use ApiPlatform\Core\Annotation\ApiResource;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity(repositoryClass=FactureRepository::class)
 * @ApiResource(
 *      itemOperations={"get", "patch", "delete"},
 *      normalizationContext={"groups"={"facture:read"}},
 *      denormalizationContext={"groups"={"facture:write"}}
 * )
 */
class Facture
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     * @Groups({"facture:read"})
     */
    private $id;

    /**
     * @ORM\Column(type="datetime")
     * @Groups({"lsa:read","facture:read", "facture:write"})
     */
    private $date;

    /**
     * @ORM\Column(type="boolean")
     * @Groups({"lsa:read","facture:read", "facture:write"})
     */
    private $status;

    /**
     * @ORM\Column(type="integer")
     * @Groups({"lsa:read","facture:read", "facture:write"})
     */
    private $licence;

    /**
     * @ORM\Column(type="integer")
     * @Groups({"lsa:read","facture:read", "facture:write"})
     */
    private $clientId;

    /**
     * @ORM\OneToMany(targetEntity=ListSMSAchette::class, mappedBy="Facture")
     */
    private $listSMSAchettes;

    /**
     * @ORM\Column(type="float")
     * @Groups({"lsa:read","facture:read","facture:write"})
     */
    private $montant;


    public function __construct()
    {

        $this->date = new \DateTime();
        $this->status = false;
        $this->listSMSAchettes = new ArrayCollection();
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

    public function getLicence():int
    {
        return $this->licence;
    }

    public function setLicence(int $Licence): self
    {
        $this->licence = $Licence;

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

    /**
     * @return Collection<int, ListSMSAchette>
     */
    public function getListSMSAchettes(): Collection
    {
        return $this->listSMSAchettes;
    }

    public function addListSMSAchette(ListSMSAchette $listSMSAchette): self
    {
        if (!$this->listSMSAchettes->contains($listSMSAchette)) {
            $this->listSMSAchettes[] = $listSMSAchette;
            $listSMSAchette->setFacture($this);
        }

        return $this;
    }

    public function removeListSMSAchette(ListSMSAchette $listSMSAchette): self
    {
        if ($this->listSMSAchettes->removeElement($listSMSAchette)) {
            // set the owning side to null (unless already changed)
            if ($listSMSAchette->getFacture() === $this) {
                $listSMSAchette->setFacture(null);
            }
        }

        return $this;
    }

    public function getMontant(): ?float
    {
        return $this->montant;
    }

    public function setMontant(float $montant): self
    {
        $this->montant = $montant;

        return $this;
    }
}
