<?php

namespace App\Entity\Bulk;

use App\Repository\Bulk\ModelMessageRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use ApiPlatform\Core\Annotation\ApiFilter;
use ApiPlatform\Core\Annotation\ApiResource;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\SearchFilter;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ApiResource()
 * @ORM\Entity(repositoryClass=ModelMessageRepository::class)
 */
class ModelMessage
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
    private $message;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $status;

    /**
     * @ORM\OneToMany(targetEntity=ListNotificationModel::class, mappedBy="modelMessage")
     */
    private $listNotificationModels;

    public function __construct()
    {
        $this->listNotificationModels = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
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

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(string $status): self
    {
        $this->status = $status;

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
            $listNotificationModel->setModelMessage($this);
        }

        return $this;
    }

    public function removeListNotificationModel(ListNotificationModel $listNotificationModel): self
    {
        if ($this->listNotificationModels->removeElement($listNotificationModel)) {
            // set the owning side to null (unless already changed)
            if ($listNotificationModel->getModelMessage() === $this) {
                $listNotificationModel->setModelMessage(null);
            }
        }

        return $this;
    }
}
