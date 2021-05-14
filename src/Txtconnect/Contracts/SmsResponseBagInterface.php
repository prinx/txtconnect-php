<?php

namespace Prinx\Txtconnect\Contracts;

interface SmsResponseBagInterface
{
    /**
     * Have TXTCONNECT successfully received SMSes for processing?
     *
     * @return bool
     */
    public function isBeingProcessed();

    /**
     * First Sms processed response.
     *
     * @return \Prinx\Txtconnect\Contracts\SmsResponseInterface
     *
     * @throws \Prinx\Txtconnect\Exceptions\SmsResponseNotFoundException
     */
    public function first();

    /**
     * Last Sms processed response.
     *
     * @return \Prinx\Txtconnect\Contracts\SmsResponseInterface
     *
     * @throws \Prinx\Txtconnect\Exceptions\SmsResponseNotFoundException
     */
    public function last();

    /**
     * Get response for specified number.
     *
     * @return \Prinx\Txtconnect\Contracts\SmsResponseInterface
     *
     * @throws \Prinx\Txtconnect\Exceptions\SmsResponseNotFoundException
     */
    public function get(string $number);

    /**
     * Responses.
     *
     * @return array
     */
    public function getResponses();

    /**
     * Map of original numbers to parsed numbers.
     *
     * @return array
     */
    public function numbers();

    /**
     * Original numbers the developer sent the SMS to.
     *
     * @return array
     */
    public function originalNumbers();

    /**
     * Error if request is not being processed.
     *
     * @return string|null
     */
    public function getError();

    /**
     * Count the number of SmsResponse in the bag.
     *
     * @return int
     */
    public function count();
}
