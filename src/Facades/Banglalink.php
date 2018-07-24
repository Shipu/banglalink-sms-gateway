<?php

namespace Shipu\BanglalinkSmsGateway\Facades;

use Illuminate\Support\Facades\Facade;

class Banglalink extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'banglalink';
    }
}