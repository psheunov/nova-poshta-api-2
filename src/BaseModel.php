<?php


use Option\Config;

abstract class BaseModel
{
    use HasRequest;

    protected string $model = 'Common';

    public function __construct(Config $option)
    {
        $this->option = $option;
    }

    /**
     * @param array $params
     * @return mixed
     */
    public function delete(array $params): mixed
    {
        return $this->request($this->model, 'delete', $params);
    }


    /**
     * @param array $params
     * @return mixed
     */
    public function update(array $params): mixed
    {
        return $this->request($this->model, 'update', $params);
    }

    /**
     * @param array $params
     * @return mixed
     */
    public function save(array $params): mixed
    {
        return $this->request($this->model, 'save', $params);
    }

    /**
     * @param $method
     * @param $params
     * @return mixed
     */
    protected function sendRequest($method, $params): mixed
    {
        return $this->request($this->model, $method, $params);
    }
}