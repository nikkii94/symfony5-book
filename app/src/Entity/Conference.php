<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\String\Slugger\SluggerInterface;

/**
 * @ApiResource(
 *     collectionOperations={"get"={"normalization_context"={"groups"="conference:list"}}},
 *     itemOperations={"get"={"normalization_context"={"groups"="conference:list"}}},
 *              order={"year"="DESC", "city"="ASC"},
 *              paginationEnabled=false
 * )
 * @ORM\Entity(repositoryClass="App\Repository\ConferenceRepository")
 * @UniqueEntity("slug")
 */
class Conference
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     *
     * @Groups({"conference:list", "conference:item"})
     */
    private ?int $id = null;

    /**
     * @ORM\Column(type="string", length=255)
     *
     * @Groups({"conference:list", "conference:item"})
     */
    private ?string $city = null;

    /**
     * @ORM\Column(type="string", length=4)
     *
     * @Groups({"conference:list", "conference:item"})
     */
    private ?string $year = null;

    /**
     * @ORM\Column(type="boolean")
     *
     * @Groups({"conference:list", "conference:item"})
     */
    private ?bool $isInternational = null;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Comment", mappedBy="conference", orphanRemoval=true)
     */
    private ?Collection $comments;

    /**
     * @ORM\Column(type="string", length=255, unique=true)
     *
     * @Groups({"conference:list", "conference:item"})
     */
    private ?string $slug = null;

    public function __serialize(): array
    {
        return ['id' => $this->getId()];
    }

    public function __unserialize(array $data): void
    {
        $this->id = $data['id'];
    }

    public function __construct()
    {
        $this->comments = new ArrayCollection();
    }

    public function __toString() :string
    {
        return $this->city . ' ' . $this->year;
    }

    /**
     * Generate unique slug
     * Cant use PrePersist because the injected SluggerInterface, so EntityListener will be used
     * @param SluggerInterface $slugger
     */
    public function computeSlug(SluggerInterface $slugger) :void {
        if (!$this->slug || '-' === $this->slug) {
            $this->slug = (string) $slugger->slug((string) $this)->lower();
        }
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCity(): ?string
    {
        return $this->city;
    }

    public function setCity(string $city): self
    {
        $this->city = $city;

        return $this;
    }

    public function getYear(): ?string
    {
        return $this->year;
    }

    public function setYear(string $year): self
    {
        $this->year = $year;

        return $this;
    }

    public function getIsInternational(): ?bool
    {
        return $this->isInternational;
    }

    public function setIsInternational(bool $isInternational): self
    {
        $this->isInternational = $isInternational;

        return $this;
    }

    /**
     * @return Collection|Comment[]
     */
    public function getComments(): Collection
    {
        return $this->comments;
    }

    public function addComment(Comment $comment): self
    {
        if (!$this->comments->contains($comment)) {
            $this->comments[] = $comment;
            $comment->setConference($this);
        }

        return $this;
    }

    public function removeComment(Comment $comment): self
    {
        if ($this->comments->contains($comment)) {
            $this->comments->removeElement($comment);
            // set the owning side to null (unless already changed)
            if ($comment->getConference() === $this) {
                $comment->setConference(null);
            }
        }

        return $this;
    }

    public function getSlug(): ?string
    {
        return $this->slug;
    }

    public function setSlug(string $slug): self
    {
        $this->slug = $slug;

        return $this;
    }
}
