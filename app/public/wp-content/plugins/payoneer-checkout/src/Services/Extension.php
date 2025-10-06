<?php

declare(strict_types=1);

namespace Inpsyde\PayoneerForWoocommerce\Services;

use Dhii\Services\ResolveKeysCapableTrait;
use Psr\Container\ContainerInterface;

class Extension
{
    use ResolveKeysCapableTrait;

    /**
     * @var string[]
     */
    protected $dependencies;
    /**
     * @var callable
     */
    protected $definition;

    /**
     * Constructor.
     *
     * @param string[] $dependencies Ids of services that this service depends on.
     */
    public function __construct(array $dependencies, callable $definition)
    {
        $this->dependencies = $dependencies;
        $this->definition = $definition;
    }

    /**
     * Retrieves the keys of dependent services.
     *
     * @return string[] A list of strings each representing the key of a service.
     */
    public function getDependencies(): array
    {
        return $this->dependencies;
    }

    /**
     * Creates a copy of this service with different dependency keys.
     *
     * @param string[] $dependencies The new service dependency keys.
     *
     * @return static The newly created service instance.
     */
    public function withDependencies(array $dependencies): Extension
    {
        $instance = clone $this;
        $instance->dependencies = $dependencies;

        return $instance;
    }

    /**
     * @param mixed $prev
     * @param ContainerInterface $container
     *
     * @return mixed
     */
    public function __invoke($prev, ContainerInterface $container)
    {
        $deps = $this->resolveKeys($container, $this->dependencies);
        array_unshift($deps, $prev);

        return ($this->definition)(...$deps);
    }
}
