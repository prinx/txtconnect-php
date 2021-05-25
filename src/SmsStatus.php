<?php

namespace Prinx\Txtconnect;

use Prinx\Txtconnect\Abstracts\SmsStatusAbstract;
use Prinx\Txtconnect\Exceptions\UndefinedSmsMessageException;
use Prinx\Txtconnect\Lib\Endpoint;

class SmsStatus extends SmsStatusAbstract
{
    protected $batchNumbers = [];
    protected $duplicates = [];
    protected $sent = [];

    public function fetch($batchNumber = '')
    {
        $this->of($batchNumber);

        $responses = [];

        // Duplicates will help us not send duplicate when batch numbers repeat.
        $this->duplicates = [];
        $this->sent = [];

        foreach ($this->batchNumbers as $key => $batchNumber) {
            if (in_array($batchNumber, $this->sent, true)) {
                $this->duplicates[$key] = $batchNumber;
                continue;
            }

            $responses[] = $this->request(self::endpoint(), $this->options($batchNumber, $key));

            $this->sent[$key] = $batchNumber;
        }

        foreach ($this->client()->stream($responses) as $response => $chunk) {
            if ($chunk->isLast()) {
                $key = $response->getInfo('user_data');
                // Look at this at the API level. Should not have to do $response->toArray()[0], instead $response->toArray().
                $this->raw[$key] = $response->toArray()[0];
            }
        }

        return $this;
    }

    private function options($batchNumber, $index)
    {
        return [
            $this->requestType() => array_replace($this->defaultParams(), [
                'batch_no' => $batchNumber,
            ]),
            'user_data' => $index,
        ];
    }

    /**
     * Add batch number to fetch sms.
     *
     * @param string[]|string $batchNumbers
     *
     * @return $this
     */
    public function of($batchNumbers)
    {
        if (!$batchNumbers || (!is_iterable($batchNumbers) && !is_string($batchNumbers))) {
            return $this;
        }

        if (is_string($batchNumbers)) {
            $batchNumbers = [$batchNumbers];
        }

        foreach ($batchNumbers as $batchNum) {
            if (!is_string($batchNum)) {
                throw new \InvalidArgumentException('Invalid batch number detected.');
            }

            $this->batchNumbers[] = $batchNum;
        }

        return $this;
    }

    /**
     * Get the status corresponding to the batch number.
     *
     * If fetching status for only one SMS, you do not need to pass the batch number.
     *
     * @return SmsMessage
     */
    public function get(string $batchNumber = '')
    {
        if (is_null($this->raw)) {
            $this->fetch();
        }

        $count = count($this->sent);

        if (!$batchNumber && $count > 1) {
            throw new \InvalidArgumentException('The batch number of the Sms to choose must be specified when retrieving the status of more than one Sms.');
        }

        // If batch number not passed and fetching status for only one SMS, take the first Sms in the bag
        if (!$batchNumber && $count === 1) {
            $index = 0;
        } else {
            $index = array_search($batchNumber, $this->sent, true);
        }

        if ($index === false) {
            throw new UndefinedSmsMessageException($batchNumber);
        }

        return $this->nth($index);
    }

    /**
     * {@inheritdoc}
     */
    public function nth($index = null)
    {
        if ($this->isDuplicate($index)) {
            $index = $this->getDuplicateRepresentantIndex($index);
        }

        return parent::nth($index);
    }

    public function isDuplicate($index)
    {
        return array_key_exists($index, $this->duplicates);
    }

    public function getDuplicateRepresentantIndex($indexOfDuplicate)
    {
        $batchNumber = $this->duplicates[$indexOfDuplicate];

        return array_search($batchNumber, $this->sent, true);
    }

    /**
     * Sms status endpoint.
     *
     * @return string
     */
    public static function endpoint()
    {
        return Endpoint::status();
    }
}
