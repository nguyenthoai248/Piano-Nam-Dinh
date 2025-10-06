<?php

declare (strict_types=1);
namespace Syde\Vendor\WpOop\Containers;

use Syde\Vendor\Dhii\Collection\ContainerInterface;
use Syde\Vendor\Psr\Container\NotFoundExceptionInterface;
use WP_Site;
use Syde\Vendor\WpOop\Containers\Exception\NotFoundException;
use Syde\Vendor\WpOop\Containers\Util\StringTranslatingTrait;
/**
 * Allows retrieval of WP site objects by ID.
 *
 * @package WpOop\Containers
 */
class Sites implements ContainerInterface
{
    use StringTranslatingTrait;
    /**
     * @inheritDoc
     *
     * @param string $id Identifier of the entry to look for.
     *
     * @return WP_Site The site for the specified ID.
     */
    public function get($id)
    {
        $id = intval($id);
        $site = get_site($id);
        if (!$site) {
            throw new NotFoundException((string) $id, $this->__('No site found for ID "%1$d"', [$id]), 0, null, $this);
        }
        return $site;
    }
    /**
     * @inheritDoc
     *
     * @param string $id Identifier of the entry to look for.
     */
    public function has($id)
    {
        /** @psalm-suppress InvalidCatch */
        try {
            $site = $this->get($id);
        } catch (NotFoundExceptionInterface $e) {
            return \false;
        }
        return \true;
    }
}
