<?php

declare (strict_types=1);
namespace Syde\Vendor\WpOop\Containers\Options;

use Syde\Vendor\Dhii\Collection\MutableContainerInterface;
use Exception;
use RuntimeException;
use UnexpectedValueException;
use Syde\Vendor\WpOop\Containers\Exception\ContainerException;
use Syde\Vendor\WpOop\Containers\Exception\NotFoundException;
use Syde\Vendor\WpOop\Containers\Util\StringTranslatingTrait;
/**
 * Metadata for a particular site.
 *
 * @package WpOop\Containers\
 */
class SiteMeta implements MutableContainerInterface
{
    use StringTranslatingTrait;
    /** @var int|null */
    protected $siteId;
    /**
     * @param int|null $siteId ID of the site, or `null` to always use current site.
     */
    public function __construct(int $siteId)
    {
        $this->siteId = $siteId;
    }
    /**
     * {@inheritDoc}
     *
     * @psalm-suppress MissingParamType Missing in PSR-11.
     */
    public function get($id)
    {
        try {
            return $this->getMeta($id);
        } catch (UnexpectedValueException $e) {
            throw new NotFoundException($id, $this->__('Meta key "%1$s" not found', [$id]), 0, $e, $this);
        } catch (Exception $e) {
            throw new ContainerException($this->__('Could not get value for meta key "%1$s', [$id]), 0, $e, $this);
        }
    }
    /**
     * @inheritDoc
     *
     * @param string $id Identifier of the entry to look for.
     */
    public function has($id)
    {
        try {
            $this->getMeta($id);
            return \true;
        } catch (UnexpectedValueException $e) {
            return \false;
        } catch (Exception $e) {
            throw new ContainerException($this->__('Could not check for meta key "%1$s"', [$id]), 0, $e, $this);
        }
    }
    /**
     * {@inheritdoc}
     */
    public function set(string $key, $value): void
    {
        try {
            $this->setMeta($key, $value);
        } catch (Exception $e) {
            throw new ContainerException($this->__('Could not set value for meta key "%1$s"', [$key]), 0, $e, $this);
        }
    }
    /**
     * {@inheritDoc}
     */
    public function unset(string $key): void
    {
        $siteId = $this->siteId;
        /** @psalm-suppress PossiblyNullArgument Actually allows null site ID */
        $result = delete_site_meta($siteId, $key);
        if ($result === \false) {
            throw new ContainerException($this->__('Could not delete meta key "%1$s" for blog %2$s', [$key, $siteId === null ? 'null' : "#{$siteId}"]), 0, null, $this);
        }
    }
    /**
     * Retrieves a meta value.
     *
     * @param string $name The name of the meta key to retrieve.
     *
     * @return mixed The meta value.
     *
     * @throws UnexpectedValueException If the meta value matches the configured default.
     * @throws RuntimeException If problem retrieving.
     * @throws Exception If problem running.
     */
    protected function getMeta(string $name)
    {
        $siteId = $this->siteId;
        /** @psalm-suppress PossiblyNullArgument Actually allows null site ID */
        $value = get_site_meta($siteId, $name, \false);
        /*
         * There's no way to pass a control value that would be returned if not found.
         * Due to the way meta retrieval works (via cache), it does not check for individual keys in the DB,
         * but instead retrieves and caches them all. There's no additional check to return a special value
         * that would reliably signify that the value is not found. Instead, it returns maybe unserialized value
         * if it is found, and null otherwise. Therefore, null is the closest value to signifying its absence.
         * https://github.com/WordPress/WordPress/blob/2699b3032a710335e47a4a3a9d3fd5e44c35bac0/wp-includes/meta.php#L665
         *
         * Also, the value can be an empty string if site ID is not found, or false if invalid, according to docs.
         * There is no way to determine the site specified is missing or if its ID is invalid, or if the empty string
         * is the actual value, without checking for existence of the site with the ID.
         * This is the concern of a factory instantiating this class.
         */
        if ($value === null) {
            throw new UnexpectedValueException($this->__('Meta key "%1$s" for blog %2$d does not exist', [$name, $siteId === null ? 'null' : "#{$siteId}"]));
        }
        return $value;
    }
    /**
     * Assigns a value to a meta key.
     *
     * @param string $name The name of the meta key to set the value for.
     * @param mixed $value The value to set.
     *
     * @throws UnexpectedValueException If new meta value does not match what was being set.
     * @throws RuntimeException If problem setting.
     * @throws Exception If problem running.
     */
    protected function setMeta(string $name, $value): void
    {
        $siteId = $this->siteId;
        /** @psalm-suppress PossiblyNullArgument Actually allows null site ID */
        $isSuccessful = update_site_meta($siteId, $name, $value);
        if (!$isSuccessful) {
            $newValue = $this->getMeta($name);
            $isSuccessful = $value === $newValue;
        }
        /** @psalm-suppress PossiblyUndefinedVariable If unsuccessful, $newValue will be defined */
        if (!$isSuccessful) {
            throw new UnexpectedValueException($this->__('New meta value did not match the intended value for blog %3$s: "%1$s" VS "%2$s"', [print_r($value, \true), print_r($newValue, \true), $siteId === null ? 'null' : "#{$siteId}"]));
        }
    }
}
