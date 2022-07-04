<?php


namespace LisDev\Delivery;


use BaseModel;

class TrackingDocument extends BaseModel
{
    protected string $model = 'TrackingDocument';

    /**
     * Get tracking information by track number.
     *
     * @param string $track Track number
     *
     * @return mixed
     */
    public function documentsTracking(string $track): mixed
    {
        return $this->sendRequest('getStatusDocuments', ['Documents' => [['DocumentNumber' => $track]]]);
    }
}