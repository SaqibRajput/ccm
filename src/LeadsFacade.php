<?php

namespace CCM\Leads;

use Illuminate\Support\Facades\Facade;

/**
 * @see \CCM\Leads\Skeleton\SkeletonClass
 */
class LeadsFacade extends Facade
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
