<?php
/**
 * Created by PhpStorm.
 * User: siim
 * Date: 1.03.19
 * Time: 6:54
 */

namespace Sf4\ApiSecurity\DependencyInjection;

use Exception;
use Sf4\ApiSecurity\Request\GoogleLoginRequest;
use Sf4\ApiUser\Entity\User;
use Sf4\ApiUser\Entity\UserDetail;
use Sf4\ApiSecurity\Entity\UserRight;
use Sf4\ApiSecurity\Entity\UserRole;
use Sf4\ApiSecurity\Entity\UserRoleRight;
use Sf4\ApiUser\Repository\UserDetailRepository;
use Sf4\ApiUser\Repository\UserRepository;
use Sf4\ApiSecurity\Repository\UserRightRepository;
use Sf4\ApiSecurity\Repository\UserRoleRepository;
use Sf4\ApiSecurity\Repository\UserRoleRightRepository;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;
use Sf4\Api\DependencyInjection\Configuration as Sf4ApiConfiguration;
use Sf4\Api\DependencyInjection\Traits\Sf4ApiExtensionTrait;

class Sf4ApiSecurityExtension extends Extension implements PrependExtensionInterface
{

    use Sf4ApiExtensionTrait;

    public const SF4_API_BUNDLE = 'Sf4ApiBundle';

    /**
     * Loads a specific configuration.
     *
     * @param array $configs
     * @param ContainerBuilder $container
     * @throws Exception
     */
    public function load(array $configs, ContainerBuilder $container): void
    {
        $this->loadServices($container, __DIR__);
    }

    /**
     * Allow an extension to prepend the extension configurations.
     * @param ContainerBuilder $container
     */
    public function prepend(ContainerBuilder $container): void
    {
        $bundles = $container->getParameter('kernel.bundles');
        if (isset($bundles[static::SF4_API_BUNDLE])) {
            $container->prependExtensionConfig(Sf4ApiConfiguration::NAME, [
                Sf4ApiConfiguration::ROUTES => $this->getRoutes(),
                Sf4ApiConfiguration::ENTITIES => $this->getEntities()
            ]);
        }
    }

    /**
     * @return array
     */
    protected function getRoutes(): array
    {
        return [
            [
                Sf4ApiConfiguration::ROUTES_ROUTE => GoogleLoginRequest::ROUTE,
                Sf4ApiConfiguration::ROUTES_REQUEST_CLASS => GoogleLoginRequest::class
            ]
        ];
    }

    /**
     * @return array
     */
    protected function getEntities(): array
    {
        return [
            [
                Sf4ApiConfiguration::ENTITIES_TABLE_NAME => UserRepository::TABLE_NAME,
                Sf4ApiConfiguration::ENTITIES_ENTITY_CLASS => User::class
            ],
            [
                Sf4ApiConfiguration::ENTITIES_TABLE_NAME => UserDetailRepository::TABLE_NAME,
                Sf4ApiConfiguration::ENTITIES_ENTITY_CLASS => UserDetail::class
            ],
            [
                Sf4ApiConfiguration::ENTITIES_TABLE_NAME => UserRightRepository::TABLE_NAME,
                Sf4ApiConfiguration::ENTITIES_ENTITY_CLASS => UserRight::class
            ],
            [
                Sf4ApiConfiguration::ENTITIES_TABLE_NAME => UserRoleRepository::TABLE_NAME,
                Sf4ApiConfiguration::ENTITIES_ENTITY_CLASS => UserRole::class
            ],
            [
                Sf4ApiConfiguration::ENTITIES_TABLE_NAME => UserRoleRightRepository::TABLE_NAME,
                Sf4ApiConfiguration::ENTITIES_ENTITY_CLASS => UserRoleRight::class
            ]
        ];
    }
}
