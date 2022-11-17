<?php

namespace App\Entity\Bulk;

use App\Repository\Bulk\ContactRepository;
use ApiPlatform\Core\Annotation\ApiFilter;
use ApiPlatform\Core\Annotation\ApiResource;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\SearchFilter;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ApiResource(
 *     normalizationContext={
 *          "groups"={
 *              "read:contact"
 *          }
 *     },
 *     collectionOperations={
 *          "get"={
 *           "read:contact"
 *          },
 *          "post"={
 *              "denormalization_context"={
 *                  "groups"={
 *                      "create:contact"
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
 *      "phone": "exact",
 *      "clientId": "exact"
 *     }
 * )
 *
 * @ORM\Entity(repositoryClass=ContactRepository::class)
 */
class Contact
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     * @Groups({"read:contact"})
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     * @Groups({"read:contact", "create:contact","read:listgroupecontact"})
     */
    private $nom = 'Inconnu';

    /**
     * @ORM\Column(type="string", length=255)
     * @Groups({"read:contact", "create:contact","read:listgroupecontact"})
     */
    private $prenom = 'Inconnu';

    /**
     * @ORM\Column(type="string", length=255)
     * @Groups({"read:contact", "create:contact","read:listgroupecontact","read:sms","read:listsmslotsenvoye"})
     */
    private $phone;

    /**
     * @ORM\Column(type="string", length=255)
     * @Groups({"read:contact", "create:contact"})
     */
    private $phoneCode = '+237';

    /**
     * @ORM\Column(type="string", length=255)
     * @Groups({"read:contact", "create:contact"})
     */
    private $attribute = '';

    /**
     * @ORM\Column(type="datetime")
     * @Groups({"read:contact"})
     */
    private $dateCreated;

    /**
     * @ORM\Column(type="integer", nullable=true)
     * @Groups({"read:contact", "create:contact","read:listgroupecontact"})
     */
    private $clientId;

    /**
     * @ORM\OneToMany(targetEntity=ListSmsContact::class, mappedBy="contact")
     * 
     */
    private $listSmsContacts;

    /**
     * @ORM\OneToMany(targetEntity=ListGroupeContact::class, mappedBy="contact")
     * @Groups({"read:contact"})
     */
    private $listGroupeContacts;

    /**
     * @ORM\Column(type="date", nullable=true)
     * @Groups({"read:contact", "create:contact","read:listgroupecontact"})
     * 
     */
    private $birdDay;

    /**
     * @ORM\OneToMany(targetEntity=ListNotificationContact::class, mappedBy="contact")
     */
    private $listNotificationContacts;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $projetId;

    

    public function __construct()
    {
        $this->listSmsContacts = new ArrayCollection();
        $this->dateCreated = new \DateTime();
        $this->listGroupeContacts = new ArrayCollection();
        $this->listNotificationContacts = new ArrayCollection(); 
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNom(): ?string
    {
        return $this->nom;
    }

    public function setNom(string $nom): self
    {
        $this->nom = $nom;

        return $this;
    }

    public function getPrenom(): ?string
    {
        return $this->prenom;
    }

    public function setPrenom(string $prenom): self
    {
        $this->prenom = $prenom;

        return $this;
    }

    public function getPhone(): ?string
    {
        return $this->phone;
    }

    public function setPhone(string $phone): self
    {
        $this->phone = $phone;

        return $this;
    }

    public function getPhoneCode(): ?string
    {
        return $this->phoneCode;
    }

    public function setPhoneCode(string $phoneCode): self
    {
        $this->phoneCode = $phoneCode;

        return $this;
    }

    public function getAttribute(): ?string
    {
        return $this->attribute;
    }

    public function setAttribute(string $attribute): self
    {
        $this->attribute = $attribute;

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
     * @return Collection<int, ListSmsContact>
     */
    public function getListSmsContacts(): Collection
    {
        return $this->listSmsContacts;
    }

    public function addListSmsContact(ListSmsContact $listSmsContact): self
    {
        if (!$this->listSmsContacts->contains($listSmsContact)) {
            $this->listSmsContacts[] = $listSmsContact;
            $listSmsContact->setContact($this);
        }

        return $this;
    }

    public function removeListSmsContact(ListSmsContact $listSmsContact): self
    {
        if ($this->listSmsContacts->removeElement($listSmsContact)) {
            // set the owning side to null (unless already changed)
            if ($listSmsContact->getContact() === $this) {
                $listSmsContact->setContact(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, ListGroupeContact>
     */
    public function getListGroupeContacts(): Collection
    {
        return $this->listGroupeContacts;
    }

    public function addListGroupeContact(ListGroupeContact $listGroupeContact): self
    {
        if (!$this->listGroupeContacts->contains($listGroupeContact)) {
            $this->listGroupeContacts[] = $listGroupeContact;
            $listGroupeContact->setContact($this);
        }

        return $this;
    }

    public function removeListGroupeContact(ListGroupeContact $listGroupeContact): self
    {
        if ($this->listGroupeContacts->removeElement($listGroupeContact)) {
            // set the owning side to null (unless already changed)
            if ($listGroupeContact->getContact() === $this) {
                $listGroupeContact->setContact(null);
            }
        }

        return $this;
    }

    public function getBirdDay(): ?\DateTimeInterface
    {
        return $this->birdDay;
    }

    public function setBirdDay(?\DateTimeInterface $birdDay): self
    {
        $this->birdDay = $birdDay;

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
            $listNotificationContact->setContact($this);
        }

        return $this;
    }

    public function removeListNotificationContact(ListNotificationContact $listNotificationContact): self
    {
        if ($this->listNotificationContacts->removeElement($listNotificationContact)) {
            // set the owning side to null (unless already changed)
            if ($listNotificationContact->getContact() === $this) {
                $listNotificationContact->setContact(null);
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
