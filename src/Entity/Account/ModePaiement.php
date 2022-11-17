<?php

namespace App\Entity\Account;

use ApiPlatform\Core\Annotation\ApiResource;

use App\Repository\Account\ModePaiementRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 *  @ApiResource()
 * @ORM\Entity(repositoryClass=ModePaiementRepository::class)
 */
class ModePaiement
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
    private $siteId;

    /**
     * @ORM\OneToMany(targetEntity=TransactionCompte::class, mappedBy="modePaiement")
     */
    private $transactionComptes;


    public function __construct()
    {
        $this->listTransactionModePaiements = new ArrayCollection();
        $this->transactionComptes = new ArrayCollection();
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

    public function getSiteId(): ?string
    {
        return $this->siteId;
    }

    public function setSiteId(string $siteId): self
    {
        $this->siteId = $siteId;

        return $this;
    }

    /**
     * @return Collection<int, TransactionCompte>
     */
    public function getTransactionComptes(): Collection
    {
        return $this->transactionComptes;
    }

    public function addTransactionCompte(TransactionCompte $transactionCompte): self
    {
        if (!$this->transactionComptes->contains($transactionCompte)) {
            $this->transactionComptes[] = $transactionCompte;
            $transactionCompte->setModePaiement($this);
        }

        return $this;
    }

    public function removeTransactionCompte(TransactionCompte $transactionCompte): self
    {
        if ($this->transactionComptes->removeElement($transactionCompte)) {
            // set the owning side to null (unless already changed)
            if ($transactionCompte->getModePaiement() === $this) {
                $transactionCompte->setModePaiement(null);
            }
        }

        return $this;
    }
}
