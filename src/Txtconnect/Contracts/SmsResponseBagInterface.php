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
     * @return \Prinx\Txtconnect\SmsResponse|null
     */
    public function first();

    /**
     * Last Sms processed response.
     *
     * @return \Prinx\Txtconnect\SmsResponse|null
     */
    public function last();

    /**
     * Get response for specified number.
     *
     * @param string|null $number
     *
     * @return \Prinx\Txtconnect\SmsResponse
     */
    public function get($number = null);

    /**
     * Responses.
     *
     * @return array
     */
    public function getResponses();

    /**
     * Actual numbers SMS was sent to. This might be different from the
     * original numbers because the numbers are sanitize before SMS is sent.
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
}
