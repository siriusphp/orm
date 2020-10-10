<?php

class ProductEntity {
    /**
     * @var int
     */
    protected $id;

    /**
     * @var string
     */
    protected $name;

    /**
     * @var ProductEntity[]
     */
    protected $images;

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @param int $id
     */
    public function setId(int $id): void
    {
        $this->id = $id;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName(string $name): void
    {
        $this->name = $name;
    }

    /**
     * @return ProductEntity[]
     */
    public function getImages(): array
    {
        return $this->images;
    }

    /**
     * @param ProductEntity[] $images
     */
    public function setImages(array $images): void
    {
        $this->images = $images;
    }


}
