<?php
/**
 * Created by PhpStorm.
 * User: siim
 * Date: 25.01.19
 * Time: 8:27
 */

namespace Sf4\ApiSecurity\Repository;

use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\QueryBuilder;
use Sf4\ApiSecurity\Entity\User;
use Sf4\ApiSecurity\Entity\UserRoleInterface;
use Sf4\ApiUser\Entity\UserInterface;

class UserRepository extends \Sf4\ApiUser\Repository\UserRepository
{

    protected function getEntityClass(): string
    {
        return User::class;
    }

    public function getAnonymousUser(): ?UserInterface
    {
        return $this->getUserByRole(UserRoleInterface::ROLE_ANONYMOUS);
    }

    public function getSuperAdmin(): ?UserInterface
    {
        return $this->getUserByRole(UserRoleInterface::ROLE_SUPER_ADMIN);
    }

    protected function getUserByRole(string $role): ?UserInterface
    {
        $likeRole = '%'.$role.'%';
        $queryBuilder = $this->createQueryBuilder('main');
        $queryBuilder->where(
            $queryBuilder->expr()->like(
                'main.roles',
                ':role'
            )
        );
        $queryBuilder->setParameter(':role', $likeRole);
        $queryBuilder->setMaxResults(1);

        return $this->getOneOrNullUser($queryBuilder);
    }

    public function getUserByToken(string $token): ?UserInterface
    {
        $queryBuilder = $this->createQueryBuilder('main');

        $queryBuilder->where(
            $queryBuilder->expr()->eq('main.api_token', ':token')
        );
        $queryBuilder->setParameter(':token', $token);

        $queryBuilder->andWhere(
            $queryBuilder->expr()->isNull('main.deleted_at')
        );

        return $this->getOneOrNullUser($queryBuilder);
    }

    protected function getOneOrNullUser(QueryBuilder $queryBuilder): ?UserInterface
    {
        try {
            return $queryBuilder->getQuery()->getOneOrNullResult();
        } catch (NonUniqueResultException $e) {
            return null;
        }
    }
}
