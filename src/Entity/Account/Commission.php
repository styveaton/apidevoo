<?php

namespace App\Entity\Account;

use App\Repository\Account\CommissionRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=CommissionRepository::class)
 */
class Commission
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
    private $libelle;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $description;

    /**
     * @ORM\Column(type="float")
     */
    private $pourcentagePartage = 0;

    /**
     * @ORM\Column(type="float")
     */
    private $pourcentageParrain = 0;

    /**
     * @ORM\Column(type="float")
     */
    private $pourcentageParrain2 = 0;

    /**
     * @ORM\Column(type="boolean")
     */
    private $status;

    /**
     * @ORM\Column(type="datetime")
     */
    private $dateCreated;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $auteur;

    /**
     * @ORM\OneToMany(targetEntity=ListCommissionTransaction::class, mappedBy="commission")
     */
    private $listCommissionTransactions;

    public function __construct()
    {
        $this->listCommissionTransactions = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getLibelle(): ?string
    {
        return $this->libelle;
    }

    public function setLibelle(string $libelle): self
    {
        $this->libelle = $libelle;

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

    public function getPourcentagePartage(): ?float
    {
        return $this->pourcentagePartage;
    }

    public function setPourcentagePartage(float $pourcentagePartage): self
    {
        $this->pourcentagePartage = $pourcentagePartage;

        return $this;
    }

    public function getPourcentageParrain(): ?float
    {
        return $this->pourcentageParrain;
    }

    public function setPourcentageParrain(float $pourcentageParrain): self
    {
        $this->pourcentageParrain = $pourcentageParrain;

        return $this;
    }

    public function getPourcentageParrain2(): ?float
    {
        return $this->pourcentageParrain2;
    }

    public function setPourcentageParrain2(float $pourcentageParrain2): self
    {
        $this->pourcentageParrain2 = $pourcentageParrain2;

        return $this;
    }

    public function isStatus(): ?bool
    {
        return $this->status;
    }

    public function setStatus(bool $status): self
    {
        $this->status = $status;

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

    public function getAuteur(): ?string
    {
        return $this->auteur;
    }

    public function setAuteur(string $auteur): self
    {
        $this->auteur = $auteur;

        return $this;
    }

    /**
     * @return Collection<int, ListCommissionTransaction>
     */
    public function getListCommissionTransactions(): Collection
    {
        return $this->listCommissionTransactions;
    }

    public function addListCommissionTransaction(ListCommissionTransaction $listCommissionTransaction): self
    {
        if (!$this->listCommissionTransactions->contains($listCommissionTransaction)) {
            $this->listCommissionTransactions[] = $listCommissionTransaction;
            $listCommissionTransaction->setCommission($this);
        }

        return $this;
    }

    public function removeListCommissionTransaction(ListCommissionTransaction $listCommissionTransaction): self
    {
        if ($this->listCommissionTransactions->removeElement($listCommissionTransaction)) {
            // set the owning side to null (unless already changed)
            if ($listCommissionTransaction->getCommission() === $this) {
                $listCommissionTransaction->setCommission(null);
            }
        }

        return $this;
    }
}
