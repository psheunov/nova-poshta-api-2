<?php


namespace LisDev\Delivery;


use Option\Config;

class Address extends \BaseModel
{
    const ERROR_MESSAGE = 'Warehouse was not found';
    protected string $model = 'Address';
    protected Areas $areas;

    public function __construct(Config $option)
    {
        $this->areas = new Areas();
        parent::__construct($option);
    }

    /**
     * Get cities of company NovaPoshta.
     *
     * @param int $page Num of page
     * @param string $findByString Find city by russian or ukrainian word
     * @param string $ref ID of city
     *
     * @return mixed
     */
    public function getCities(int $page = 0, string $findByString = '', string $ref = ''): mixed
    {
        return $this->sendRequest('getCities', [
            'Page'         => $page,
            'FindByString' => $findByString,
            'Ref'          => $ref,
        ]);
    }

    /**
     * Get warehouses by city.
     *
     * @param string $cityRef ID of city
     * @param int $page
     *
     * @return mixed
     */
    public function getWarehouses(string $cityRef, int $page = 0): mixed
    {
        return $this->sendRequest('getWarehouses', [
            'CityRef' => $cityRef,
            'Page'    => $page,
        ]);
    }

    /**
     * Get warehouse types.
     *
     * @return mixed
     */
    public function getWarehouseTypes(): mixed
    {
        return $this->sendRequest('getWarehouseTypes', null);
    }

    /**
     * Get 5 nearest warehouses by array of strings.
     *
     * @param array $searchStringArray
     *
     * @return mixed
     */
    public function findNearestWarehouse(array $searchStringArray): mixed
    {
        return $this->sendRequest('findNearestWarehouse', [
            'SearchStringArray' => $searchStringArray,
        ]);
    }

    /**
     * Get one warehouse by city name and warehouse's description.
     *
     * @param string $cityRef ID of city
     * @param string $description Description like in getted by getWarehouses()
     *
     * @return mixed
     * @throws \Exception
     */
    public function getWarehouse($cityRef, $description = ''): mixed
    {
        $warehouses = $this->getWarehouses($cityRef);
        $error = [];
        $data = [];

        if (is_array($warehouses['data'])) {
            $data = $warehouses['data'][0];
            if (count($warehouses['data']) > 1 && $description) {
                foreach ($warehouses['data'] as $warehouse) {
                    if (false !== mb_stripos($warehouse['Description'], $description)
                        or false !== mb_stripos($warehouse['DescriptionRu'], $description)) {
                        $data = $warehouse;
                        break;
                    }
                }
            }
        }


        (!$data) and $error = [self::ERROR_MESSAGE];

        return $this->prepare(
            [
                'success'  => empty($error),
                'data'     => [$data],
                'errors'   => $error,
                'warnings' => [],
                'info'     => [],
            ]
        );
    }


    /**
     * Get streets list by city and/or search string.
     *
     * @param string $cityRef ID of city
     * @param string $findByString
     * @param int $page
     *
     * @return mixed
     */
    public function getStreet(string $cityRef, string $findByString = '', int $page = 0): mixed
    {
        return $this->sendRequest('getStreet', [
            'FindByString' => $findByString,
            'CityRef'      => $cityRef,
            'Page'         => $page,
        ]);
    }

    /**
     * Get city by name and region (if it needs).
     *
     * @param string $cityName City's name
     * @param string $areaName Region's name
     * @param string $warehouseDescription Warehouse description to identiry needed city (if it more than 1 in the area)
     *
     * @return array Cities's data Can be returned more than 1 city with the same name
     * @throws \Exception
     */
    public function getCity(string $cityName, string $areaName = '', string $warehouseDescription = ''): array
    {
        // Get cities by name
        $cities = $this->getCities(0, $cityName);
        $data = [];
        if (is_array($cities) && is_array($cities['data'])) {
            // If cities more then one, calculate current by area name
            $data = (count($cities['data']) > 1)
                ? $this->findCityByRegion($cities, $areaName)
                : [$cities['data'][0]];
        }
        // Try to identify city by one of warehouses descriptions
        if (count($data) > 1 && $warehouseDescription) {
            foreach ($data as $cityData) {
                $warehouseData = $this->getWarehouse($cityData['Ref'], $warehouseDescription);
                $warehouseDescriptions = [
                    $warehouseData['data'][0]['Description'],
                    $warehouseData['data'][0]['DescriptionRu']
                ];
                if (in_array($warehouseDescription, $warehouseDescriptions)) {
                    $data = [$cityData];
                    break;
                }
            }
        }
        // Error
        $error = [];
        (!$data) and $error = ['City was not found'];
        // Return data in same format like NovaPoshta API
        return $this->prepare(
            [
                'success'  => empty($error),
                'data'     => $data,
                'errors'   => $error,
                'warnings' => [],
                'info'     => [],
            ]
        );
    }

    /**
     * Find city from list by name of region.
     *
     * @param array $cities Array from query getCities to NovaPoshta
     * @param string $areaName
     *
     * @return array
     * @throws \Exception
     */
    protected function findCityByRegion(array $cities, string $areaName): array
    {
        $data = [];
        $areaRef = '';

        $area =  $this->areas->getArea($areaName);
        $area['success'] and $areaRef = $area['data'][0]['Ref'];

        if ($areaRef and is_array($cities['data'])) {
            foreach ($cities['data'] as $city) {
                if ($city['Area'] == $areaRef) {
                    $data[] = $city;
                }
            }
        }

        return $data;
    }
}