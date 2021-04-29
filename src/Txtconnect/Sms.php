<?php

namespace Prinx\Txtconnect;

use Prinx\Txtconnect\Abstracts\SmsAbstract;
use Prinx\Txtconnect\Contracts\SmsResponseBagInterface;
use Prinx\Txtconnect\Exceptions\InvalidApiKeyException;
use Prinx\Txtconnect\Exceptions\InvalidHttpMethodException;
use Prinx\Txtconnect\Exceptions\InvalidSenderNameException;
use Prinx\Txtconnect\Utils\PhoneNumberUtils;
use Symfony\Component\HttpClient\HttpClient;
use function Prinx\Dotenv\env;
use libphonenumber\NumberParseException;

class Sms extends SmsAbstract
{
    protected $defaultCountry = null;
    protected $defaultCountryCode = null;
    protected $supportedMethods = ['GET', 'POST'];
    protected $endpoint = 'https://txtconnect.net/sms/api?action=send-sms';
    protected $phones = [];
    protected $removeDuplicate = true;
    protected $isUnicode = false;
    protected $sent = [];
    protected $timeout = null;

    const ENV_PREFIX = 'TXTCONNECT';
    const INVALID_NUMBER = 1;
    const CANNOT_RECEIVE_SMS = 2;
    const UNSUPPORTED_NUMBERS = [
        self::INVALID_NUMBER,
        self::CANNOT_RECEIVE_SMS,
    ];

    /**
     * {@inheritdoc}
     */
    public function send($sms, $phone = null, string $method = 'POST'): SmsResponseBagInterface
    {
        if ($phone) {
            $this->to($phone);
        }

        $numbers = $this->getParsedPhones();
        $originalNumbers = $this->phones;

        try {
            $method = $this->validateMethod($method);
            $params = $this->prepareParams($sms);
        } catch (\Throwable $th) {
            return new SmsResponseBag([
                'success' => false,
                'error' => $th->getMessage(),
                'numbers' => $numbers,
                'originalNumbers' => $originalNumbers,
            ]);
        }

        $httpClient = HttpClient::create();
        $paramsType = $method === 'POST' ? 'json' : 'query';

        $smsResponses = [
            'success' => true,
            'numbers' => $numbers,
            'originalNumbers' => $originalNumbers,
        ];

        $responses = [];

        foreach ($numbers as $number => $parsed) {
            if ($this->removeDuplicate && in_array($number, $this->sent)) {
                continue;
            }

            if (in_array($parsed, self::UNSUPPORTED_NUMBERS)) {
                $smsResponses['data'][$number] = new SmsResponse($parsed, $number, $parsed);
                continue;
            }

            $params['to'] = $parsed;
            $options = [
                $paramsType => $params,
                'user_data' => [$number, $parsed],
            ];

            if (is_numeric($this->timeout)) {
                $options['timeout'] = $this->timeout;
            }

            $responses[] = $httpClient->request($method, $this->endpoint, $options);

            $this->sent[] = $number;
        }

        foreach ($httpClient->stream($responses) as $response => $chunk) {
            if ($chunk->isLast()) {
                [$number, $parsed] = $response->getInfo('user_data');
                $smsResponses['data'][$number] = new SmsResponse($response, $number, $parsed);
            }
        }

        return new SmsResponseBag($smsResponses);
    }

    /**
     * Validate and return method name.
     *
     * @param string $method
     *
     * @return string
     *
     * @throws InvalidHttpMethodException
     */
    public function validateMethod($method)
    {
        $method = strtoupper($method);

        if (!in_array($method, $this->supportedMethods)) {
            throw new InvalidHttpMethodException('Invalid HTTP method.');
        }

        return $method;
    }

    /**
     * Params.
     *
     * @param string[]|string $sms
     *
     * @return array
     */
    public function prepareParams($sms)
    {
        return [
            'api_key' => $this->getApiKey(),
            'from' => $this->getFrom(),
            'sms' => $this->getSmsString($sms),
            'response' => 'json',
            'unicode' => $this->getIsUnicode(),
        ];
    }

    /**
     * Get Api Key.
     *
     * @return string
     *
     * @throws InvalidApiKeyException
     */
    public function getApiKey()
    {
        $key = $this->apiKey ?: env($this->envPrefix.'_KEY');

        if (!$key) {
            throw new InvalidApiKeyException('No sender name defined.');
        }

        return $key;
    }

    /**
     * Get Sender name that will appear on receiver's phone.
     *
     * @return string
     *
     * @throws InvalidSenderNameException
     */
    public function getFrom()
    {
        $from = $this->from ?: env($this->envPrefix.'_FROM');

        if (!$from) {
            throw new InvalidSenderNameException('No sender name defined.');
        }

        return $from;
    }

    /**
     * Get Sms as string.
     *
     * @param string[]|string|stringable $sms
     *
     * @return string
     *
     * @throws \InvalidArgumentException
     */
    public function getSmsString($sms)
    {
        if (is_array($sms)) {
            return implode(PHP_EOL, $sms);
        }

        if (is_object($sms) && method_exists($sms, '__toString')) {
            return $sms->__toString();
        }

        if (is_string($sms)) {
            return $sms;
        }

        throw new \InvalidArgumentException('Invalid sms type. Only string, array or stringifiable object supported.');
    }

    /**
     * @return bool
     */
    public function getIsUnicode()
    {
        return $this->isUnicode;
    }

    public function from(string $from)
    {
        $this->from = $from;

        return $this;
    }

    /**
     * Set phone numbers to send SMS to.
     *
     * @param string[]|string $phones
     *
     * @return $this
     */
    public function to($phones)
    {
        if (!is_string($phones) && !is_array($phones)) {
            throw new \InvalidArgumentException('Invalid phone number type. Only string or array supported. Got '.gettype($phones));
        }

        if (!is_array($phones)) {
            $phones = [$phones];
        }

        $this->phones = array_merge($this->phones, $phones);

        return $this;
    }

    public function keepDuplicate()
    {
        $this->removeDuplicate = false;

        return $this;
    }

    public function removeDuplicate()
    {
        $this->removeDuplicate = true;

        return $this;
    }

    public function getPhones()
    {
        return $this->phones;
    }

    public function getParsedPhones()
    {
        $phones = $this->removeDuplicate ? array_unique($this->phones, SORT_REGULAR) : $this->phones;

        $parsed = array_map(function ($phone) {
            try {
                $phone = PhoneNumberUtils::parse($phone, $this->defaultCountry);
            } catch (NumberParseException $th) {
                return self::INVALID_NUMBER;
            }

            if (PhoneNumberUtils::isValidNumber($phone)) {
                return self::INVALID_NUMBER;
            }

            if (PhoneNumberUtils::canReceiveSms($phone)) {
                return self::CANNOT_RECEIVE_SMS;
            }

            return PhoneNumberUtils::removePlus(PhoneNumberUtils::formatE164($phone));
        }, $phones);

        $parsed = PhoneNumberUtils::purify($parsed, false);

        return array_combine($phones, $parsed);
    }

    /**
     * Set default country to use for the numbers.
     *
     * @return $this
     */
    public function country(string $code)
    {
        $this->defaultCountry = $code;

        return $this;
    }

    public function asUnicode()
    {
        $this->isUnicode = true;

        return $this;
    }

    public function asPlainText()
    {
        $this->isUnicode = false;

        return $this;
    }

    public function env(string $prefix)
    {
        $this->envPrefix = $prefix;

        return $this;
    }

    public function withDefaultEnv()
    {
        $this->envPrefix = self::ENV_PREFIX;

        return $this;
    }

    /**
     * Set timeout on the requests.
     *
     * @param int|float|string $timeout
     *
     * @return $this
     */
    public function setTimeout($timeout)
    {
        if (!is_numeric($timeout) || floatval($timeout) < 0) {
            throw new \InvalidArgumentException('Invalid timeout.');
        }

        $this->timeout = floatval($timeout);

        return $this;
    }
}
