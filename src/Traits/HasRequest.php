<?php

use Convertor\ConvertToJson;
use Convertor\ConvertToXml;
use Option\Config;

trait HasRequest
{


    protected Config $option;

    public function request(string $model, string $method, array $params)
    {
        $url = $this->genericUrl();

        $data = [
            'language'         => $this->option->getLanguage(),
            'apiKey'           => $this->option->getKey(),
            'modelName'        => $model,
            'calledMethod'     => $method,
            'methodProperties' => $params,
        ];
        $result = [];
        // Convert data to neccessary format
        $post = 'xml' == $this->option->getFormat()
            ? (new ConvertToXml())->convert($data)
            : (new ConvertToJson())->convert($data);

        if ('curl' == $this->getConnectionType()) {
            $ch = curl_init($url);
            if (is_resource($ch)) {
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                curl_setopt($ch, CURLOPT_HTTPHEADER,
                    ['Content-Type: ' . ('xml' == $this->format ? 'text/xml' : 'application/json')]);
                curl_setopt($ch, CURLOPT_HEADER, 0);
                curl_setopt($ch, CURLOPT_POST, 1);
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $post);

                if ($this->timeout > 0) {
                    curl_setopt($ch, CURLOPT_TIMEOUT, $this->timeout);
                    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $this->timeout);
                }

                $result = curl_exec($ch);
                curl_close($ch);
            }
        } else {
            $httpOptions = [
                'method'  => 'POST',
                'header'  => "Content-type: application/x-www-form-urlencoded;\r\n",
                'content' => $post,
            ];

            if ($this->timeout > 0) {
                $httpOptions['timeout'] = $this->timeout;
            }

            $result = file_get_contents($url, false, stream_context_create([
                'http' => $httpOptions,
            ]));
        }

        return $this->prepare($result);
    }

    /**
     * Prepare data before return it.
     *
     * @param string|array $data
     *
     * @return mixed
     */
    protected function prepare(array|string $data): mixed
    {
        // Returns array
        if ('array' == $this->option->getFormat()) {
            $result = is_array($data)
                ? $data
                : json_decode($data, true);
            // If error exists, throw Exception
            if ($this->throwErrors and array_key_exists('errors', $result) and $result['errors']) {
                throw new \Exception(is_array($result['errors']) ? implode("\n",
                    $result['errors']) : $result['errors']);
            }
            return $result;
        }

        return $data;
    }

    protected function genericUrl(): string
    {
        $url = sprintf('%s/%s/', Config::API_URI, $this->option->getFormat());

        return $url;
    }
}