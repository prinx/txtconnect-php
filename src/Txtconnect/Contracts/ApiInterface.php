<?php

namespace Prinx\Txtconnect\Contracts;

interface ApiInterface
{
    /**
     * Specify the HTTP verb to use to send the request.
     *
     * Only POST and GET supported.
     *
     * @return $this
     */
    public function via(string $method);
}
