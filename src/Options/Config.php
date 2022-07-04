<?php

namespace Option;


class Config
{
    // TODO: may need to be converted to a singleton
    const API_URI = 'https://api.novaposhta.ua/v2.0';

    private string $key;

    /**
     * @var string Format of returned data - array, json, xml
     */
    protected string $format = 'array';
    protected string $language = 'ru';
    protected string $connectionType = 'curl';
    protected int $timeout = 0;
    protected bool $throwErrors = false;

    public function __construct($key)
    {
        $this->key = $key;
    }

    public function setKey($key): static
    {
        $this->key = $key;
        return $this;
    }

    /**
     * Getter for key property.
     *
     * @return string
     */
    public function getKey(): string
    {
        return $this->key;
    }

    public function setConnectionType($connectionType): static
    {
        $this->connectionType = $connectionType;
        return $this;
    }

    /**
     * Getter for $connectionType property.
     *
     * @return string
     */
    public function getConnectionType(): string
    {
        return $this->connectionType;
    }

    public function setLanguage($language): static
    {
        $this->language = $language;
        return $this;
    }

    /**
     * Getter for language property.
     *
     * @return string
     */
    public function getLanguage(): string
    {
        return $this->language;
    }

    public function setFormat($format): static
    {
        $this->format = $format;
        return $this;
    }

    public function getFormat(): string
    {
        return $this->format;
    }

    /**
     * @param int $timeout
     *
     * @return $this
     */
    public function setTimeout(int $timeout): static
    {
        $this->timeout = $timeout;

        return $this;
    }

    /**
     * @return int
     */
    public function getTimeout(): int
    {
        return $this->timeout;
    }
}