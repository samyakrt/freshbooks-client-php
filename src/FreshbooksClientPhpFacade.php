<?php

namespace Sabinks\FreshbooksClientPhp;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Sabinks\FreshbooksClientPhp\Skeleton\SkeletonClass
 */
class FreshbooksClientPhpFacade extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'freshbooks-client-php';
    }
}
