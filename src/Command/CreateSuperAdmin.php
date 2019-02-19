<?php
/**
 * Created by PhpStorm.
 * User: siim
 * Date: 19.02.19
 * Time: 9:54
 */

namespace Sf4\ApiSecurity\Command;

use Sf4\Api\Repository\RepositoryFactory;
use Sf4\Api\RequestHandler\Traits\RepositoryFactoryTrait;
use Sf4\Api\Setting\StatusSettingInterface;
use Sf4\ApiSecurity\Entity\User;
use Sf4\ApiSecurity\Entity\UserRoleInterface;
use Sf4\ApiSecurity\Repository\UserRepository;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CreateSuperAdmin extends Command
{
    use RepositoryFactoryTrait;

    protected static $defaultName = 'api-security:create-super-admin';

    public function __construct(RepositoryFactory $repositoryFactory)
    {
        parent::__construct(null);
        $this->setRepositoryFactory($repositoryFactory);
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int|void|null
     * @throws \Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $entityClass = $this->getRepositoryFactory()->getEntityClass(
            UserRepository::TABLE_NAME
        );
        /** @var User $user */
        $user = new $entityClass();
        $user->createUuid();
        $user->setPassword($user->createNewToken());
        $user->setApiToken($user->createNewToken());
        $user->setStatus(StatusSettingInterface::ACTIVE);
        $user->setRoles([UserRoleInterface::ROLE_SUPER_ADMIN]);
        $user->setEmail('super.admin@example.example');
        $user->setCreatedAt(new \DateTime());
        $user->setUpdatedAt(new \DateTime());
        $this->getRepositoryFactory()->getEntityManager()->persist($user);
        $this->getRepositoryFactory()->getEntityManager()->flush();
    }
}
