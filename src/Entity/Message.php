<?php

declare(strict_types=1);

namespace App\Entity;

use App\Entity\Traits\PrimaryKeyTrait;
use App\Entity\Traits\TimestampableTrait;
use App\Entity\Traits\UuidTrait;
use App\Repository\MessageRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Uid\Uuid;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: MessageRepository::class)]
class Message
{
    use PrimaryKeyTrait;
    use TimestampableTrait;
    use UuidTrait;

    public const CONTENT_MIN_LENGTH = 10;
    public const CONTENT_MAX_LENGTH = 6000;

    #[Gedmo\Blameable(on: 'create')]
    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'messages')]
    private ?User $author = null;

    #[ORM\Column(type: 'text')]
    #[Assert\NotBlank(message: 'Votre message ne peut être vide.')]
    #[Assert\Length(min: self::CONTENT_MIN_LENGTH, max: self::CONTENT_MAX_LENGTH, minMessage: 'Votre message doit faire au moins 3 caractères.', maxMessage: 'Votre message doit faire au maximum {{ limit }} caractères.')]
    private ?string $content = null;

    #[ORM\ManyToOne(targetEntity: Thread::class, inversedBy: 'messages')]
    #[ORM\JoinColumn(nullable: false)]
    private $thread;

    #[Gedmo\Blameable(on: 'change', field: ['content'])]
    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'updatedMessages')]
    private ?User $updatedBy = null;

    #[ORM\OneToMany(mappedBy: 'message', targetEntity: MessageLike::class, orphanRemoval: true)]
    private Collection $likes;

    #[ORM\OneToMany(targetEntity: Report::class, mappedBy: 'message', orphanRemoval: true)]
    private Collection $reports;

    public function __construct()
    {
        $this->uuid = Uuid::v4();
        $this->likes = new ArrayCollection();
        $this->reports = new ArrayCollection();
    }

    public function getAuthor(): ?User
    {
        return $this->author;
    }

    public function setAuthor(?User $author): self
    {
        $this->author = $author;

        return $this;
    }

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent(?string $content): self
    {
        $this->content = $content;

        return $this;
    }

    public function getThread(): Thread
    {
        return $this->thread;
    }

    public function setThread(?Thread $thread): self
    {
        $this->thread = $thread;

        return $this;
    }

    public function getUpdatedBy(): ?User
    {
        return $this->updatedBy;
    }

    public function setUpdatedBy(?User $updatedBy): self
    {
        $this->updatedBy = $updatedBy;

        return $this;
    }

    /**
     * @return Collection|MessageLike[]
     */
    public function getLikes(): Collection
    {
        return $this->likes;
    }

    public function addLike(MessageLike $like): self
    {
        if (!$this->likes->contains($like)) {
            $this->likes[] = $like;
            $like->setMessage($this);
        }

        return $this;
    }

    public function removeLike(MessageLike $like): self
    {
        if ($this->likes->removeElement($like)) {
            // set the owning side to null (unless already changed)
            if ($like->getMessage() === $this) {
                $like->setMessage(null);
            }
        }

        return $this;
    }

    public function isLikeByUser(User $user): bool
    {
        foreach ($this->likes as $like) {
            if ($like->getUser() === $user) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return Collection|Message[]
     */
    public function getReports(): Collection
    {
        return $this->reports;
    }

    public function addReport(Report $report): self
    {
        if (!$this->reports->contains($report)) {
            $this->reports[] = $report;
            $report->setMessage($this);
        }

        return $this;
    }

    public function removeReport(Report $report): self
    {
        if ($this->reports->removeElement($report)) {
            // set the owning side to null (unless already changed)
            if ($report->getMessage() === $this) {
                $report->setMessage(null);
            }
        }

        return $this;
    }
}
