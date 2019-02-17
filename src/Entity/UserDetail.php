<?php
/**
 * Created by PhpStorm.
 * User: siim
 * Date: 16.02.19
 * Time: 17:02
 */

namespace Sf4\ApiSecurity\Entity;

use Sf4\Api\Entity\EntityInterface;
use Sf4\Api\Entity\Traits\EntityIdTrait;
use Sf4\ApiUser\Entity\Traits\UserDetail\PublicTrait;
use Sf4\ApiUser\Entity\UserDetailFieldInterface;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="Sf4\ApiSecurity\Repository\UserDetailRepository")
 */
class UserDetail implements EntityInterface, UserDetailFieldInterface, \Sf4\ApiUser\Entity\UserDetailInterface
{
    use EntityIdTrait;
    use PublicTrait;

    /**
     * @ORM\Column(type="string", length=100, options={"default" : ""})
     */
    protected $firstName;

    /**
     * @ORM\Column(type="string", length=100, options={"default" : ""})
     */
    protected $lastName;

    /**
     * @ORM\Column(type="string", length=255, options={"default" : ""})
     */
    protected $avatar;
}
