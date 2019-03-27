<?php
/**
 * Created by PhpStorm.
 * User: siim
 * Date: 15.02.19
 * Time: 8:17
 */

namespace Sf4\ApiSecurity\Entity\Traits;

use Sf4\Api\Entity\Traits\CodeTrait;
use Sf4\Api\Entity\Traits\EntityIdTrait;
use Sf4\Api\Entity\Traits\NameTrait;
use Sf4\Api\Entity\Traits\StatusTrait;
use Sf4\ApiUser\Entity\Traits\TimestampableTrait;

trait UserRoleTrait
{
    use EntityIdTrait;
    use TimestampableTrait;
    use StatusTrait;
    use CodeTrait;
    use NameTrait;
    use SiteTrait;
}
