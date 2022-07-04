<?php


namespace Documents;


use BaseModel;

class Counterparty extends BaseModel
{
    protected string $model = 'Counterparty';

    /**
     * getCounterparties() function of model Counterparty.
     *
     * @param string $counterpartyProperty Type of Counterparty (Sender|Recipient)
     * @param int|null $page Page number
     * @param string|null $findByString String to search
     * @param string|null $cityRef City ID
     *
     * @return mixed
     */
    public function getCounterparties(
        string $counterpartyProperty = 'Recipient',
        int $page = null,
        string $findByString = null,
        string $cityRef = null
    ): mixed {
        // Any param can be skipped
        $params = [];
        $params['CounterpartyProperty'] = $counterpartyProperty ?? 'Recipient';
        $page and $params['Page'] = $page;
        $findByString and $params['FindByString'] = $findByString;
        $cityRef and $params['City'] = $cityRef;

        return $this->sendRequest('getCounterparties', $params);
    }

    /**
     * cloneLoyaltyCounterpartySender() function of model Counterparty
     * The counterparty will be not created immediately, you can wait a long time.
     *
     * @param string $cityRef City ID
     *
     * @return mixed
     */
    public function cloneLoyaltyCounterpartySender(string $cityRef): mixed
    {
        return $this->sendRequest('cloneLoyaltyCounterpartySender', ['CityRef' => $cityRef]);
    }

    /**
     * getCounterpartyContactPersons() function of model Counterparty.
     *
     * @param string $ref Counterparty ref
     *
     * @return mixed
     */
    public function getCounterpartyContactPersons(string $ref): mixed
    {
        return $this->sendRequest('getCounterpartyContactPersons', ['Ref' => $ref]);
    }

    /**
     * getCounterpartyAddresses() function of model Counterparty.
     *
     * @param string $ref Counterparty ref
     * @param int $page
     *
     * @return mixed
     */
    public function getCounterpartyAddresses(string $ref, int $page = 0): mixed
    {
        return $this->sendRequest('getCounterpartyAddresses', ['Ref' => $ref, 'Page' => $page]);
    }

    /**
     * getCounterpartyOptions() function of model Counterparty.
     *
     * @param string $ref Counterparty ref
     *
     * @return mixed
     */
    public function getCounterpartyOptions(string $ref): mixed
    {
        return $this->sendRequest('getCounterpartyOptions', ['Ref' => $ref]);
    }

    /**
     * getCounterpartyByEDRPOU() function of model Counterparty.
     *
     * @param string $edrpou EDRPOU code
     * @param string $cityRef City ID
     *
     * @return mixed
     */
    public function getCounterpartyByEDRPOU(string $edrpou, string $cityRef): mixed
    {
        return $this->sendRequest('getCounterpartyByEDRPOU', ['EDRPOU' => $edrpou, 'cityRef' => $cityRef]);
    }
}