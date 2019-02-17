<?php
/**
 * Created by PhpStorm.
 * User: siim
 * Date: 25.01.19
 * Time: 8:27
 */

namespace Sf4\ApiSecurity\Repository;

use Doctrine\ORM\NonUniqueResultException;
use Sf4\ApiSecurity\Entity\User;
use Symfony\Component\Security\Core\User\UserInterface;

class UserRepository extends \Sf4\ApiUser\Repository\UserRepository
{

    protected function getEntityClass(): string
    {
        return User::class;
    }

    public function getUserByToken(string $token): ?UserInterface
    {
        $qb = $this->createQueryBuilder('main');

        $qb->where(
            $qb->expr()->eq('main.api_token', ':token')
        );
        $qb->setParameter(':token', $token);

        $qb->andWhere(
            $qb->expr()->isNull('main.deleted_at')
        );

        try {
            return $qb->getQuery()->getOneOrNullResult();
        } catch (NonUniqueResultException $e) {
            return null;
        }
    }
}
