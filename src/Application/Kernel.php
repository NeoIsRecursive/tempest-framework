<?php

declare(strict_types=1);

namespace Tempest\Application;

use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use ReflectionClass;
use SplFileInfo;
use Tempest\AppConfig;
use Tempest\Bootstraps\ConfigBootstrap;
use Tempest\Bootstraps\DiscoveryLocationBootstrap;
use Tempest\Container\Container;
use Tempest\Container\GenericContainer;
use Tempest\Database\PDOInitializer;
use Tempest\Discovery\Discovery;
use Tempest\Http\RequestInitializer;
use Tempest\Http\RouteBindingInitializer;
use Throwable;

final readonly class Kernel
{
    public function __construct(
        public string $root,
        private AppConfig $appConfig,
    ) {
    }

    public function init(): Container
    {
        $container = $this->createContainer();

        $bootstraps = [
            DiscoveryLocationBootstrap::class,
            ConfigBootstrap::class,
        ];

        foreach ($bootstraps as $bootstrap) {
            $container->get($bootstrap)->boot();
        }

        $this->initDiscovery($container);

        return $container;
    }

    private function createContainer(): Container
    {
        $container = new GenericContainer();

        GenericContainer::setInstance($container);

        $container
            ->config($this->appConfig)
            ->singleton(self::class, fn () => $this)
            ->singleton(Container::class, fn () => $container)
            ->addInitializer(new RequestInitializer())
            ->addInitializer(new RouteBindingInitializer())
            ->addInitializer(new PDOInitializer());

        return $container;
    }

    private function initDiscovery(Container $container): void
    {
        reset($this->appConfig->discoveryClasses);

        while ($discoveryClass = current($this->appConfig->discoveryClasses)) {
            /** @var Discovery $discovery */
            $discovery = $container->get($discoveryClass);

            if ($this->appConfig->discoveryCache && $discovery->hasCache()) {
                $discovery->restoreCache($container);
                next($this->appConfig->discoveryClasses);

                continue;
            }

            foreach ($this->appConfig->discoveryLocations as $discoveryLocation) {
                $directories = new RecursiveDirectoryIterator($discoveryLocation->path);
                $files = new RecursiveIteratorIterator($directories);

                /** @var SplFileInfo $file */
                foreach ($files as $file) {
                    $fileName = $file->getFilename();

                    if (
                        $fileName === ''
                        || $fileName === '.'
                        || $fileName === '..'
                        || ucfirst($fileName) !== $fileName
                    ) {
                        continue;
                    }

                    $className = str_replace(
                        [$discoveryLocation->path, '/', '.php', '\\\\'],
                        [$discoveryLocation->namespace, '\\', '', '\\'],
                        $file->getPathname(),
                    );

                    try {
                        $reflection = new ReflectionClass($className);
                    } catch (Throwable) {
                        continue;
                    }

                    $discovery->discover($reflection);
                }
            }

            next($this->appConfig->discoveryClasses);

            $discovery->storeCache();
        }
    }
}
