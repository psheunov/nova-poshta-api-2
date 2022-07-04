<?php


namespace Documents;


use BaseModel;
use LisDev\Delivery\Address;
use Option\Config;

class InternetDocument extends BaseModel
{
    protected string $model = 'InternetDocument';
    private Counterparty $counterparty;
    private Address $address;

    public function __construct(Config $option)
    {
        $this->init();
        parent::__construct($option);
    }

    //:TODO split a method into multiple methods or classes
    /**
     * Create Internet Document by.
     *
     * @param array $sender Sender info.
     *                         Required:
     *                         For existing sender:
     *                         'Description' => String (Full name i.e.), 'City' => String (City name)
     *                         For creating:
     *                         'FirstName' => String, 'MiddleName' => String,
     *                         'LastName' => String, 'Phone' => '000xxxxxxx', 'City' => String (City name), 'Region' => String (Region name),
     *                         'Warehouse' => String (Description from getWarehouses))
     * @param array $recipient Recipient info, same like $sender param
     * @param array $params Additional params of Internet Document
     *                         Required:
     *                         'Description' => String, 'Weight' => Float, 'Cost' => Float
     *                         Recommended:
     *                         'VolumeGeneral' => Float (default = 0.004), 'SeatsAmount' => Int (default = 1),
     *                         'PayerType' => (Sender|Recipient - default), 'PaymentMethod' => (NonCash|Cash - default)
     *                         'ServiceType' => (DoorsDoors|DoorsWarehouse|WarehouseDoors|WarehouseWarehouse - default)
     *                         'CargoType' => String
     * @return mixed
     * @throws \Exception
     */
    public function Create(array $sender, array $recipient, array $params): mixed
    {
        // Check for required params and set defaults
        $this->checkInternetDocumentRecipient($recipient);
        $this->checkInternetDocumentParams($params);

        if (empty($sender['CitySender'])) {
            $senderCity = $this->address->getCity($sender['City'], $sender['Region'], $sender['Warehouse']);
            $sender['CitySender'] = $senderCity['data'][0]['Ref'];
        }

        $sender['CityRef'] = $sender['CitySender'];
        if (empty($sender['SenderAddress']) and $sender['CitySender'] and $sender['Warehouse']) {
            $senderWarehouse =  $this->address->getWarehouse($sender['CitySender'], $sender['Warehouse']);
            $sender['SenderAddress'] = $senderWarehouse['data'][0]['Ref'];
        }

        if (empty($sender['Sender'])) {
            $sender['CounterpartyProperty'] = 'Sender';
            $fullName = trim($sender['LastName'] . ' ' . $sender['FirstName'] . ' ' . $sender['MiddleName']);
            // Set full name to Description if is not set
            if (empty($sender['Description'])) {
                $sender['Description'] = $fullName;
            }
            // Check for existing sender
            $senderCounterpartyExisting = $this->counterparty->getCounterparties('Sender', 1, $fullName, $sender['CityRef']);
            // Copy user to the selected city if user doesn't exists there
            if (isset($senderCounterpartyExisting['data'][0]['Ref'])) {
                // Counterparty exists
                $sender['Sender'] = $senderCounterpartyExisting['data'][0]['Ref'];
                $contactSender = $this->counterparty->getCounterpartyContactPersons($sender['Sender']);
                $sender['ContactSender'] = $contactSender['data'][0]['Ref'];
                $sender['SendersPhone'] = $sender['Phone'] ?? $contactSender['data'][0]['Phones'];
            }
        }

        // Prepare recipient data
        $recipient['CounterpartyProperty'] = 'Recipient';
        $recipient['RecipientsPhone'] = $recipient['Phone'];
        if (empty($recipient['CityRecipient'])) {
            $recipientCity =  $this->address->getCity($recipient['City'], $recipient['Region'], $recipient['Warehouse']);
            $recipient['CityRecipient'] = $recipientCity['data'][0]['Ref'];
        }
        $recipient['CityRef'] = $recipient['CityRecipient'];
        if (empty($recipient['RecipientAddress'])) {
            $recipientWarehouse =  $this->address->getWarehouse($recipient['CityRecipient'], $recipient['Warehouse']);
            $recipient['RecipientAddress'] = $recipientWarehouse['data'][0]['Ref'];
        }
        if (empty($recipient['Recipient'])) {
            $recipientCounterparty = $this->counterparty->save($recipient);
            $recipient['Recipient'] = $recipientCounterparty['data'][0]['Ref'];
            $recipient['ContactRecipient'] = $recipientCounterparty['data'][0]['ContactPerson']['data'][0]['Ref'];
        }
        // Full params is merge of arrays $sender, $recipient, $params
        $paramsInternetDocument = array_merge($sender, $recipient, $params);

        // Creating new Internet Document
        return $this->save($paramsInternetDocument);
    }

    /**
     * @param array|null $params
     * @return mixed
     */
    public function getDocumentList(array $params = null): mixed
    {
        return $this->sendRequest('getDocumentList', $params ?: null);
    }

    /**
     * @param string $ref
     * @return mixed
     */
    public function getDocument(string $ref): mixed
    {
        return $this->sendRequest('getDocument', ['Ref' => $ref,]);
    }

    /**
     * @param $params
     * @return mixed
     */
    public function generateReport(array $params): mixed
    {
        return $this->sendRequest('generateReport', $params);
    }

    /**
     * @param $citySender
     * @param $cityRecipient
     * @param $serviceType
     * @param $dateTime
     * @return mixed
     */
    public function getDocumentDeliveryDate($citySender, $cityRecipient, $serviceType, $dateTime): mixed
    {
        return $this->$this->sendRequest('getDocumentDeliveryDate', [
            'CitySender'    => $citySender,
            'CityRecipient' => $cityRecipient,
            'ServiceType'   => $serviceType,
            'DateTime'      => $dateTime,
        ]);
    }

    /**
     * @param string $citySender
     * @param string $cityRecipient
     * @param string $serviceType
     * @param float $weight
     * @param float $cost
     * @return mixed
     */
    public function getDocumentPrice(string $citySender, string $cityRecipient, string $serviceType, float $weight, float $cost): mixed
    {
        return $this->sendRequest('getDocumentPrice', [
            'CitySender'    => $citySender,
            'CityRecipient' => $cityRecipient,
            'ServiceType'   => $serviceType,
            'Weight'        => $weight,
            'Cost'          => $cost,
        ]);
    }

    // TODO: need refactor this method
    /**
     * Check required params for new InternetDocument and set defaults.
     *
     * @param array &$params
     */
    protected function checkInternetDocumentParams(array &$params)
    {
        if (!$params['Description']) {
            throw new \Exception('Description is required filed for new Internet document');
        }
        if (!$params['Weight']) {
            throw new \Exception('Weight is required filed for new Internet document');
        }
        if (!$params['Cost']) {
            throw new \Exception('Cost is required filed for new Internet document');
        }
        empty($params['DateTime']) and $params['DateTime'] = date('d.m.Y');
        empty($params['ServiceType']) and $params['ServiceType'] = 'WarehouseWarehouse';
        empty($params['PaymentMethod']) and $params['PaymentMethod'] = 'Cash';
        empty($params['PayerType']) and $params['PayerType'] = 'Recipient';
        empty($params['SeatsAmount']) and $params['SeatsAmount'] = '1';
        empty($params['CargoType']) and $params['CargoType'] = 'Cargo';
        if ($params['CargoType'] != 'Documents') {
            empty($params['VolumeGeneral']) and $params['VolumeGeneral'] = '0.0004';
            empty($params['VolumeWeight']) and $params['VolumeWeight'] = $params['Weight'];
        }
    }

    // TODO: throw the exception in a separate class
    /**
     * Check required fields for new InternetDocument and set defaults.
     *
     * @param array &$counterparty Recipient info array
     */
    protected function checkInternetDocumentRecipient(array &$counterparty)
    {
        // Check required fields
        if (!$counterparty['FirstName']) {
            throw new \Exception('FirstName is required filed for recipient');
        }
        // MiddleName realy is not required field, but manual says otherwise
        // if ( ! $counterparty['MiddleName'])
        // throw new \Exception('MiddleName is required filed for sender and recipient');
        if (!$counterparty['LastName']) {
            throw new \Exception('LastName is required filed for recipient');
        }
        if (!$counterparty['Phone']) {
            throw new \Exception('Phone is required filed for recipient');
        }
        if (!($counterparty['City'] or $counterparty['CityRef'])) {
            throw new \Exception('City is required filed for recipient');
        }
        if (!($counterparty['Region'] or $counterparty['CityRef'])) {
            throw new \Exception('Region is required filed for recipient');
        }

        // Set defaults
        if (empty($counterparty['CounterpartyType'])) {
            $counterparty['CounterpartyType'] = 'PrivatePerson';
        }
    }

    private function init() {
        $this->counterparty = new Counterparty($this->option);
        $this->address = new Address($this->option);
    }
}