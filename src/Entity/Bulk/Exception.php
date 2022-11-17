<?php

namespace App\Entity\Bulk;

use App\Repository\Bulk\ExceptionRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=ExceptionRepository::class)
 */
class Exception
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
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $text;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $description;

    /**
     * @ORM\OneToMany(targetEntity=ListSmsExcepte::class, mappedBy="exception")
     */
    private $listSmsExceptes;

    /**
     * @ORM\OneToMany(targetEntity=ListContactExcepte::class, mappedBy="exception")
     */
    private $listContactExcepte;

    /**
     * @ORM\OneToMany(targetEntity=ListSenderIdExcepte::class, mappedBy="exception")
     */
    private $listSenderIdExceptes;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $senderId;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $contact;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $codePhone;

    /**
     * @ORM\Column(type="boolean")
     */
    private $status = 1;



    public function __construct()
    {
        $this->dateCreated = new \DateTime();

        $this->listSmsExceptes = new ArrayCollection();
        $this->listContactExcepte = new ArrayCollection();
        $this->listSenderIdExceptes = new ArrayCollection();
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

    public function getText(): ?string
    {
        return $this->text;
    }

    public function setText(string $text): self
    {
        $this->text = $text;

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

    /**
     * @return Collection<int, ListSmsExcepte>
     */
    public function getListSmsExceptes(): Collection
    {
        return $this->listSmsExceptes;
    }

    public function addListSmsExcepte(ListSmsExcepte $listSmsExcepte): self
    {
        if (!$this->listSmsExceptes->contains($listSmsExcepte)) {
            $this->listSmsExceptes[] = $listSmsExcepte;
            $listSmsExcepte->setException($this);
        }

        return $this;
    }

    public function removeListSmsExcepte(ListSmsExcepte $listSmsExcepte): self
    {
        if ($this->listSmsExceptes->removeElement($listSmsExcepte)) {
            // set the owning side to null (unless already changed)
            if ($listSmsExcepte->getException() === $this) {
                $listSmsExcepte->setException(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, ListContactExcepte>
     */
    public function getListContactExcepte(): Collection
    {
        return $this->listContactExcepte;
    }

    public function addListContactExcepte(ListContactExcepte $listContactExcepte): self
    {
        if (!$this->listContactExcepte->contains($listContactExcepte)) {
            $this->listContactExcepte[] = $listContactExcepte;
            $listContactExcepte->setException($this);
        }

        return $this;
    }

    public function removeListContactExcepte(ListContactExcepte $listContactExcepte): self
    {
        if ($this->listContactExcepte->removeElement($listContactExcepte)) {
            // set the owning side to null (unless already changed)
            if ($listContactExcepte->getException() === $this) {
                $listContactExcepte->setException(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, ListSenderIdExcepte>
     */
    public function getListSenderIdExceptes(): Collection
    {
        return $this->listSenderIdExceptes;
    }

    public function addListSenderIdExcepte(ListSenderIdExcepte $listSenderIdExcepte): self
    {
        if (!$this->listSenderIdExceptes->contains($listSenderIdExcepte)) {
            $this->listSenderIdExceptes[] = $listSenderIdExcepte;
            $listSenderIdExcepte->setException($this);
        }

        return $this;
    }

    public function removeListSenderIdExcepte(ListSenderIdExcepte $listSenderIdExcepte): self
    {
        if ($this->listSenderIdExceptes->removeElement($listSenderIdExcepte)) {
            // set the owning side to null (unless already changed)
            if ($listSenderIdExcepte->getException() === $this) {
                $listSenderIdExcepte->setException(null);
            }
        }

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

    public function getContact(): ?string
    {
        return $this->contact;
    }

    public function setContact(?string $contact): self
    {
        $this->contact = $contact;

        return $this;
    }

    public function getCodePhone(): ?int
    {
        return $this->codePhone;
    }

    public function setCodePhone(int $codePhone): self
    {
        $this->codePhone = $codePhone;

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
}
