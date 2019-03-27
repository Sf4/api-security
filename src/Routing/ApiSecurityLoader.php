<?php
/**
 * Created by PhpStorm.
 * User: john
 * Date: 27.03.19
 * Time: 10:03
 */

namespace Sf4\ApiSecurity\Routing;

use Symfony\Component\Config\Loader\Loader;
use Symfony\Component\Routing\RouteCollection;

class ApiSecurityLoader extends Loader
{

    public const TYPE = 'sf4_api_security';

    /** @var bool $isLoaded */
    protected $isLoaded = false;

    /**
     * Loads a resource.
     *
     * @param mixed $resource The resource
     * @param string|null $type The resource type or null if unknown
     *
     * @return RouteCollection|null
     * @throws \Exception If something went wrong
     */
    public function load($resource, $type = null): ?RouteCollection
    {
        if (true === $this->isLoaded) {
            return null;
        }

        $routes = new RouteCollection();

        $importedRoutes = $this->import(
            '@Sf4ApiSecurityBundle/Resources/config/routes.yaml',
            'yaml'
        );
        $routes->addCollection($importedRoutes);

        $this->isLoaded = true;

        return $routes;
    }

    /**
     * Returns whether this class supports the given resource.
     *
     * @param mixed $resource A resource
     * @param string|null $type The resource type or null if unknown
     *
     * @return bool True if this class supports the given resource, false otherwise
     */
    public function supports($resource, $type = null): bool
    {
        return static::TYPE === $type;
    }
}
