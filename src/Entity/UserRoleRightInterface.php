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
     * @return int
     */
    public function getRole(): int;

    /**
     * @param int $role
     */
    public function setRole(int $role): void;

    /**
     * @return int
     */
    public function getRight(): int;

    /**
     * @param int $right
     */
    public function setRight(int $right): void;
}
