<?php

namespace App\Entity\Licence;

use App\Repository\Licence\LicenceRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use ApiPlatform\Core\Annotation\ApiResource;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity(repositoryClass=LicenceRepository::class)
 * @ApiResource(
 *      itemOperations={"get", "patch", "delete"},
 *      normalizationContext={"groups"={"licence:read"}},
 *      denormalizationContext={"groups"={"licence:write"}}
 * )
 */
class Licence
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     * @Groups("licence:read", "facture:read", "lsitsmsmachette:read")
     */
    private $id;

    /**
     * @ORM\Column(type="datetime")
     * @Groups("licence:read", "licence:write")
     */
    private $date;

    /**
     * @ORM\Column(type="string", length=255)
     * @Groups("licence:read", "licence:write", "facture:read", "lsitsmsmachette:read")
     */
    private $nom;

    /**
     * @ORM\Column(type="time")
     * @Groups("licence:read", "licence:write")
     */
    private $duree;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Groups("licence:read", "licence:write")
     */
    private $description;

    /**
     * @ORM\Column(type="boolean")
     * @Groups("licence:read", "licence:write")
     */
    private $status;


    /**
     *  @ORM\Column(type="integer", length=255)
     * @ORM\JoinColumn(nullable=false)
     * @Groups("licence:read", "licence:write")
     */
    private $typeTransaction;


    /**
     * @ORM\OneToMany(targetEntity=Facture::class, mappedBy="Licence")
     */
    private $factures;

    public function __construct()
    {

        $this->factures = new ArrayCollection();
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

    public function getNom(): ?string
    {
        return $this->nom;
    }

    public function setNom(string $nom): self
    {
        $this->nom = $nom;

        return $this;
    }

    public function getDuree(): ?\DateTimeInterface
    {
        return $this->duree;
    }

    public function setDuree(\DateTimeInterface $duree): self
    {
        $this->duree = $duree;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;

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


    public function getTypeTransaction(): ?int
    {
        return $this->typeTransaction;
    }

    public function setTypeTransaction(int $typeTransaction): self
    {
        $this->typeTransaction = $typeTransaction;

        return $this;
    }

    /**
     * @return Collection<int, Facture>
     */
    public function getFactures(): Collection
    {
        return $this->factures;
    }

    public function addFacture(Facture $facture): self
    {
        if (!$this->factures->contains($facture)) {
            $this->factures[] = $facture;
            $facture->setLicence($this);
        }

        return $this;
    }

    public function removeFacture(Facture $facture): self
    {
        if ($this->factures->removeElement($facture)) {
            // set the owning side to null (unless already changed)
            if ($facture->getLicence() === $this) {
                $facture->setLicence(null);
            }
        }

        return $this;
    }
}
