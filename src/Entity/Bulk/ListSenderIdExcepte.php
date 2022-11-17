<?php

namespace App\Entity\Bulk;

use App\Repository\Bulk\ListSenderIdExcepteRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=ListSenderIdExcepteRepository::class)
 */
class ListSenderIdExcepte
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
     * @ORM\ManyToOne(targetEntity=Exception::class, inversedBy="listSenderIdExceptes")
     */
    private $exception;

    /**
     * @ORM\Column(type="text")
     *  
     */
    private $senderId;

    public function __construct()
    {
        $this->dateCreated = new \DateTime();
    }
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

    public function getException(): ?Exception
    {
        return $this->exception;
    }

    public function setException(?Exception $exception): self
    {
        $this->exception = $exception;

        return $this;
    }

    public function getSenderId(): ?string
    {
        return $this->senderId;
    }

    public function setSenderId(?string $senderId): self
    {
        $this->senderId = $senderId;

        return $this;
    }
}
