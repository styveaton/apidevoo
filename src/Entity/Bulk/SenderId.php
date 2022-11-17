<?php

namespace App\Entity\Bulk;

use ApiPlatform\Core\Annotation\ApiFilter;
use ApiPlatform\Core\Annotation\ApiResource;
use App\Repository\Bulk\SenderIdRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\SearchFilter;

/**
 * @ApiResource(
 *     normalizationContext={
 *          "groups"={
 *              "read:senderid"
 *          }
 *     },
 *     collectionOperations={
 *          "get"={
 *             
 *          },
 *          "post"={
 *              "denormalization_context"={
 *                  "groups"={
 *                      "create:senderid"
 *                  }
 *              },
 *              "security"="is_granted('IS_AUTHENTICATED_FULLY')"
 *          }
 *     },
 *     itemOperations={
 *          "get"={
 *              "security"="is_granted('IS_AUTHENTICATED_FULLY')"
 *          },
 *          "patch"={
 *              "security"="is_granted('IS_AUTHENTICATED_FULLY')"
 *          },
 *          "delete"={
 *              "security"="is_granted('ROLE_SMS_DELETE')"
 *          },
 *     }
 * )
 *
 * @ApiFilter(
 *     SearchFilter::class,
 *     properties={
 *      "id": "exact",
 *      "senderId":"exact",
 *      "clientId":"exact",
 *     }
 * )
 *
 * @ORM\Entity(repositoryClass=SenderIdRepository::class)
 */
class SenderId
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     * @Groups({"read:senderid","read:listsmslotsenvoye","read:sms"})
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=11 )
     * @Groups({"read:senderid","read:sms", "create:senderid","read:listsmslotsenvoye"})
     */
    private $senderId;

    /**
     * @ORM\Column(type="string", length=255)
     * @Groups({"read:senderid", "create:senderid"})
     */
    private $description;

    /**
     * @ORM\Column(type="boolean")
     * @Groups({"read:senderid", "create:senderid"})
     */
    private $status = true;

    /**
     * @ORM\Column(type="datetime")
     * @Groups({"read:senderid"})
     */
    private $dateCreated;

    /**
     * @ORM\Column(type="integer", nullable=true)
     * @Groups({"read:senderid", "create:senderid"})
     */
    private $clientId;

    /**
     * @ORM\OneToMany(targetEntity=Sms::class, mappedBy="senderId")
     */
    private $sms;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $projetId;

    
 

    public function __construct()
    {
        $this->sms = new ArrayCollection();
        $this->dateCreated = new \DateTime(); 
    }

    public function getId(): ?int
    {
        return $this->id;
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

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): self
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

    public function getDateCreated(): ?\DateTimeInterface
    {
        return $this->dateCreated;
    }

    public function setDateCreated(\DateTimeInterface $dateCreated): self
    {
        $this->dateCreated = $dateCreated;

        return $this;
    }

    public function getClientId(): ?int
    {
        return $this->clientId;
    }

    public function setClientId(?int $clientId): self
    {
        $this->clientId = $clientId;

        return $this;
    }

    /**
     * @return Collection<int, Sms>
     */
    public function getSms(): Collection
    {
        return $this->sms;
    }

    public function addSms(Sms $sms): self
    {
        if (!$this->sms->contains($sms)) {
            $this->sms[] = $sms;
            $sms->setSenderId($this);
        }

        return $this;
    }

    public function removeSms(Sms $sms): self
    {
        if ($this->sms->removeElement($sms)) {
            // set the owning side to null (unless already changed)
            if ($sms->getSenderId() === $this) {
                $sms->setSenderId(null);
            }
        }

        return $this;
    }

    public function getProjetId(): ?int
    {
        return $this->projetId;
    }

    public function setProjetId(int $projetId): self
    {
        $this->projetId = $projetId;

        return $this;
    }

    
}
