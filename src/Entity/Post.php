<?php


namespace App\Entity;

/**
 * Class Post
 * @package App\Entity
 */
class Post
{
    /**
     * Url адреса
     * @var string
     */
    private string $url;

    /**
     * @var string
     */
    private string $id;

    /**
     * Создание записи
     * @var string
     */
    private string $date = '';

    /**
     * Краткое описание
     * @var string
     */
    private string $description = '';

    /**
     * Content html
     * @var string
     */
    private string $html = '';

    /**
     * Title
     * @var string
     */
    private string $title;

    /**
     * PostName
     * @var string|null
     */
    private ?string $postName;

    /**
     * @var array
     */
    private array $rubrics;

    /**
     * @var string
     */
    private string $endDate = '';

    /**
     * @return string
     */
    public function getUrl(): string
    {
        return $this->url;
    }

    /**
     * @param string $url
     * @return Post
     */
    public function setUrl(string $url): self
    {
        $this->url = $url;
        return $this;
    }

    /**
     * @return string
     */
    public function getDescription(): string
    {
        return $this->description;
    }

    /**
     * @param string $description
     * @return Post
     */
    public function setDescription(string $description): self
    {
        $this->description = $description;
        return $this;
    }

    /**
     * @return string
     */
    public function getDate(): string
    {
        return $this->date;
    }

    /**
     * @param string $date
     * @return Post
     */
    public function setDate(string $date): self
    {
        $this->date = $date;
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
     * @return Post
     */
    public function setHtml(string $html): self
    {
        $this->html = $html;
        return $this;
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
     * @return Post
     */
    public function setTitle(string $title): self
    {
        $this->title = $title;
        return $this;
    }

    /**
     * @return array
     */
    public function getRubrics(): array
    {
        return $this->rubrics;
    }

    /**
     * @param string $rubric
     * @return Post
     */
    public function addRubric(string $rubric): self
    {
        $this->rubrics[] = $rubric;
        return $this;
    }

    /**
     * @return string
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * @param string $id
     * @return Post
     */
    public function setId(string $id): self
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getPostName(): ?string
    {
        return $this->postName;
    }

    /**
     * @param string|null $postName
     * @return Post
     */
    public function setPostName(?string $postName): self
    {
        $this->postName = $postName;
        return $this;
    }

    /**
     * @param string $endDate
     * @return $this
     */
    public function setEndDate(string $endDate): self
    {
        $this->endDate = $endDate;
        return $this;
    }

    public function getEndDate(): string
    {
        return $this->endDate;
    }
}