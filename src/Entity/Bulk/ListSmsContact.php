<?php

namespace App\Entity\Bulk;

use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Core\Annotation\ApiFilter;
use ApiPlatform\Core\Annotation\ApiResource;
use App\Repository\Bulk\ListSmsContactRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ApiResource(
 *     normalizationContext={
 *          "groups"={
 *              "read:listsmscontact"
 *          }
 *     },
 *     collectionOperations={
 *          "get"={
 *             
 *          },
 *          "post"={
 *              "denormalization_context"={
 *                  "groups"={
 *                      "create:listsmscontact"
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
 * ),
 * 
 * @ApiFilter(
 *     SearchFilter::class,
 *     properties={
 *      "listSmsLotsEnvoye": "exact",
 *      "contact": "exact",
 *      "contact.clientId": "exact"
 * 
 *     }
 * )
 *
 * @ORM\Entity(repositoryClass=ListSmsContactRepository::class)
 */
class ListSmsContact
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity=ListSmsLotsEnvoye::class, inversedBy="listSmsContacts")
     */
    private $listSmsLotsEnvoye;

    /**
     * @ORM\ManyToOne(targetEntity=Contact::class, inversedBy="listSmsContacts")
     * @Groups({"create:sms","read:sms","read:listsmslotsenvoye"})
     */
    private $contact;

    /**
     * @ORM\Column(type="boolean")
     * @Groups({"read:listsmscontact"})
     */
    private $status = false;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $responseApi;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getListSmsLotsEnvoye(): ?ListSmsLotsEnvoye
    {
        return $this->listSmsLotsEnvoye;
    }

    public function setListSmsLotsEnvoye(?ListSmsLotsEnvoye $listSmsLotsEnvoye): self
    {
        $this->listSmsLotsEnvoye = $listSmsLotsEnvoye;

        return $this;
    }

    public function getContact(): ?Contact
    {
        return $this->contact;
    }

    public function setContact(?Contact $contact): self
    {
        $this->contact = $contact;

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

    public function getResponseApi(): ?string
    {
        return $this->responseApi;
    }

    public function setResponseApi(?string $responseApi): self
    {
        $this->responseApi = $responseApi;

        return $this;
    }
}
