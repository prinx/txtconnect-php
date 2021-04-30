<?php

namespace Prinx\Txtconnect;

use Prinx\Txtconnect\Abstracts\SmsStatusAbstract;

class SmsStatus extends SmsStatusAbstract
{
    protected $batchNumbers = [];
    protected $statuses = [];

    public function fetch($batchNumber = '')
    {
        $this->of($batchNumber);

        $responses = [];
        $options = $this->defaultOptions();

        foreach ($this->batchNumbers as $batchNumber) {
            $options['user_data'] = $batchNumber;
            $responses[] = $this->request(self::endpoint(), $options);
        }

        foreach ($this->client()->stream($responses) as $response => $chunk) {
            if ($chunk->isLast()) {
                $batchNumber = $response->getInfo('user_data');
                $this->statuses[$batchNumber] = $response->toArray();
            }
        }

        return $this;
    }

    public function of($batchNumber)
    {
        if ($batchNumber) {
            $this->batchNumbers = array_merge($this->batchNumbers, $batchNumber);
        }

        return $this;
    }

    public function all()
    {
        return $this->statuses;
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
