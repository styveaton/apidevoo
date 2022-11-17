<?php

namespace App\Entity\Pub;

use ApiPlatform\Core\Annotation\ApiProperty;
use App\Repository\Pub\PublicationRepository;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use ApiPlatform\Core\Annotation\ApiResource;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity(repositoryClass=PublicationRepository::class) 
 * * @ApiResource(
 *      normalizationContext={"groups"={"pub:read"}},
 *      denormalizationContext={"groups"={"pub:write"}}
 * )
 */
class Publication
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     * @Groups({"pub:read","category:read"})
     * 
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     * @Groups({"pub:read","pub:write","category:read"})
     * 
     */
    private $title;

    /**
     * @ORM\Column(type="string", length=5000)
     * @Groups({"pub:read","pub:write","category:read"})
     * 
     */
    private $description;

    /**
     * @ORM\JoinColumn(nullable=true)
     * @ApiProperty(iri="http://schema.org/publicationObject")
     * @ORM\ManyToOne(targetEntity=PublicationObject::class)
     * @Groups({"pub:read","pub:write","category:read"})
     */
    private $publicationObject;

    /**
     * @Groups({"pub:read","pub:write" })
     * 
     * @ORM\ManyToOne(targetEntity=CategoryPub::class, inversedBy="publications")
     */
    private $categoryPub;

    /**
     * @ORM\Column(type="integer",nullable =true)
     * @Groups({"pub:read","pub:write","category:read"})
     */
    private $sms;

    /**
     * @ORM\Column(type="string", length=255,nullable =true)
     */
    private $clef;

    /**
     * @ORM\Column(type="datetime")
     */
    private $dateCreated;


    public function __construct()
    {

        $this->dateCreated = new \DateTime();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;

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

    public function getPublicationObject(): ?PublicationObject
    {
        return $this->publicationObject;
    }

    public function setPublicationObject(?PublicationObject $publicationObject): self
    {
        $this->publicationObject = $publicationObject;

        return $this;
    }

    public function getCategoryPub(): ?CategoryPub
    {
        return $this->categoryPub;
    }

    public function setCategoryPub(?CategoryPub $categoryPub): self
    {
        $this->categoryPub = $categoryPub;

        return $this;
    }

    public function getSms(): ?int
    {
        return $this->sms;
    }

    public function setSms(int $sms): self
    {
        $this->sms = $sms;

        return $this;
    }

    public function getClef(): ?string
    {
        return $this->clef;
    }

    public function setClef(string $clef): self
    {
        $this->clef = $clef;

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
}
