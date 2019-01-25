<?php
/**
 * Created by PhpStorm.
 * User: siim
 * Date: 25.01.19
 * Time: 6:58
 */

namespace Sf4\ApiSecurity\Entity;

use Sf4\ApiUser\Entity\Traits\UserTrait;
use Symfony\Component\Security\Core\User\UserInterface;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="Sf4\ApiSecurity\Repository\UserRepository")
 * @ORM\Table(indexes={
 *     @ORM\Index(name="email_idx", columns={"email"})
 * })
 */
class User implements UserInterface
{

    use UserTrait;

    /**
     * @see UserInterface
     */
    public function getSalt()
    {

    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials()
    {

    }
}
