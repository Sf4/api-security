<?php
/**
 * Created by PhpStorm.
 * User: siim
 * Date: 16.02.19
 * Time: 12:48
 */

namespace Sf4\ApiSecurity\Entity\Traits;

use Sf4\Api\Entity\Traits\EntityIdTrait;
use Sf4\ApiSecurity\Entity\UserRight;
use Sf4\ApiSecurity\Entity\UserRole;

trait UserRoleRightTrait
{
    use EntityIdTrait;

    /** @var UserRole $role */
    protected $role;

    /** @var UserRight $right */
    protected $right;

    /**
     * @return UserRole
     */
    public function getRole(): UserRole
    {
        return $this->role;
    }

    /**
     * @param UserRole $role
     */
    public function setRole(UserRole $role): void
    {
        $this->role = $role;
    }

    /**
     * @return UserRight
     */
    public function getRight(): UserRight
    {
        return $this->right;
    }

    /**
     * @param UserRight $right
     */
    public function setRight(UserRight $right): void
    {
        $this->right = $right;
    }
}
