<?php
/**
 * Created by PhpStorm.
 * User: Michal Kolář
 * Date: 24. 4. 2019
 * Time: 10:13
 */

namespace Asyf\ApiBundle\Event;

use Symfony\Component\EventDispatcher\Event;

class ApiEvent extends Event
{
    const API_EVENT__PRE_NORMALIZE = 'asyf.api.event.pre_normalize';
    const API_EVENT__POST_NORMALIZE = 'asyf.api.event.post_normalize';
}