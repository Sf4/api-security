<?php
/**
 * Created by PhpStorm.
 * User: siim
 * Date: 25.01.19
 * Time: 6:58
 */

namespace Sf4\ApiSecurity\Entity;

use Sf4\Api\Entity\EntityInterface;
use Sf4\ApiUser\Entity\Traits\UserTrait;
use Sf4\ApiUser\Entity\UserFieldsInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="Sf4\ApiSecurity\Repository\UserRepository")
 */
class User implements EntityInterface, UserFieldsInterface, UserInterface, \Sf4\ApiUser\Entity\UserInterface
{
    use UserTrait;

    /**
     * @ORM\ManyToOne(targetEntity="Sf4\ApiSecurity\Entity\UserDetail", cascade={"persist"})
     */
    protected $userDetail;

    /**
     * @see UserInterface6
     */
    public function getSalt()
    {
        return 'xTy9g4eR2';
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials()
    {
        $this->status = $this->getStatus();
    }
}
