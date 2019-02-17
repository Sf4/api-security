<?php
/**
 * Created by PhpStorm.
 * User: siim
 * Date: 16.02.19
 * Time: 12:43
 */

namespace Sf4\ApiSecurity\Entity;

use Sf4\Api\Entity\EntityInterface;
use Sf4\ApiSecurity\Entity\Traits\UserRoleRightTrait;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="Sf4\ApiSecurity\Repository\UserRoleRightRepository")
 */
class UserRoleRight implements EntityInterface, UserRoleRightInterface
{
    use UserRoleRightTrait;

    /**
     * @ORM\ManyToOne(targetEntity="Sf4\ApiSecurity\Entity\UserRole", cascade={"persist"})
     */
    protected $role;

    /**
     * @ORM\ManyToOne(targetEntity="Sf4\ApiSecurity\Entity\UserRight", cascade={"persist"})
     */
    protected $right;
}
