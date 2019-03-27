<?php
/**
 * Created by PhpStorm.
 * User: siim
 * Date: 25.02.19
 * Time: 8:52
 */

namespace Sf4\ApiSecurity\Entity\Traits;

trait SiteTrait
{
    /** @var string|null $site */
    protected $site;

    /**
     * @return string|null
     */
    public function getSite(): ?string
    {
        return $this->site;
    }

    /**
     * @param string|null $site
     */
    public function setSite(?string $site): void
    {
        $this->site = $site;
    }
}
