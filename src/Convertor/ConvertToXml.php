<?php

namespace Convertor;

use SimpleXMLElement;

class ConvertToXml implements Convertor
{
    /**
     * @var SimpleXMLElement
     */
    private SimpleXMLElement $file;

    public function __construct()
    {
        $this->init();
    }

    public function convert(array $data): bool|string
    {
        foreach ($data as $key => $value) {
            if (is_numeric($key)) {
                $key = 'item';
            }

            if (is_array($value)) {
                $this->convert($value);
            } else {
                $this->file->addChild($key);
            }
        }
        return $this->file->asXML();
    }

    private function init()
    {
        $this->file = new SimpleXMLElement('<root/>');
    }
}