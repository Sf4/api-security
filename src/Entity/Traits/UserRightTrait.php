<?php
/**
 * Created by PhpStorm.
 * User: siim
 * Date: 15.02.19
 * Time: 9:44
 */

namespace Sf4\ApiSecurity\Entity\Traits;

use Sf4\Api\Entity\Traits\CodeTrait;
use Sf4\Api\Entity\Traits\EntityIdTrait;
use Sf4\Api\Entity\Traits\StatusTrait;
use Sf4\ApiUser\Entity\Traits\TimestampableTrait;

trait UserRightTrait
{
    use EntityIdTrait;
    use TimestampableTrait;
    use StatusTrait;
    use CodeTrait;

    /** @var string $site */
    protected $site;

    /**
     * @return string
     */
    public function getSite(): string
    {
        return $this->site;
    }

    /**
     * @param string $site
     */
    public function setSite(string $site): void
    {
        $this->site = $site;
    }
}
