<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\Core;

use Tests\Tempest\Integration\FrameworkIntegrationTestCase;

use function Tempest\base_path;

/**
 * @internal
 */
final class PathHelperTest extends FrameworkIntegrationTestCase
{
    public function test_can_get_base_path(): void
    {
        $this->assertSame(realpath($this->root), base_path());
        $this->assertSame(realpath($this->root . '/tests/Fixtures'), base_path('/tests/Fixtures'));
    }
}