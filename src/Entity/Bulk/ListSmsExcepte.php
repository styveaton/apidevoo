<?php

namespace App\Entity\Bulk;

use App\Repository\Bulk\ListSmsExcepteRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=ListSmsExcepteRepository::class)
 */
class ListSmsExcepte
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
    private $dateCreated;

    /**
     * @ORM\ManyToOne(targetEntity=Sms::class, inversedBy="listSmsExceptes")
     */
    private $sms;

    /**
     * @ORM\ManyToOne(targetEntity=Exception::class, inversedBy="listSmsExceptes")
     */
    private $exception;

    public function getId(): ?int
    {
        return $this->id;
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

    public function getSms(): ?Sms
    {
        return $this->sms;
    }

    public function setSms(?Sms $sms): self
    {
        $this->sms = $sms;

        return $this;
    }

    public function getException(): ?Exception
    {
        return $this->exception;
    }

    public function setException(?Exception $exception): self
    {
        $this->exception = $exception;

        return $this;
    }
}
