<?php
/**
 * Created by PhpStorm.
 * User: siim
 * Date: 18.02.19
 * Time: 7:22
 */

namespace Sf4\ApiSecurity\Command;

use Curl\Curl;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Sf4\Api\Dto\Response\SiteResponseDto;
use Sf4\Api\Repository\RepositoryFactory;
use Sf4\Api\RequestHandler\RequestHandlerTrait;
use Sf4\Api\Setting\StatusSettingInterface;
use Sf4\ApiSecurity\Entity\UserRight;
use Sf4\ApiSecurity\Entity\UserRole;
use Sf4\ApiSecurity\Entity\UserRoleInterface;
use Sf4\ApiSecurity\Entity\UserRoleRight;
use Sf4\ApiUser\Repository\UserRepository;
use Sf4\ApiSecurity\Repository\UserRightRepository;
use Sf4\Api\RequestHandler\RequestHandlerInterface;
use Sf4\Api\Utils\Traits\TruncateTableTrait;
use Sf4\ApiSecurity\Repository\UserRoleRepository;
use Sf4\ApiSecurity\Repository\UserRoleRightRepository;
use Sf4\ApiUser\Entity\TimestampableInterface;
use Sf4\ApiUser\Entity\UserInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class UserRightCreator extends Command
{

    use TruncateTableTrait;
    use RequestHandlerTrait;

    public const SITE_SITE = 'site';
    public const SITE_URL = 'url';
    public const SITE_TOKEN = 'token';
    public const SITE_MAIN = 'main';
    public const SITE_PARENT = 'parent';

    protected static $defaultName = 'api-security:create-user-rights';

    public function __construct(RequestHandlerInterface $requestHandler)
    {
        parent::__construct();
        $this->setRequestHandler($requestHandler);
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
            UserRightRepository::TABLE_NAME
        );
        /** @var UserRepository $userRepository */
        $userRepository = $this->getRepositoryFactory()->create(
            UserRepository::TABLE_NAME
        );
        $superAdmin = $userRepository->getUserByRole(UserRoleInterface::ROLE_SUPER_ADMIN);
        if ($superAdmin instanceof UserInterface) {
            /*
             * Truncate role and rights table
             */
            $this->truncateTables();

            $requestHandler = $this->getRequestHandler();
            if ($requestHandler) {
                /*
                 * Add available rights
                 */
                $availableRoutes = $requestHandler->getAvailableRoutes();
                $this->addRights(static::SITE_MAIN, array_keys($availableRoutes), $entityClass, $superAdmin);

                /*
                 * Add super admin rights
                 */
                $this->addSuperAdminRights($superAdmin);

                /*
                 * Add anonymous user rights
                 */
                $anonymousUser = $userRepository->getUserByRole(UserRoleInterface::ROLE_ANONYMOUS);
                if ($anonymousUser) {
                    $this->addAnonymousUserRights($anonymousUser);
                }

                /*
                 * Add site rights
                 */
                $sites = $requestHandler->getSites();
                foreach ($sites as $site) {
                    if (!isset($site[static::SITE_SITE], $site[static::SITE_URL]) ||
                        $site[static::SITE_SITE] === static::SITE_PARENT ||
                        $site[static::SITE_URL] === null
                    ) {
                        continue;
                    }
                    $this->addSiteRights($site, $entityClass, $superAdmin);
                }
            }
        }
    }

    /**
     * @param array $site
     * @param string $entityClass
     * @param UserInterface $superAdmin
     * @throws Exception
     */
    protected function addSiteRights(array $site, string $entityClass, UserInterface $superAdmin): void
    {
        $siteName = $site[static::SITE_SITE];
        $apiToken = $site[static::SITE_TOKEN] ?? '';
        $url = $site[static::SITE_URL] . '/'. static::SITE_SITE .'/' . $apiToken;

        try {
            $curl = new Curl();
            $curl->get($url);
            $response = $curl->getResponse();
            $curl->close();
        } catch (Exception $exception) {
            $response = '{}';
        }

        if ($response) {
            $data = json_decode($response, true);
            if (is_array($data)) {
                $dto = new SiteResponseDto();
                $dto->populate($data);

                foreach ($dto->getAvailableRoutes() as $code => $className) {
                    $rights = [
                        $siteName . ':' . $code
                    ];
                    $this->addRights($siteName, $rights, $entityClass, $superAdmin);
                }
            }
        }
    }

    /**
     * @param UserInterface $superAdmin
     * @throws Exception
     */
    protected function addSuperAdminRights(UserInterface $superAdmin): void
    {
        $requestHandler = $this->getRequestHandler();
        if ($requestHandler) {
            $superAdminTranslation = $requestHandler->getTranslator()->trans('user_role.super_admin');
            $rightCodes = UserRight::$superAdminRights;
            $this->addUserRights(
                $superAdmin,
                $rightCodes,
                UserRoleInterface::ROLE_SUPER_ADMIN,
                $superAdminTranslation
            );
        }
    }

    /**
     * @param UserInterface $user
     * @throws Exception
     */
    protected function addAnonymousUserRights(UserInterface $user): void
    {
        $requestHandler = $this->getRequestHandler();
        if ($requestHandler) {
            $anonymousUserTranslation = $requestHandler->getTranslator()->trans('user_role.anonymous_user');
            $rightCodes = UserRight::$anonymousUserRights;
            $this->addUserRights(
                $user,
                $rightCodes,
                UserRoleInterface::ROLE_ANONYMOUS,
                $anonymousUserTranslation
            );
        }
    }

    /**
     * @param UserInterface $user
     * @param array $rightCodes
     * @param string $code
     * @param string $name
     * @param string $site
     * @throws Exception
     */
    protected function addUserRights(
        UserInterface $user,
        array $rightCodes,
        string $code,
        string $name,
        string $site = null
    ): void {
        $userRole = $this->createUserRole($user, $code, $name, $site);
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
    protected function createUserRoleRight(UserRole $role, UserRight $right): void
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
     * @param string $site
     * @return UserRole
     * @throws Exception
     */
    protected function createUserRole(
        UserInterface $superAdmin,
        string $code,
        string $name,
        string $site = null
    ): UserRole {
        $userRole = new UserRole();
        $userRole->setCode($code);
        $userRole->setName($name);
        $userRole->setSite($site);

        if ($userRole instanceof TimestampableInterface) {
            $this->addTimeAndUser($userRole, $superAdmin);
        }

        $userRole->setStatus(StatusSettingInterface::ACTIVE);
        $userRole->createUuid();

        $this->getEntityManager()->persist($userRole);
        $this->getEntityManager()->flush();

        return $userRole;
    }

    /**
     * @throws Exception
     */
    protected function truncateTables(): void
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
     * @param string $site
     * @param array $rights
     * @param string $entityClass
     * @param UserInterface $superAdmin
     * @throws Exception
     */
    protected function addRights(string $site, array $rights, string $entityClass, UserInterface $superAdmin): void
    {
        foreach ($rights as $rightCode) {
            /** @var UserRight $userRight */
            $userRight = new $entityClass();
            $userRight->setSite($site);
            $userRight->setCode($rightCode);
            $userRight->createUuid();
            $userRight->setStatus(StatusSettingInterface::ACTIVE);

            if ($userRight instanceof TimestampableInterface) {
                $this->addTimeAndUser($userRight, $superAdmin);
            }

            $this->getEntityManager()->persist($userRight);
        }
        $this->getEntityManager()->flush();
    }

    /**
     * @param TimestampableInterface $entity
     * @param UserInterface $user
     * @throws Exception
     */
    protected function addTimeAndUser(TimestampableInterface $entity, UserInterface $user): void
    {
        $dateTime = new DateTime('now');
        $entity->setCreatedAt($dateTime);
        $entity->setUpdatedAt($dateTime);
        $entity->setCreatedBy($user);
        $entity->setUpdatedBy($user);
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
