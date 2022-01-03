<?php

namespace CCM\Leads\Facade;

use Illuminate\Support\Facades\Facade as LumenFacade;

/**
 * @see \CCM\Leads\Skeleton\SkeletonClass
 */
class Facade extends LumenFacade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'leads';
    }
}
