<?php

namespace App\Entity\Account;

use App\Repository\Account\ListCommissionTransactionRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=ListCommissionTransactionRepository::class)
 */
class ListCommissionTransaction
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="datetime")
     */
    private $date;

    /**
     * @ORM\ManyToOne(targetEntity=TransactionCompte::class, inversedBy="listCommissionTransactions")
     */
    private $transaction;

    /**
     * @ORM\ManyToOne(targetEntity=Commission::class, inversedBy="listCommissionTransactions")
     */
    private $commission;


    public function __construct()
    {

        $this->date = new \DateTime();
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

    public function getTransaction(): ?TransactionCompte
    {
        return $this->transaction;
    }

    public function setTransaction(?TransactionCompte $transaction): self
    {
        $this->transaction = $transaction;

        return $this;
    }

    public function getCommission(): ?Commission
    {
        return $this->commission;
    }

    public function setCommission(?Commission $commission): self
    {
        $this->commission = $commission;

        return $this;
    }
}
