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
use Sf4\ApiUser\Entity\TimestampableInterface;

/**
 * @ORM\Entity(repositoryClass="Sf4\ApiSecurity\Repository\UserRightRepository")
 */
class UserRight implements EntityInterface, UserRightInterface, TimestampableInterface
{
    use UserRightTrait;

    public static $superAdminRights = [
        UserRightInterface::RIGHT_API_SITE
    ];

    public static $anonymousUserRights = [
        UserRightInterface::RIGHT_API_DEFAULT,
        UserRightInterface::RIGHT_API_SECURITY_GOOGLE_LOGIN
    ];

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    protected $code;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    protected $status;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    protected $site;
}
