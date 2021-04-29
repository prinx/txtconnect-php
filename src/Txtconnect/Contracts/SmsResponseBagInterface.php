<?php

namespace Prinx\Txtconnect\Contracts;

interface SmsResponseBagInterface
{
    /**
     * Get response for specified number.
     *
     * @param string|null $number
     *
     * @return SmsResponse
     */
    public function get($number = null);

    /**
     * First Sms processed response.
     *
     * @return SmsResponse|null
     */
    public function first();

    /**
     * Last Sms processed response.
     *
     * @return SmsResponse|null
     */
    public function last();

    /**
     * Responses.
     *
     * @return array
     */
    public function getResponses();

    /**
     * Have TXTCONNECT successfully received SMSes for processing?
     *
     * @return boolean
     */
    public function isBeingProcessed();

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
