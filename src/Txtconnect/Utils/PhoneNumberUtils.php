<?php

namespace Prinx\Txtconnect\Utils;

use libphonenumber\NumberParseException;
use libphonenumber\PhoneNumberFormat;
use libphonenumber\PhoneNumberType;
use libphonenumber\PhoneNumberUtil;

class PhoneNumberUtils
{
    const CONTACT_STRING_SEPERATOR = ',';
    const SMS = 'sms';
    const VOICE = 'voice';
    const PLUS = '+';

    /**
     * @var \libphonenumber\PhoneNumberUtil
     */
    protected static $phoneNumberLib;

    /**
     * Map each contact with it country code. Map to NULL if invalid contact.
     *
     * @param string[] $contacts
     *
     * @return array
     */
    public static function getContactsWithCode($contacts)
    {
        if (!is_array($contacts)) {
            throw new \Exception('Getting country code from contacts. Parameter contacts must be an array. Got '.gettype($contacts));
        }

        $contactsWithCode = [];
        $phoneNumbers = $contacts;
        $phoneNumberLib = static::getLib();

        foreach ($phoneNumbers as $contact) {
            $phoneNumberInstance = $phoneNumberLib->parse(static::ensurePlus($contact), null);
            $isValidNumber = static::isValidNumber($phoneNumberInstance);

            $contactsWithCode[$contact] = $isValidNumber ? $phoneNumberInstance->getCountryCode() : null;
        }

        return $contactsWithCode;
    }

    /**
     * Check a number is internationally valid, irrespective of the country.
     *
     * @param string|\libphonenumber\PhoneNumber $number
     *
     * @return boolean
     */
    public static function isValidNumber($number, string $region = null)
    {
        try {
            return static::getLib()->isValidNumber(static::parse($number, $region));
        } catch (NumberParseException $th) {
            return false;
        }
    }

    /**
     * Make the number a phonenumber instance.
     * If no region is specified, the number must be in international format starting with '+'.
     *
     * @param string|\libphonenumber\PhoneNumber $number
     *
     * @return \libphonenumber\PhoneNumber
     */
    public static function parse($number, string $region = null)
    {
        if (!$region && is_string($number)) {
            $number = static::ensurePlus($number);
        } elseif (is_string($region)) {
            $region = strtoupper($region);
        }

        if (is_string($number)) {
            $phoneNumberLib = static::getLib();
            $number = $phoneNumberLib->parse($number, $region);
        }

        return $number;
    }

    /**
     * Check if a phone number can receive SMS.
     *
     * @param string|\libphonenumber\PhoneNumber $number
     *
     * @return boolean
     */
    public static function canReceiveSms($number, string $region = null)
    {
        $number = static::parse($number, $region);

        return in_array(static::getLib()->getNumberType($number), [
            PhoneNumberType::MOBILE, PhoneNumberType::FIXED_LINE_OR_MOBILE,
        ]);
    }

    /**
     * Check if a phone number can receive an international call.
     *
     * @param string|\libphonenumber\PhoneNumber $number
     *
     * @return boolean
     */
    public static function canReceiveVoice($number, string $region = null)
    {
        return static::getLib()->canBeInternationallyDialled(static::parse($number, $region));
    }

    /**
     * Check if a phone number can receive an international call.
     * Alias for `canReceiveVoice`.
     *
     * @param string|\libphonenumber\PhoneNumber $number
     *
     * @return boolean
     */
    public static function canReceiveCall($number, string $region = null)
    {
        return static::canReceiveVoice($number, $region);
    }

    /**
     * Get country code of this phone number.
     *
     * @param string|\libphonenumber\PhoneNumber $number
     * @param string                             $region
     *
     * @return int|null The country code, or null if not set.
     */
    public static function getCountryCode($number, string $region = null)
    {
        return static::parse($number, $region)->getCountryCode();
    }

    /**
     * Get number with the format +{countryCode}{Number} (Eg: +233545454545, for a Ghanian number).
     *
     * @param string|\libphonenumber\PhoneNumber $number
     *
     * @return string The formatted number
     */
    public static function formatE164($number, string $region = null)
    {
        return self::getLib()->format(static::parse($number, $region), PhoneNumberFormat::E164);
    }

    /**
     * Get number with the format +{countryCode}{Number} (Eg: +233545454545, for a Ghanian number).
     * Alias for `formatE164($number, $region)`.
     *
     * @param string|\libphonenumber\PhoneNumber $number
     *
     * @return string The formatted number
     */
    public static function toInternational($number, string $region = null)
    {
        return self::formatE164($number, $region);
    }

    /**
     * libphonenumber\PhoneNumberUtil instance.
     *
     * Eg usage:
     *
     * $lib = PhoneNumberUtils::getLib();
     *
     * $number = $lib->parse('+233545454545', null);
     *
     * $lib->isValidNumber($number); // true
     * $lib->canBeInternationallyDialled($number); // true
     *
     * $countryCode = $number->getCountryCode(); // 233
     * $regionCode = $lib->getRegionCodeForNumber($number); // "GH"
     *
     * @return \libphonenumber\PhoneNumberUtil
     */
    public static function getLib()
    {
        if (is_null(static::$phoneNumberLib)) {
            static::$phoneNumberLib = PhoneNumberUtil::getInstance();
        }

        return static::$phoneNumberLib;
    }

    public static function ensurePlus(string $number)
    {
        if (!str_starts_with($number, self::PLUS)) {
            return self::PLUS.$number;
        }

        return $number;
    }

    public static function removePlus($number)
    {
        return trim($number, self::PLUS);
    }

    /**
     * Returns an array of contacts from an array or a sign-separated string list of contacts. The separator is defined by the CONTACT_STRING_SEPERATOR constant of this class.
     * Any character which is not a number or is not the separator, the plus sign, or a parenthesis,
     * will be remove.
     *
     * Eg:
     *
     * $contacts = "+233(0)54 54 54 554, +233 45-4545-445    ,  + 233 44 55;44 555";
     *
     * Will be purified to the array:
     *
     * [
     *     "+233545454554",
     *     "+233454545445",
     *     "+233445544555",
     * ]
     *
     * @param string[]|string $contacts
     * @param bool            $removeDuplicate
     * @param bool            $removeEmpty
     *
     * @return array
     */
    public static function purify($contacts, $removeDuplicate = true, $removeEmpty = true)
    {
        if (is_array($contacts)) {
            $contactsString = implode(self::CONTACT_STRING_SEPERATOR, $contacts);
        } elseif (is_string($contacts)) {
            $contactsString = $contacts;
        } else {
            throw new \InvalidArgumentException('Invalid contacts type. Only array or string supported.');
        }

        $contactsString = preg_replace('/[^0-9,+)(]|\(.+?\)/', '', $contactsString);

        $contacts = static::toArray($contactsString);

        $removeEmpty && $contacts = static::removeEmpty($contacts);

        $removeDuplicate && $contacts = static::removeDuplicate($contacts);

        return $contacts;
    }

    public static function toArray($contactsString)
    {
        return explode(static::CONTACT_STRING_SEPERATOR, $contactsString);
    }

    public static function removeEmpty($contacts)
    {
        return array_filter($contacts);
    }

    public static function removeDuplicate($contacts)
    {
        return array_unique($contacts, SORT_REGULAR);
    }
}
