<?php

namespace App\Entity\Vitrine;

use ApiPlatform\Core\Annotation\ApiProperty;
use ApiPlatform\Core\Annotation\ApiResource;
use App\Controller\CreateVitrineObjectAction;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;
use Vich\UploaderBundle\Mapping\Annotation as Vich;
use App\Repository\Vitrine\VitrineObjectRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=VitrineObjectRepository::class)
 @ApiResource(
 *     iri="http://schema.org/VitrineObject",
 *     normalizationContext={
 *         "groups"={"read:vitrineObject"}
 *     },
 *     collectionOperations={  
 * 
 *         "post"={  
 * "controller"=CreateVitrineObjectAction::class,
 *  "deserialize"=false,
 * "denormalization_context"={
 *                  "groups"={
 *                      "create:vitrineObject"
 *                  }
 *              },
 *               "input_formats"={
 *                  "multipart"={ "multipart/form-data" }
 *              },
 *            
 *              "security"="is_granted('IS_AUTHENTICATED_ANONYMOUSLY')"
 *         },
 *     },
 *     itemOperations={

 *         "get"={
 *              "security"="is_granted('IS_AUTHENTICATED_ANONYMOUSLY')"
 *          },
 *         "delete"={
 *              "security"="is_granted('IS_AUTHENTICATED_ANONYMOUSLY')"
 *          }
 *     }
 * )
 * @Vich\Uploadable
 */
class VitrineObject
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;


    /**
     * @ApiProperty(iri="http://schema.org/contentUrl")
     * @Groups({"read:vitrineObject","read:vitrine" })
     */
    public ?string $contentUrl = '';

    /**
     * @Assert\NotNull(groups={"create:vitrineObject"})
     * @Groups({"create:vitrineObject"})
     * @Vich\UploadableField(mapping="vitrine_object", fileNameProperty="filePath")
     */
    public ?File $file = null;

    /**
     * @ORM\Column(nullable=true)
     * @Groups({"category:read"})
     */
    public ?string $filePath = null;

    /**
     * @ORM\ManyToOne(targetEntity=Contenu::class, inversedBy="vitrineObjects")
     *  @Groups({"create:vitrineObject" })
     */
    private $contenu;

    /**
     * @ORM\ManyToOne(targetEntity=Vitrine::class, inversedBy="vitrineObjects")
     * @Groups({"create:vitrineObject" })
     */
    private $Vitrine;

    public function setFile(?File $fileA): self
    {

        $this->file = $fileA;

        return $this;
    }
    public function setFilePath(?string $filePath): self
    {

        $this->filePath = $filePath;

        return $this;
    }

    // public function getFilePath(): ?string
    // {
    //     return $this->filePath;
    // }
    public function getFilePathId(): ?string
    {
        return $this->filePath;
    }
    public function getId(): ?int
    {
        return $this->id;
    }

    public function getContenu(): ?Contenu
    {
        return $this->contenu;
    }

    public function setContenu(?Contenu $contenu): self
    {
        $this->contenu = $contenu;

        return $this;
    }

    public function getVitrine(): ?Vitrine
    {
        return $this->Vitrine;
    }

    public function setVitrine(?Vitrine $Vitrine): self
    {
        $this->Vitrine = $Vitrine;

        return $this;
    }
}
