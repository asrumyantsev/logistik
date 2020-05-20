<?php
/**
 *
 */

namespace Enot\NotificationBundle\Model;


class PushMessage
{
    /** @var string */
    private $title = "";

    /** @var string */
    private $text = "";

    /** @var array */
    private $data = [];

    /** @var bool */
    private $contentAvailable = false;

    private $filters = [];

    /**
     * @return array
     */
    public function getFields()
    {
        $fields = [
            'app_id' => "6425bbcc-e5c9-4797-a502-9d153608e8a9",
            'data' => $this->getData(),
            'content_available' => $this->isContentAvailable()
        ];

        if($this->getText()) {
            $fields['contents'] = [
                "en" => $this->getText()
            ];
        }

        if($this->getTitle()) {
            $fields['headings'] = [
                "en" => $this->getTitle()
            ];
        }

        foreach ($this->filters as $key => $value) {
            $filter = [
                "field" => "tag",
                "key" => $key,
                "relation" => "=",
                "value" => $value
            ];
            $fields['filters'][] = $filter;
        }

        if(!isset($fields['filters'])) {
            $fields['included_segments'] = [
                'All'
            ];
        }

        return $fields;
    }

    /**
     * @return mixed
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @param mixed $title
     */
    public function setTitle($title): void
    {
        $this->title = $title;
    }

    /**
     * @return mixed
     */
    public function getText()
    {
        return $this->text;
    }

    /**
     * @param mixed $text
     */
    public function setText($text): void
    {
        $this->text = $text;
    }

    /**
     * @return mixed
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * @param mixed $data
     */
    public function setData($data): void
    {
        $this->data = $data;
    }

    /**
     * @return bool
     */
    public function isContentAvailable(): bool
    {
        return $this->contentAvailable;
    }

    /**
     * @param bool $contentAvailable
     */
    public function setContentAvailable(bool $contentAvailable): void
    {
        $this->contentAvailable = $contentAvailable;
    }

    /**
     * @return array
     */
    public function getFilters(): array
    {
        return $this->filters;
    }

    /**
     * @param array $filters
     */
    public function setFilters(array $filters): void
    {
        $this->filters = $filters;
    }

    public function addFilter($name, $value)
    {
        $this->filters[$name] = $value;
    }
}