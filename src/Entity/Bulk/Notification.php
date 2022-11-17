<?php

namespace App\Entity\Bulk;

use App\Repository\Bulk\NotificationRepository;
use Doctrine\ORM\Mapping as ORM;

use ApiPlatform\Core\Annotation\ApiFilter;
use ApiPlatform\Core\Annotation\ApiResource;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\SearchFilter;
use DateTime;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints\Date;

/**
 * @ApiResource()
 * @ORM\Entity(repositoryClass=NotificationRepository::class)
 */
class Notification
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="boolean")
     */
    private $status = false;

    /**
     * @ORM\Column(type="datetime")
     */
    private $dateCreated;

    /**
     * @ORM\ManyToOne(targetEntity=TypeNotification::class, inversedBy="notifications")
     */
    private $typeNotification;

    /**
     * @ORM\Column(type="integer")
     */
    private $clientId;

    /**
     * @ORM\OneToMany(targetEntity=ListNotificationModel::class, mappedBy="notification")
     */
    private $listNotificationModels;

    /**
     * @ORM\OneToMany(targetEntity=ListNotificationContact::class, mappedBy="notification")
     */
    private $listNotificationContacts;

    /**
     * @ORM\Column(type="string", length=11)
     */
    private $senderId;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $message;

    /**
     * @ORM\Column(type="integer")
     */
    private $frequence = 0;


    public function __construct()
    {
        $this->dateCreated = new DateTime();
        $this->listNotificationModels = new ArrayCollection();
        $this->listNotificationContacts = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
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

    public function getTypeNotification(): ?TypeNotification
    {
        return $this->typeNotification;
    }

    public function setTypeNotification(?TypeNotification $typeNotification): self
    {
        $this->typeNotification = $typeNotification;

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

    /**
     * @return Collection<int, ListNotificationModel>
     */
    public function getListNotificationModels(): Collection
    {
        return $this->listNotificationModels;
    }

    public function addListNotificationModel(ListNotificationModel $listNotificationModel): self
    {
        if (!$this->listNotificationModels->contains($listNotificationModel)) {
            $this->listNotificationModels[] = $listNotificationModel;
            $listNotificationModel->setNotification($this);
        }

        return $this;
    }

    public function removeListNotificationModel(ListNotificationModel $listNotificationModel): self
    {
        if ($this->listNotificationModels->removeElement($listNotificationModel)) {
            // set the owning side to null (unless already changed)
            if ($listNotificationModel->getNotification() === $this) {
                $listNotificationModel->setNotification(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, ListNotificationContact>
     */
    public function getListNotificationContacts(): Collection
    {
        return $this->listNotificationContacts;
    }

    public function addListNotificationContact(ListNotificationContact $listNotificationContact): self
    {
        if (!$this->listNotificationContacts->contains($listNotificationContact)) {
            $this->listNotificationContacts[] = $listNotificationContact;
            $listNotificationContact->setNotification($this);
        }

        return $this;
    }

    public function removeListNotificationContact(ListNotificationContact $listNotificationContact): self
    {
        if ($this->listNotificationContacts->removeElement($listNotificationContact)) {
            // set the owning side to null (unless already changed)
            if ($listNotificationContact->getNotification() === $this) {
                $listNotificationContact->setNotification(null);
            }
        }

        return $this;
    }

    public function getSenderId(): ?string
    {
        return $this->senderId;
    }

    public function setSenderId(string $senderId): self
    {
        $this->senderId = $senderId;

        return $this;
    }

    public function getMessage(): ?string
    {
        return $this->message;
    }

    public function setMessage(string $message): self
    {
        $this->message = $message;

        return $this;
    }

    public function getFrequence(): ?int
    {
        return $this->frequence;
    }

    public function setFrequence(int $frequence): self
    {
        $this->frequence = $frequence;

        return $this;
    }
}
