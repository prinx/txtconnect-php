<?php

namespace Prinx\Txtconnect\Contracts;

interface BalanceInterface extends ApiInterface
{
    /**
     * Fetch the balance information from API.
     *
     * @return $this
     */
    public function fetch();

    /**
     * Amount.
     *
     * @return int
     */
    public function amount();

    /**
     * User name of the account.
     *
     * @return string
     */
    public function user();

    /**
     * Country of the user.
     *
     * @return string
     */
    public function country();

    /**
     * Get the key if balance already fetched or fetched balance then return key.
     *
     * @param string $key
     *
     * @return mixed
     */
    public function getOrRefresh($key = '');

    /**
     * Re-fetch balance the next time the user tries to get the balance value.
     *
     * @return $this
     */
    public function refresh();

    /**
     * Raw response.
     *
     * @return string|null
     */
    public function getRawResponse();
}
