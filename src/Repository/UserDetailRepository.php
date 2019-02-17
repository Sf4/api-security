<?php
/**
 * Created by PhpStorm.
 * User: siim
 * Date: 16.02.19
 * Time: 17:09
 */

namespace Sf4\ApiSecurity\Repository;

use Sf4\ApiSecurity\Entity\User;
use Sf4\ApiSecurity\Entity\UserDetail;

class UserDetailRepository extends \Sf4\ApiUser\Repository\UserDetailRepository
{
    protected function getEntityClass(): string
    {
        return UserDetail::class;
    }

    protected function getUserEntityClass(): string
    {
        return User::class;
    }
}
