<?php

namespace App\Entity\Account;

use App\Repository\Account\TransactionCompteRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use ApiPlatform\Core\Annotation\ApiResource;
use Symfony\Component\Serializer\Annotation\Groups;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\BooleanFilter;

use ApiPlatform\Core\Annotation\ApiFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\SearchFilter;

/**
 * @ORM\Entity(repositoryClass=TransactionCompteRepository::class)
 * @ApiResource(
 *   normalizationContext={"groups"={"transactionCompte:read"}},
 *   denormalizationContext={"groups"={"transactionCompte:write"}}
 * )
 * 
 * @ApiFilter(
 *    SearchFilter::class, 
 *    properties={ 
 *      "typeTransaction.id": "exact",
 * "clientId":"exact"
 *    
 * }
 *)
 
 * @ApiFilter(
 *     BooleanFilter::class,
 *     properties={
 *     
 *      "status": "exact"
 *     }
 * )
 */
class TransactionCompte
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     * @Groups({"transactionCompte:read"})
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Groups({"transactionCompte:read","transactionCompte:write"})
     */
    private $description;

    /**
     * @ORM\Column(type="datetime")
     * @Groups({"transactionCompte:read","transactionCompte:write"})
     */
    private $dateCreate;

    /**
     * @ORM\Column(type="boolean")
     * @Groups({"transactionCompte:read","transactionCompte:write"})
     */
    private $status;

    /**
     * @ORM\Column(type="integer")
     * @Groups({"transactionCompte:read","transactionCompte:write"})
     */
    private $clientId;
    /**
     * @Groups({"transactionCompte:read","transactionCompte:write"})
     * @ORM\Column(type="integer")
     */
    private $montant;

    /**
     * @Groups({"transactionCompte:read","transactionCompte:write"})
     * @ORM\ManyToOne(targetEntity=Compte::class, inversedBy="transactionComptes")
     */
    private $emetteur;

    /**
     * @Groups({"transactionCompte:read","transactionCompte:write"})
     * @ORM\ManyToOne(targetEntity=Compte::class, inversedBy="transactionComptes")
     */
    private $recepteur;

    /**
     * @Groups({"transactionCompte:read","transactionCompte:write"})
     * @ORM\ManyToOne(targetEntity=TypeTransaction::class, inversedBy="transactionComptes")
     */
    private $typeTransaction;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Groups({"transactionCompte:read","transactionCompte:write"})
     * 
     */
    private $token;

    /**
     * @ORM\Column(type="string", nullable=true)
    
     * @Groups({"transactionCompte:read","transactionCompte:write"})
     */
    private $transactionId;


    /**
     * @ORM\Column(type="float")
     */
    private $montantPartage = 0;

    /**
     * @ORM\Column(type="float")
     */
    private $montantParrain = 0;

    /**
     * @ORM\Column(type="float")
     */
    private $montantParrain2 = 0;

    /**
     * @ORM\OneToMany(targetEntity=ListCommissionTransaction::class, mappedBy="transaction")
     */
    private $listCommissionTransactions;

    /**
     * @ORM\ManyToOne(targetEntity=ModePaiement::class, inversedBy="transactionComptes")
     */
    private $modePaiement;



    public function __construct()
    {

        $this->dateCreate = new \DateTime();
        $this->status = false;

        $this->listCommissionTransactions = new ArrayCollection();
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
    public function getMontant(): ?int
    {
        return $this->montant;
    }

    public function setMontant(int $montant): self
    {
        $this->montant = $montant;

        return $this;
    }

    public function getEmetteur(): ?Compte
    {
        return $this->emetteur;
    }

    public function setEmetteur(?Compte $emetteur): self
    {
        $this->emetteur = $emetteur;

        return $this;
    }

    public function getRecepteur(): ?Compte
    {
        return $this->recepteur;
    }

    public function setRecepteur(?Compte $recepteur): self
    {
        $this->recepteur = $recepteur;

        return $this;
    }

    public function getTypeTransaction(): ?TypeTransaction
    {
        return $this->typeTransaction;
    }

    public function setTypeTransaction(?TypeTransaction $typeTransaction): self
    {
        $this->typeTransaction = $typeTransaction;

        return $this;
    }

    public function getToken(): ?string
    {
        return $this->token;
    }

    public function setToken(string $token): self
    {
        $this->token = $token;

        return $this;
    }


    public function getTransactionId(): ?string
    {
        return $this->transactionId;
    }

    public function setTransactionId(string $transactionId): self
    {
        $this->transactionId = $transactionId;

        return $this;
    }

    public function getMontantPartage(): ?float
    {
        return $this->montantPartage;
    }

    public function setMontantPartage(float $montantPartage): self
    {
        $this->montantPartage = $montantPartage;

        return $this;
    }

    public function getMontantParrain(): ?float
    {
        return $this->montantParrain;
    }

    public function setMontantParrain(float $montantParrain): self
    {
        $this->montantParrain = $montantParrain;

        return $this;
    }

    public function getMontantParrain2(): ?float
    {
        return $this->montantParrain2;
    }

    public function setMontantParrain2(float $montantParrain2): self
    {
        $this->montantParrain2 = $montantParrain2;

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
            $listCommissionTransaction->setTransaction($this);
        }

        return $this;
    }

    public function removeListCommissionTransaction(ListCommissionTransaction $listCommissionTransaction): self
    {
        if ($this->listCommissionTransactions->removeElement($listCommissionTransaction)) {
            // set the owning side to null (unless already changed)
            if ($listCommissionTransaction->getTransaction() === $this) {
                $listCommissionTransaction->setTransaction(null);
            }
        }

        return $this;
    }

    public function getModePaiement(): ?ModePaiement
    {
        return $this->modePaiement;
    }

    public function setModePaiement(?ModePaiement $modePaiement): self
    {
        $this->modePaiement = $modePaiement;

        return $this;
    }
}
