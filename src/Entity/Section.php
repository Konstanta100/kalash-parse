<?php


namespace App\Entity;


class Section
{
    private string $html;

    private string $_color;

    private string $_image;

    private string $parentName;

    private string $parentNameRus;

    private int $postId;

    private int $firstParentId;

    private string $postDate;

    /**
     * @return int
     */
    public function getFirstParentId(): int
    {
        return $this->firstParentId;
    }

    /**
     * @param int $firstParentId
     * @return Section
     */
    public function setFirstParentId(int $firstParentId): self
    {
        $this->firstParentId = $firstParentId;
        return $this;
    }

    /**
     * @return int
     */
    public function getPostId(): int
    {
        return $this->postId;
    }

    /**
     * @param int $postId
     * @return Section
     */
    public function setPostId(int $postId): self
    {
        $this->postId = $postId;
        return $this;
    }

    /**
     * @return string
     */
    public function getParentNameRus(): string
    {
        return $this->parentNameRus;
    }

    /**
     * @param string $parentNameRus
     * @return Section
     */
    public function setParentNameRus(string $parentNameRus): self
    {
        $this->parentNameRus = $parentNameRus;
        return $this;
    }

    /**
     * @return string
     */
    public function getParentName(): string
    {
        return $this->parentName;
    }

    /**
     * @param string $parentName
     * @return Section
     */
    public function setParentName(string $parentName): self
    {
        $this->parentName = $parentName;
        return $this;
    }

    /**
     * @return string
     */
    public function getColor(): string
    {
        return $this->_color;
    }

    /**
     * @param string $color
     * @return Section
     */
    public function setColor(string $color): self
    {
        $this->_color = $color;
        return $this;
    }

    /**
     * @return string
     */
    public function getImage(): string
    {
        return $this->_image;
    }

    /**
     * @param string $image
     * @return Section
     */
    public function setImage(string $image): self
    {
        $this->_image = $image;
        return $this;
    }

    /**
     * @return string
     */
    public function getHtml(): string
    {
        return $this->html;
    }

    /**
     * @param string $html
     * @return Section
     */
    public function setHtml(string $html): self
    {
        $this->html = $html;
        return $this;
    }

    /**
     * @return string
     */
    public function getPostDate(): string
    {
        return $this->postDate;
    }

    /**
     * @param string $postDate
     * @return Section
     */
    public function setPostDate(string $postDate): self
    {
        $this->postDate = $postDate;
        return $this;
    }
}