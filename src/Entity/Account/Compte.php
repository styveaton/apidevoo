<?php

namespace App\Entity\Account;

use App\Repository\Account\CompteRepository;
use Doctrine\ORM\Mapping as ORM;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use ApiPlatform\Core\Annotation\ApiResource;
use Symfony\Component\Serializer\Annotation\Groups;

use ApiPlatform\Core\Annotation\ApiFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\SearchFilter;

/**
 * @ORM\Entity(repositoryClass=CompteRepository::class)
 *@ApiResource( 
 *   normalizationContext={"groups"={"compte:read"}},
 *   denormalizationContext={"groups"={"compte:write"}}
 * )
 * 
 * @ApiFilter(
 *    SearchFilter::class, 
 *    properties={ 
 *      "clientId": "exact",
 *      "typeCompte":"exact"
 *    
 * }
 *)
 */
class Compte
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     * @Groups({"compte:read"})
     */
    private $id;

    /**
     * @ORM\Column(type="float")
     * @Groups({"compte:read","compte:write"})
     * 
     */
    private $solde = '0';

    /**
     * @ORM\Column(type="datetime")
     * @Groups({"compte:read","compte:write"})
     */
    private $dateCreate;



    /**
     * @ORM\Column(type="boolean")
     * @Groups({"compte:read","compte:write"})
     */
    private $status;


    /**
     * @ORM\Column(type="integer")
     * @Groups({"compte:read","compte:write"})
     */
    private $clientId;

    /**
     * @ORM\ManyToOne(targetEntity=TypeCompte::class, inversedBy="comptes")
     */
    private $typeCompte;

    /**
     * @ORM\OneToMany(targetEntity=TransactionCompte::class, mappedBy="emetteru")
     */
    private $transactionComptes;


    public function __construct()
    {
        $this->transactionComptes = new ArrayCollection();
        $this->dateCreate = new \DateTime();
        $this->status = false;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getSolde(): ?int
    {
        return $this->solde;
    }

    public function setSolde(int $solde): self
    {
        $this->solde = $solde;

        return $this;
    }

    public function getDateCreate(): ?\DateTimeInterface
    {
        return $this->dateCreate;
    }

    public function setDateCreate(\DateTimeInterface $dateCreate): self
    {
        $this->dateCreate = $dateCreate;

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

    public function getClientId(): ?int
    {
        return $this->clientId;
    }

    public function setClientId(int $clientId): self
    {
        $this->clientId = $clientId;

        return $this;
    }

    public function getTypeCompte(): ?TypeCompte
    {
        return $this->typeCompte;
    }

    public function setTypeCompte(?TypeCompte $typeCompte): self
    {
        $this->typeCompte = $typeCompte;

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
            $transactionCompte->setEmetteur($this);
        }

        return $this;
    }

    public function removeTransactionCompte(TransactionCompte $transactionCompte): self
    {
        if ($this->transactionComptes->removeElement($transactionCompte)) {
            // set the owning side to null (unless already changed)
            if ($transactionCompte->getEmetteur() === $this) {
                $transactionCompte->setEmetteur(null);
            }
        }

        return $this;
    }
}
