<?php


namespace LisDev\Delivery;


use HasRequest;

class Areas
{
    use HasRequest;

    const ERROR_MESSAGE = 'Area was not found';

    protected array $areas;

    public function __construct()
    {
        if (empty($this->areas)) {
            $this->areas = (include dirname(__FILE__) . '/NovaPoshtaApi2Areas.php');
        }
    }

    /**
     * @param string $findByString
     * @param string $ref
     * @return mixed
     * @throws \Exception
     */

    public function getArea(string $findByString = '', string $ref = ''): mixed
    {
        $data = $this->findArea($this->areas, $findByString, $ref);
        $error = empty($data) ? [] : [self::ERROR_MESSAGE];

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
     * Get areas list by city and/or search string.
     *
     * @param string $ref ID of area
     * @param int $page
     *
     * @return mixed
     */
    public function getAreas(string $ref, int $page = 0): mixed
    {
        return $this->request('Address', 'getAreas', ['Ref' => $ref, 'Page' => $page,]);
    }

    /**
     * Find current area in list of areas.
     *
     * @param array $areas List of arias, getted from file
     * @param string $findByString Area name
     * @param string $ref Area Ref ID
     *
     * @return array
     */
    protected function findArea(array $areas, string $findByString = '', string $ref = ''): array
    {
        $data = [];

        if (!$findByString && !$ref) {
            return $data;
        }

        foreach ($areas as $key => $area) {
            // Is current area found by string or by key
            $found = $findByString
                ? ((false !== mb_stripos($area['Description'], $findByString))
                    or (false !== mb_stripos($area['DescriptionRu'], $findByString))
                    or (false !== mb_stripos($area['Area'], $findByString))
                    or (false !== mb_stripos($area['AreaRu'], $findByString)))
                : ($key == $ref);
            if ($found) {
                $area['Ref'] = $key;
                $data[] = $area;
                break;
            }
        }
        return $data;
    }
}