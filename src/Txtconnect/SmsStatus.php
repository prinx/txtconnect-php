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
                $this->raw[$key] = $response->toArray();
            }
        }

        return $this;
    }

    public function options($batchNumber, $index)
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
     * @return SmsMessage|array|mixed
     */
    public function get(string $batchNumber, string $key = '')
    {
        $index = array_search($batchNumber, $this->sent, true);

        if ($index === false) {
            throw new UndefinedSmsMessageException($batchNumber);
        }

        return $this->nth($index, $key);
    }

    /**
     * {@inheritdoc}
     */
    public function nth($index = null, $key = '')
    {
        if ($this->isDuplicate($index)) {
            $index = $this->getDuplicateRepresentantIndex($index);
        }

        return parent::nth($index, $key);
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
