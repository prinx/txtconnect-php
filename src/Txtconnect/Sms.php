<?php

namespace Prinx\Txtconnect;

use Prinx\Txtconnect\Abstracts\SmsAbstract;
use Prinx\Txtconnect\Exceptions\InvalidSenderNameException;
use Prinx\Txtconnect\Lib\Endpoint;
use Prinx\Txtconnect\Lib\PhoneNumber;
use Prinx\Txtconnect\Lib\SmsResponse;
use Prinx\Txtconnect\Lib\SmsResponseBag;
use function Prinx\Dotenv\env;
use libphonenumber\NumberParseException;

class Sms extends SmsAbstract
{
    protected $defaultCountry = null;
    protected $defaultCountryCode = null;
    protected $phones = [];
    protected $removeDuplicate = true;
    protected $isUnicode = false;
    protected $sent = [];
    protected $processed = [];
    protected $from = null;
    protected $sendAsBag = false;

    /**
     * {@inheritdoc}
     */
    public function send($sms, $phone = null, string $method = 'POST')
    {
        if ($phone) {
            $this->to($phone);
        }

        $this->via($method);

        $numberMap = $this->mapOriginalNumbersToParsed();
        $params = $this->prepareParams($sms);

        $smsResponses = [];
        $responses = [];
        $paramsType = $this->requestType();

        foreach ($numberMap as $original => $parsed) {
            if ($this->removeDuplicate && $mainDuplicate = array_search($parsed, $this->sent)) {
                $smsResponses[$original] = $smsResponses[$mainDuplicate];
                continue;
            }

            if (in_array($parsed, PhoneNumber::UNSUPPORTED_NUMBERS, true)) {
                $error = $this->getUnsupportedNumberError($parsed);
                $smsResponses[$original] = new SmsResponse($error, $params['sms'], $original, $parsed);
                $this->processed[$original] = $parsed;
                continue;
            }

            $params['to'] = $parsed;
            $options = [
                $paramsType => $params,
                'user_data' => [$original, $parsed],
            ];

            $responses[] = $this->request(self::endpoint(), $options);

            $this->sent[$original] = $parsed;
            $this->processed[$original] = $parsed;
        }

        $isBeingProcessed = false;

        foreach ($this->client()->stream($responses) as $response => $chunk) {
            if ($chunk->isLast()) {
                [$originalNumber, $parsedNumber] = $response->getInfo('user_data');
                $smsResponses[$originalNumber] = new SmsResponse($response, $params['sms'], $originalNumber, $parsedNumber);
                $isBeingProcessed = true;
            }
        }

        // If only one SMS sent, return directly the SmsResponse instead of a SmsResponseBag
        if (!$this->sendAsBag && count($this->processed) === 1) {
            $this->reInit();
    
            return current($smsResponses);
        }

        $this->reInit();

        return new SmsResponseBag($isBeingProcessed, $smsResponses, $numberMap);
    }

    public function reInit()
    {
        // Reinit the Sms instance fot it to be able to receive other contacts to send SMS to.
        $this->sent = [];
        $this->phones = [];
        $this->processed = [];
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

    public function getUnsupportedNumberError($number)
    {
        switch ($number) {
            case PhoneNumber::CANNOT_RECEIVE_SMS:
                return 'Number cannot receive SMS';
            default:
                return 'Invalid number';
        }
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
        $from = $this->from ?: env($this->envPrefix.'_SENDER_ID');

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

    public function mapOriginalNumbersToParsed()
    {
        $originalNumbers = $this->phones;

        $parsed = array_map(function ($phone) {
            return PhoneNumber::sanitize($phone, $this->defaultCountry);
        }, $originalNumbers);

        // Duplication is handled when sending the request.
        $parsed = PhoneNumber::purify($parsed, false, false);

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

    /**
     * If `true`, will a single message sent will be treated as an Sms bag and will return a
     * SmsResponseBag. Then you will need to access the SmsResponse via `$response->first()` or
     * `$response->get($number)`.
     * If `false`, an SMS sent to only one number will return a SmsResponse and an SMS sent to more
     * than one number will return a SmsResponseBag.
     *
     * @return $this
     */
    public function asBag(bool $sendAsBag = true)
    {
        $this->sendAsBag = $sendAsBag;

        return $this;
    }
}
