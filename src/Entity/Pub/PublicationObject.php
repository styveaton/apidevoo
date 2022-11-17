<?php

namespace App\Entity\Pub;

use ApiPlatform\Core\Annotation\ApiProperty;
use ApiPlatform\Core\Annotation\ApiResource;
use App\Controller\CreatePublicationObjectAction;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;
use Vich\UploaderBundle\Mapping\Annotation as Vich;
use App\Repository\Pub\PublicationObjectRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 *  @ORM\Entity(repositoryClass=PublicationObjectRepository::class)
 * @ApiResource(
 *     iri="http://schema.org/PublicationObject",
 *     normalizationContext={
 *         "groups"={"read:pubObject"}
 *     },
 *     collectionOperations={  
 *         "post"={  
 * "controller"=CreatePublicationObjectAction::class,
 *  "deserialize"=false,
 * "denormalization_context"={
 *                  "groups"={
 *                      "create:pubObject"
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
class PublicationObject
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     * @Groups({"read:pubObject",  "read:pub"})
     */
    private ?int $id = null;

    /**
     * @ApiProperty(iri="http://schema.org/contentUrl")
     * @Groups({"read:pubObject","read:pub" })
     */
    public ?string $contentUrl = null;

    /**
     * @Assert\NotNull(groups={"create:pubObject"})
     * @Groups({"create:pubObject"})
     * @Vich\UploadableField(mapping="publication_object", fileNameProperty="filePath")
     */
    public ?File $file = null;

    /**
     * @ORM\Column(nullable=true)
     * @Groups({"category:read"})
     */
    public ?string $filePath = null;







    public function getId(): ?int
    {
        return $this->id;
    }
    public function getFilePathId(): ?string
    {
        return $this->filePath;
    }
}
