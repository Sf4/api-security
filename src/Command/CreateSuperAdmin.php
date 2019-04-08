<?php
/**
 * Created by PhpStorm.
 * User: siim
 * Date: 19.02.19
 * Time: 9:54
 */

namespace Sf4\ApiSecurity\Command;

use DateTime;
use Exception;
use Sf4\Api\Repository\RepositoryFactory;
use Sf4\Api\RequestHandler\Traits\RepositoryFactoryTrait;
use Sf4\Api\Setting\StatusSettingInterface;
use Sf4\ApiUser\Entity\User;
use Sf4\ApiUser\Entity\UserDetail;
use Sf4\ApiSecurity\Entity\UserRoleInterface;
use Sf4\ApiUser\Repository\UserDetailRepository;
use Sf4\ApiUser\Repository\UserRepository;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CreateSuperAdmin extends Command
{
    use RepositoryFactoryTrait;

    protected static $defaultName = 'api-security:create-super-admin';

    public function __construct(RepositoryFactory $repositoryFactory)
    {
        parent::__construct();
        $this->setRepositoryFactory($repositoryFactory);
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int|void|null
     * @throws Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $entityClass = $this->getRepositoryFactory()->getEntityClass(
            UserRepository::TABLE_NAME
        );
        $detailEntityClass = $this->getRepositoryFactory()->getEntityClass(
            UserDetailRepository::TABLE_NAME
        );

        $this->createUser(
            $entityClass,
            $detailEntityClass,
            [UserRoleInterface::ROLE_ANONYMOUS],
            'anonymous.user@example.example'
        );
        $this->createUser(
            $entityClass,
            $detailEntityClass,
            [UserRoleInterface::ROLE_SUPER_ADMIN],
            'super.admin@example.example'
        );
    }

    /**
     * @param string $entityClass
     * @param string $detailEntityClass
     * @param array $roles
     * @param string $email
     * @throws Exception
     */
    protected function createUser(string $entityClass, string $detailEntityClass, array $roles, string $email): void
    {
        /** @var User $user */
        $user = new $entityClass();
        $user->setPassword($user->createNewToken());
        $user->setApiToken($user->createNewToken());
        $user->setStatus(StatusSettingInterface::ACTIVE);
        $user->setRoles($roles);
        $user->setEmail($email);
        $user->setCreatedAt(new DateTime());
        $user->setUpdatedAt(new DateTime());
        $user->setUserDetail(
            $this->createUserDetail(
                $detailEntityClass,
                $email,
                ''
            )
        );
        $user->createUuid();

        $this->getRepositoryFactory()->getEntityManager()->persist($user);
        $this->getRepositoryFactory()->getEntityManager()->flush();
    }

    /**
     * @param string $detailEntityClass
     * @param string $firstName
     * @param string $lastName
     * @return UserDetail
     */
    protected function createUserDetail(string $detailEntityClass, string $firstName, string $lastName): UserDetail
    {
        /** @var UserDetail $userDetail */
        $userDetail = new $detailEntityClass();
        $userDetail->setFirstName($firstName);
        $userDetail->setLastName($lastName);
        $userDetail->setAvatar('');
        $userDetail->createUuid();

        return $userDetail;
    }
}
