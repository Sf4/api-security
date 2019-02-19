<?php
/**
 * Created by PhpStorm.
 * User: siim
 * Date: 15.02.19
 * Time: 8:08
 */

namespace Sf4\ApiSecurity\Entity;

use Doctrine\ORM\Mapping as ORM;
use Sf4\Api\Entity\EntityInterface;
use Sf4\ApiSecurity\Entity\Traits\UserRoleTrait;

/**
 * @ORM\Entity(repositoryClass="Sf4\ApiSecurity\Repository\UserRoleRepository")
 */
class UserRole implements EntityInterface, UserRoleInterface
{

    use UserRoleTrait;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    protected $status;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    protected $name;

    /**
     * @ORM\Column(length=20, nullable=true)
     */
    protected $code;
}
