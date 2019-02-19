<?php
/**
 * Created by PhpStorm.
 * User: siim
 * Date: 18.02.19
 * Time: 7:22
 */

namespace Sf4\ApiSecurity\Command;

use Doctrine\ORM\EntityManagerInterface;
use Sf4\Api\Repository\RepositoryFactory;
use Sf4\Api\Setting\StatusSettingInterface;
use Sf4\ApiSecurity\Entity\UserRight;
use Sf4\ApiSecurity\Entity\UserRole;
use Sf4\ApiSecurity\Entity\UserRoleInterface;
use Sf4\ApiSecurity\Entity\UserRoleRight;
use Sf4\ApiSecurity\Repository\UserRepository;
use Sf4\ApiSecurity\Repository\UserRightRepository;
use Sf4\Api\RequestHandler\RequestHandlerInterface;
use Sf4\Api\Utils\Traits\TruncateTableTrait;
use Sf4\ApiSecurity\Repository\UserRoleRepository;
use Sf4\ApiSecurity\Repository\UserRoleRightRepository;
use Sf4\ApiUser\Entity\UserInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class UserRightCreator extends Command
{

    use TruncateTableTrait;

    protected static $defaultName = 'api-security:create-user-rights';

    /** @var RequestHandlerInterface $requestHandler */
    protected $requestHandler;

    public function __construct(RequestHandlerInterface $requestHandler)
    {
        parent::__construct(null);
        $this->requestHandler = $requestHandler;
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
            UserRightRepository::TABLE_NAME
        );
        /** @var UserRepository $userRepository */
        $userRepository = $this->getRepositoryFactory()->create(
            UserRepository::TABLE_NAME
        );
        $superAdmin = $userRepository->getSuperAdmin();
        if ($superAdmin instanceof UserInterface) {
            $this->truncateTables();
            $availableRoutes = $this->requestHandler->getAvailableRoutes();
            $this->addRights(array_keys($availableRoutes), $entityClass, $superAdmin);
            $sites = $this->requestHandler->getSites();
            foreach ($sites as $site) {
                foreach ($site as $key => $value) {
                    if ($key === 'parent' || $value === null) {
                        continue 2;
                    }
                }
//                $this->addSiteRights($site, $entityClass, $superAdmin);
            }
        }
        $this->addSuperAdminRights($superAdmin);
    }

//    protected function addSiteRights(array $site, string $entityClass, UserInterface $superAdmin)
//    {
//        $apiToken = $site['token'];
//        $url = $site['url'] . '/' . '/' . $apiToken;
//    }

    /**
     * @param UserInterface $superAdmin
     * @throws \Exception
     */
    protected function addSuperAdminRights(UserInterface $superAdmin)
    {
        $rightCodes = UserRight::$superAdminRights;
        $userRole = $this->createUserRole($superAdmin, UserRoleInterface::ROLE_SUPER_ADMIN, 'Super admin');
        $userRights = $this->getRightsByCodes($rightCodes);

        foreach ($userRights as $userRight) {
            if ($userRight instanceof UserRight) {
                $this->createUserRoleRight($userRole, $userRight);
            }
        }
    }

    /**
     * @param array $codes
     * @return array
     */
    protected function getRightsByCodes(array $codes): array
    {
        $repository = $this->getRepositoryFactory()->create(
            UserRightRepository::TABLE_NAME
        );
        if ($repository instanceof UserRightRepository) {
            return $repository->getRightsByCodes($codes);
        }

        return [];
    }

    /**
     * @param UserRole $role
     * @param UserRight $right
     */
    protected function createUserRoleRight(UserRole $role, UserRight $right)
    {
        $userRoleRight = new UserRoleRight();
        $userRoleRight->createUuid();
        $userRoleRight->setRole($role);
        $userRoleRight->setRight($right);
        $this->getEntityManager()->persist($userRoleRight);
        $this->getEntityManager()->flush();
    }

    /**
     * @param UserInterface $superAdmin
     * @param string $code
     * @param string $name
     * @return UserRole
     * @throws \Exception
     */
    protected function createUserRole(UserInterface $superAdmin, string $code, string $name): UserRole
    {
        $userRole = new UserRole();
        $userRole->createUuid();
        $userRole->setCode($code);
        $userRole->setName($name);
        $userRole->setCreatedAt(new \DateTime());
        $userRole->setUpdatedAt(new \DateTime());
        $userRole->setCreatedBy($superAdmin);
        $userRole->setUpdatedBy($superAdmin);
        $userRole->setStatus(StatusSettingInterface::ACTIVE);
        $this->getEntityManager()->persist($userRole);
        $this->getEntityManager()->flush();

        return $userRole;
    }

    /**
     * @throws \Exception
     */
    protected function truncateTables()
    {
        $em = $this->getEntityManager();
        $this->truncateTable($em, $this->getRepositoryFactory()->getEntityClass(
            UserRoleRightRepository::TABLE_NAME
        ));
        $this->truncateTable($em, $this->getRepositoryFactory()->getEntityClass(
            UserRightRepository::TABLE_NAME
        ));
        $this->truncateTable($em, $this->getRepositoryFactory()->getEntityClass(
            UserRoleRepository::TABLE_NAME
        ));
    }

    /**
     * @param array $rights
     * @param string $entityClass
     * @param UserInterface $superAdmin
     * @throws \Exception
     */
    protected function addRights(array $rights, string $entityClass, UserInterface $superAdmin)
    {
        foreach ($rights as $rightCode) {
            /** @var UserRight $userRight */
            $userRight = new $entityClass();
            $userRight->setCode($rightCode);
            $userRight->createUuid();
            $userRight->setStatus(StatusSettingInterface::ACTIVE);
            $userRight->setCreatedAt(new \DateTime());
            $userRight->setUpdatedAt(new \DateTime());
            $userRight->setCreatedBy($superAdmin);
            $userRight->setUpdatedBy($superAdmin);

            $this->getEntityManager()->persist($userRight);
        }
        $this->getEntityManager()->flush();
    }



    /**
     * @return EntityManagerInterface
     */
    protected function getEntityManager(): EntityManagerInterface
    {
        return $this->requestHandler->getEntityManager();
    }

    /**
     * @return RepositoryFactory
     */
    protected function getRepositoryFactory(): RepositoryFactory
    {
        return $this->requestHandler->getRepositoryFactory();
    }
}
