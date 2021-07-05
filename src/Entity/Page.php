<?php


namespace App\Entity;


class Page
{

    /**
     * @var int
     */
    private int $postId;

    /**
     * @var int
     */
    private int $parentId;

    /**
     * @var string
     */
    private string $title;


    /**
     * Url адреса
     * @var string
     */
    private string $url;

    /**
     * @var string
     */
    private string $parentUrl;

    /**
     * @var string
     */
    private string $content;

    /**
     * @return string
     */
    public function getUrl(): string
    {
        return $this->url;
    }

    /**
     * @param string $url
     * @return Page
     */
    public function setUrl(string $url): self
    {
        $this->url = $url;
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
     * @return Page
     */
    public function setPostId(int $postId): self
    {
        $this->postId = $postId;
        return $this;
    }

    /**
     * @return int
     */
    public function getParentId(): int
    {
        return $this->parentId;
    }

    /**
     * @param int $parentId
     * @return Page
     */
    public function setParentId(int $parentId): self
    {
        $this->parentId = $parentId;
        return $this;
    }

    /**
     * @param string $html
     * @return $this
     */
    public function setContent(string $html): self
    {
        $this->content = $html;
        return $this;
    }

    /**
     * @return string
     */
    public function getContent(): string
    {
        return $this->content;
    }

    /**
     * @return string
     */
    public function getTitle(): string
    {
        return $this->title;
    }

    /**
     * @param string $title
     * @return Page
     */
    public function setTitle(string $title): self
    {
        $this->title = $title;
        return $this;
    }

    /**
     * @return string
     */
    public function getParentUrl(): string
    {
        return $this->parentUrl;
    }

    /**
     * @param string $parentUrl
     * @return Page
     */
    public function setParentUrl(string $parentUrl): self
    {
        $this->parentUrl = $parentUrl;
        return $this;
    }
}