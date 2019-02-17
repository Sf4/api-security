<?php
/**
 * Created by PhpStorm.
 * User: siim
 * Date: 16.02.19
 * Time: 12:48
 */

namespace Sf4\ApiSecurity\Entity\Traits;

use Sf4\Api\Entity\Traits\EntityIdTrait;

trait UserRoleRightTrait
{
    use EntityIdTrait;

    /** @var int $role */
    protected $role;

    /** @var int $right */
    protected $right;

    /**
     * @return int
     */
    public function getRole(): int
    {
        return $this->role;
    }

    /**
     * @param int $role
     */
    public function setRole(int $role): void
    {
        $this->role = $role;
    }

    /**
     * @return int
     */
    public function getRight(): int
    {
        return $this->right;
    }

    /**
     * @param int $right
     */
    public function setRight(int $right): void
    {
        $this->right = $right;
    }
}
