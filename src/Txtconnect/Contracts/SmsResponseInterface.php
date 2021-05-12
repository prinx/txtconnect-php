<?php

namespace Prinx\Txtconnect\Contracts;

interface SmsResponseInterface
{
    /**
     * Has SMS been forwarded to Telco successfully for delivering onto the user's phone?
     *
     * This does not mean the user has received the SMS. The reception of SMS by user still depends
     * on the availability of the Telco processing the SMS.
     *
     * To know if the user has actually received the SMS, you need to check the status of the SMS.
     *
     * @return bool
     */
    public function isOk();

    /**
     * API code of the SmsResponse.
     *
     * @return string|int|null
     */
    public function getCode();

    /**
     * Batch number to which belong the SMS.
     * Returns null if an error happened and SMS did not reach TxtConnect for processing.
     *
     * @return string|null
     */
    public function getBatchNumber();

    /**
     * User name of the account used to send the SMS.
     * Returns null if an error happened and SMS did not reach TxtConnect for processing.
     *
     * @return string|null
     */
    public function getUserName();

    /**
     * URL to check the status of this SMS.
     * Check if user has received SMS.
     * Returns null if an error happened and SMS did not reach TxtConnect for processing.
     *
     * @return string|null
     */
    public function getStatusCheckUrl();

    /**
     * Available balance after SMS has been sent.
     * Returns null if an error happened and SMS did not reach TxtConnect for processing.
     *
     * @return int|null
     */
    public function getBalance();

    /**
     * Message attached to the status of the SMS request.
     *
     * @return string|null
     */
    public function getMessage();

    /**
     * Error of the SMS request.
     *
     * @return string|null
     */
    public function getError();

    /**
     * Actual number used to send the SMS.
     *
     * @return string
     */
    public function getParsedNumber();

    /**
     * Original number user sent the SMS to.
     *
     * @return string
     */
    public function getOriginalNumber();

    /**
     * Raw response of the request for this SMS.
     *
     * @return string
     */
    public function getRawResponse();

    /**
     * Get full response body or key of body.
     *
     * @param string|null $key
     * @param mixed|null  $default
     *
     * @return mixed
     */
    public function getResponse($key = null, $default = null);
}
