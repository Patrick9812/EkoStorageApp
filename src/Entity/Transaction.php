<?php

namespace App\Entity;

use App\Enum\TransactionType;
use App\Repository\TransactionRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: TransactionRepository::class)]
#[ORM\HasLifecycleCallbacks]
class Transaction
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(enumType: TransactionType::class)]
    private ?TransactionType $type = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 3)]
    private ?string $quantity = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2, nullable: true)]
    private ?string $priceNetto = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'transactions')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $user = null;

    #[ORM\ManyToOne(targetEntity: Warehouse::class, inversedBy: 'transactions')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Warehouse $warehouse = null;

    #[ORM\ManyToOne(targetEntity: Article::class, inversedBy: 'transactions')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Article $article = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 5, scale: 2, nullable: true)]
    private ?string $VAT = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $unit = null;

    #[ORM\OneToMany(targetEntity: InvoiceFile::class, mappedBy: 'transaction', cascade: ['persist', 'remove'], orphanRemoval: true)]
    private Collection $invoiceFiles;

    public function __construct()
    {
        $this->invoiceFiles = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getType(): ?TransactionType
    {
        return $this->type;
    }

    public function setType(TransactionType $type): static
    {
        $this->type = $type;
        return $this;
    }

    public function getQuantity(): ?string
    {
        return $this->quantity;
    }

    public function setQuantity(string $quantity): static
    {
        $this->quantity = $quantity;
        return $this;
    }

    public function getPriceNetto(): ?string
    {
        return $this->priceNetto;
    }

    public function setPriceNetto(?string $priceNetto): static
    {
        $this->priceNetto = $priceNetto;
        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;
        return $this;
    }

    #[ORM\PrePersist]
    public function setCreatedAtValue(): void
    {
        if ($this->createdAt === null) {
            $this->createdAt = new \DateTimeImmutable();
        }
    }

    public function getVAT(): ?string
    {
        return $this->VAT;
    }

    public function setVAT(?string $VAT): static
    {
        $this->VAT = $VAT;
        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): static
    {
        $this->user = $user;
        return $this;
    }

    public function getWarehouse(): ?Warehouse
    {
        return $this->warehouse;
    }

    public function setWarehouse(?Warehouse $warehouse): static
    {
        $this->warehouse = $warehouse;
        return $this;
    }

    public function getArticle(): ?Article
    {
        return $this->article;
    }

    public function setArticle(?Article $article): static
    {
        $this->article = $article;
        return $this;
    }

    /**
     * @return Collection<int, InvoiceFile>
     */
    public function getInvoiceFiles(): Collection
    {
        return $this->invoiceFiles;
    }

    public function addInvoiceFile(InvoiceFile $invoiceFile): static
    {
        if (!$this->invoiceFiles->contains($invoiceFile)) {
            $this->invoiceFiles->add($invoiceFile);
            $invoiceFile->setTransaction($this);
        }

        return $this;
    }

    public function removeInvoiceFile(InvoiceFile $invoiceFile): static
    {
        if ($this->invoiceFiles->removeElement($invoiceFile)) {
            // set the owning side to null (unless already changed)
            if ($invoiceFile->getTransaction() === $this) {
                $invoiceFile->setTransaction(null);
            }
        }

        return $this;
    }

    public function getUnit(): ?string
    {
        return $this->unit;
    }

    public function setUnit(?string $unit): self
    {
        $this->unit = $unit;
        return $this;
    }
}
