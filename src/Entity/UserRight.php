<?php
/**
 * Created by PhpStorm.
 * User: siim
 * Date: 15.02.19
 * Time: 8:43
 */

namespace Sf4\ApiSecurity\Entity;

use Doctrine\ORM\Mapping as ORM;
use Sf4\Api\Entity\EntityInterface;
use Sf4\ApiSecurity\Entity\Traits\UserRightTrait;

/**
 * @ORM\Entity(repositoryClass="Sf4\ApiSecurity\Repository\UserRightRepository")
 */
class UserRight implements EntityInterface, UserRightInterface
{
    use UserRightTrait;

    public static $superAdminRights = [
        UserRightInterface::RIGHT_API_SITE
    ];

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    protected $code;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    protected $status;
}
