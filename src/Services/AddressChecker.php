<?php

namespace okpt\furnics\project\Services;

use okpt\furnics\project\Util\EUCountries;

class AddressChecker
{
    public function isEU($address): bool
    {
        if (!isset($address)) {
            throw new \InvalidArgumentException('Address must contain a country.');
        }

        $euCountries = EUCountries::getEUCountries();
        return in_array($address, $euCountries, true);
    }
}