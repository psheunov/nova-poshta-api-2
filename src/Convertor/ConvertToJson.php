<?php


namespace Convertor;


class ConvertToJson implements Convertor
{
    public function convert(array $data): bool|string
    {
        return json_encode($data);
    }
}