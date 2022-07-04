<?php

namespace LisDev\Delivery;

use BaseModel;
use Option\Config;

/**
 * Nova Poshta API Class.
 *
 * @author lis-dev
 *
 * @see https://my.novaposhta.ua/data/API2-200215-1622-28.pdf
 * @see https://github.com/lis-dev
 *
 * @license MIT
 */
class NovaPoshtaApi2 extends BaseModel
{
    const COMMON_METHODS = [
        'getTypesOfCounterparties',
        'getBackwardDeliveryCargoTypes',
        'getCargoDescriptionList',
        'getCargoTypes',
        'getDocumentStatuses',
        'getOwnershipFormsList',
        'getPalletsList',
        'getPaymentForms',
        'getTimeIntervals',
        'getServiceTypes',
        'getTiresWheelsList',
        'getTraysList',
        'getTypesOfAlternativePayers',
        'getTypesOfPayers',
        'getTypesOfPayersForRedelivery',
    ];

    /**
     * @var string Set method of current model
     */
    protected string $method = '';

    /**
     * @var array Set params of current method of current model
     */
    protected array $params = [];

    /**
     * @param string $model
     * @return $this|string
     */
    public function model(string $model = ''): string|static
    {
        if (!$model) {
            return $this->model;
        }

        $this->model = $model;
        $this->method = '';
        $this->params = [];

        return $this;
    }

    /**
     * Set method of current model property and empties params properties.
     *
     * @param string $method
     *
     * @return string|static
     */
    public function method(string $method = ''): static|string
    {
        if (!$method) {
            return $this->method;
        }

        $this->method = $method;
        $this->params = [];
        return $this;
    }

    /**
     * Set params of current method/property property.
     *
     * @param array $params
     *
     * @return static
     */
    public function params(array $params): static
    {
        $this->params = $params;
        return $this;
    }

    /**
     * Execute request to NovaPoshta API.
     *
     * @return mixed
     */
    public function execute(): mixed
    {
        return $this->sendRequest($this->method, $this->params);
    }


    /**
     * @param string $method
     * @param $args
     * @return mixed
     */
    public function __call(string $method, $args)
    {
        if (in_array($method, self::COMMON_METHODS)) {
            return $this
                ->model($this->model)
                ->method($method)
                ->params(null)
                ->execute();
        }
    }
}
