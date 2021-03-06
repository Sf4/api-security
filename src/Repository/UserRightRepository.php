<?php
/**
 * Created by PhpStorm.
 * User: siim
 * Date: 15.02.19
 * Time: 8:44
 */

namespace Sf4\ApiSecurity\Repository;

use Sf4\Api\Repository\AbstractRepository;
use Sf4\Api\Setting\StatusSettingInterface;
use Sf4\ApiSecurity\Entity\UserRole;
use Sf4\ApiSecurity\Entity\UserRoleRight;
use Sf4\ApiUser\Entity\UserInterface;
use Doctrine\ORM\Query\Expr\Join;

class UserRightRepository extends AbstractRepository
{
    public const TABLE_NAME = 'user_right';
    public const PARAMETER_STATUS_ACTIVE = ':status_active';

    public function getUserRights(UserInterface $user): array
    {
        $roles = $user->getRoles();
        $queryBuilder = $this->createQueryBuilder('main');
        $queryBuilder->select([
            'main.code'
        ]);
        $queryBuilder->join(
            UserRoleRight::class,
            'role_right',
            Join::WITH,
            'main.id = role_right.right'
        );
        $queryBuilder->join(
            UserRole::class,
            'role',
            Join::WITH,
            'role_right.role = role.id'
        );
        $queryBuilder->andWhere(
            $queryBuilder->expr()->andX(
                $queryBuilder->expr()->in('role.code', $roles),
                $queryBuilder->expr()->eq('role.status', static::PARAMETER_STATUS_ACTIVE),
                $queryBuilder->expr()->eq('main.status', static::PARAMETER_STATUS_ACTIVE)
            )
        );
        $queryBuilder->setParameter(
            static::PARAMETER_STATUS_ACTIVE,
            StatusSettingInterface::ACTIVE
        );

        return $queryBuilder->getQuery()->getArrayResult();
    }

    public function getRightsByCodes(array $codes)
    {
        $queryBuilder = $this->createQueryBuilder('main');
        $queryBuilder->where(
            $queryBuilder->expr()->andX(
                $queryBuilder->expr()->in('main.code', $codes),
                $queryBuilder->expr()->eq('main.status', static::PARAMETER_STATUS_ACTIVE)
            )
        );
        $queryBuilder->setParameter(
            static::PARAMETER_STATUS_ACTIVE,
            StatusSettingInterface::ACTIVE
        );

        return $queryBuilder->getQuery()->getResult();
    }
}
