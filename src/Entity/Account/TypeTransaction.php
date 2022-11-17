<?php

namespace App\Entity\Account;

use App\Repository\Account\TypeTransactionRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use ApiPlatform\Core\Annotation\ApiResource;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity(repositoryClass=TypeTransactionRepository::class)
 * @ApiResource(
 *   normalizationContext={"groups"={"typetransaction:read"}},
 *   denormalizationContext={"groups"={"typetransaction:write"}}
 * )
 */
class TypeTransaction
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     * @Groups({"typetransaction:read", "licence:read","transactionCompte:read"})
     */
    private $id;

    /**
     * @ORM\Column(type="datetime")
     * @Groups({"typetransaction:read"})
     */
    private $date;

    /**
     * @ORM\Column(type="string", length=50)
     * @Groups({"typetransaction:read", "licence:read"})
     */
    private $libelle;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Groups({"typetransaction:read"})
     */
    private $description;

    /**
     * @ORM\Column(type="boolean")
     * @Groups({"typetransaction:read"})
     */
    private $status;

    /**
     * @ORM\OneToMany(targetEntity=TransactionCompte::class, mappedBy="typeTransaction")
     */
    private $transactionComptes;

    public function __construct()
    {
        $this->transactionComptes = new ArrayCollection();
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
            $transactionCompte->setTypeTransaction($this);
        }

        return $this;
    }

    public function removeTransactionCompte(TransactionCompte $transactionCompte): self
    {
        if ($this->transactionComptes->removeElement($transactionCompte)) {
            // set the owning side to null (unless already changed)
            if ($transactionCompte->getTypeTransaction() === $this) {
                $transactionCompte->setTypeTransaction(null);
            }
        }

        return $this;
    }
}
