<?php

namespace App\Entity\Bulk;

use App\Repository\Bulk\ListContactExcepteRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=ListContactExcepteRepository::class)
 */
class ListContactExcepte
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
     * @ORM\ManyToOne(targetEntity=Exception::class, inversedBy="contact")
     */
    private $exception;

    /**
     * @ORM\Column(type="text")
     *  
     */
    private $contact;

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

    public function getContact(): ?string
    {
        return $this->contact;
    }

    public function setContact(?string $contact): self
    {
        $this->contact = $contact;

        return $this;
    }
}
