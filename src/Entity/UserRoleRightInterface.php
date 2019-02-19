<?php
/**
 * Created by PhpStorm.
 * User: siim
 * Date: 16.02.19
 * Time: 12:49
 */

namespace Sf4\ApiSecurity\Entity;

interface UserRoleRightInterface
{
    /**
     * @return UserRole
     */
    public function getRole(): UserRole;

    /**
     * @param UserRole $role
     */
    public function setRole(UserRole $role): void;

    /**
     * @return UserRight
     */
    public function getRight(): UserRight;

    /**
     * @param UserRight $right
     */
    public function setRight(UserRight $right): void;
}
