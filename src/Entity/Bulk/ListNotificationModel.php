<?php

namespace App\Entity\Bulk;

use App\Repository\Bulk\ListNotificationModelRepository;
use Doctrine\ORM\Mapping as ORM;

use ApiPlatform\Core\Annotation\ApiFilter;
use ApiPlatform\Core\Annotation\ApiResource;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\SearchFilter;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ApiResource()
 * @ORM\Entity(repositoryClass=ListNotificationModelRepository::class)
 */
class ListNotificationModel
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity=Notification::class, inversedBy="listNotificationModels")
     */
    private $notification;

    /**
     * @ORM\ManyToOne(targetEntity=ModelMessage::class, inversedBy="listNotificationModels")
     */
    private $modelMessage;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNotification(): ?Notification
    {
        return $this->notification;
    }

    public function setNotification(?Notification $notification): self
    {
        $this->notification = $notification;

        return $this;
    }

    public function getModelMessage(): ?ModelMessage
    {
        return $this->modelMessage;
    }

    public function setModelMessage(?ModelMessage $modelMessage): self
    {
        $this->modelMessage = $modelMessage;

        return $this;
    }
}
