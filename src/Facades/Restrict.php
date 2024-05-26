<?php

namespace Doefom\Restrict\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @mixin \Doefom\Restrict\Restrict
 */
class Restrict extends Facade
{

    protected static function getFacadeAccessor(): string
    {
        return 'restrict';
    }

}
