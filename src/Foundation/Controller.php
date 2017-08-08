<?php

namespace Awok\Foundation;

use Awok\Foundation\Traits\ServesFeaturesTrait;
use Laravel\Lumen\Routing\Controller as BaseController;

class Controller extends BaseController
{
    use ServesFeaturesTrait;
}
