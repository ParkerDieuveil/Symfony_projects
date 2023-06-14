<?php




namespace App\Entity\Trait;

use Doctrine\ORM\Mapping as ORM;

trait CreatedAtTrait
{

    #[ORM\Column(type: 'datetime_immutable', options: ['default' => 'CURRENT_TIMESTAMP'])]
    private $create_at;

    public function getCreateAt(): ?\DateTimeImmutable
    {
        return $this->create_at;
    }

    public function setCreateAt(\DateTimeImmutable $create_at ): self
    {
        $this->create_at;
        return $this;
    }
}