<?php

namespace Prinx\Txtconnect;

use Prinx\Txtconnect\Abstracts\SmsAbstract;
use Prinx\Txtconnect\Contracts\SmsResponseBagInterface;
use Prinx\Txtconnect\Exceptions\InvalidSenderNameException;
use Prinx\Txtconnect\Utils\PhoneNumberUtils;
use function Prinx\Dotenv\env;
use libphonenumber\NumberParseException;

class Sms extends SmsAbstract
{
    const INVALID_NUMBER = 1;
    const CANNOT_RECEIVE_SMS = 2;
    const UNSUPPORTED_NUMBERS = [
        self::INVALID_NUMBER,
        self::CANNOT_RECEIVE_SMS,
    ];

    protected $defaultCountry = null;
    protected $defaultCountryCode = null;
    protected $phones = [];
    protected $removeDuplicate = true;
    protected $isUnicode = false;
    protected $sent = [];
    protected $from = null;

    /**
     * {@inheritdoc}
     */
    public function send($sms, $phone = null, string $method = 'POST'): SmsResponseBagInterface
    {
        if ($phone) {
            $this->to($phone);
        }

        $this->via($method);

        $numbers = $this->getParsedNumbers();
        $params = $this->prepareParams($sms);

        $smsResponses = [
            'success' => true,
            'numbers' => $numbers,
            'originalNumbers' => $this->phones,
        ];

        $responses = [];
        $paramsType = $this->requestType();

        foreach ($numbers as $number => $parsed) {
            if ($this->removeDuplicate && in_array($number, $this->sent)) {
                continue;
            }

            if (in_array($parsed, self::UNSUPPORTED_NUMBERS, true)) {
                $smsResponses['data'][$number] = new SmsResponse($parsed, $number, $parsed);
                continue;
            }

            $params['to'] = $parsed;
            $options = [
                $paramsType => $params,
                'user_data' => [$number, $parsed],
            ];

            $responses[] = $this->request(self::endpoint(), $options);

            $this->sent[] = $number;
        }

        foreach ($this->client()->stream($responses) as $response => $chunk) {
            if ($chunk->isLast()) {
                [$number, $parsed] = $response->getInfo('user_data');
                $smsResponses['data'][$number] = new SmsResponse($response, $number, $parsed);
            }
        }

        return new SmsResponseBag($smsResponses);
    }

    /**
     * Params.
     *
     * @param string[]|string $sms
     *
     * @return array
     */
    public function prepareParams($sms = '')
    {
        $smsParams = [
            'from' => $this->getFrom(),
            'sms' => $this->getSmsString($sms),
            'unicode' => intval($this->getIsUnicode()),
        ];

        return array_replace($this->defaultParams(), $smsParams);
    }

    /**
     * Get SMS endpoint.
     *
     * @return string
     */
    public static function endpoint()
    {
        return Endpoint::sms();
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

    public function getParsedNumbers()
    {
        $originalNumbers = $this->removeDuplicate ? array_unique($this->phones, SORT_REGULAR) : $this->phones;

        $parsed = array_map(function ($phone) {
            try {
                $phone = PhoneNumberUtils::parse($phone, $this->defaultCountry);
            } catch (NumberParseException $th) {
                return self::INVALID_NUMBER;
            }

            if (!PhoneNumberUtils::isValidNumber($phone)) {
                return self::INVALID_NUMBER;
            }

            if (!PhoneNumberUtils::canReceiveSms($phone)) {
                return self::CANNOT_RECEIVE_SMS;
            }

            return PhoneNumberUtils::removePlus(PhoneNumberUtils::formatE164($phone));
        }, $originalNumbers);

        $parsed = PhoneNumberUtils::purify($parsed, false);

        return array_combine($originalNumbers, $parsed);
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
}
