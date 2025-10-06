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
 * Allows access to options for a particular site.
 *
 * @package WpOop\Containers
 */
class BlogOptions implements MutableContainerInterface
{
    use StringTranslatingTrait;
    /** @var int|null */
    protected $blogId;
    /** @var string */
    protected $default;
    /**
     * @param int|null $blogId The ID of the blog to represent the options for, or null for current blog.
     * @param string $default The value to return if an option is not found.
     * This is necessary because WP will otherwise return `false`, which
     * is indistinguishable from a real option value.
     * Therefore, this should be set to something that is unlikely to be a
     * valid option value.
     */
    public function __construct(?int $blogId, string $default)
    {
        $this->blogId = $blogId;
        $this->default = $default;
    }
    /**
     * @inheritDoc
     */
    public function get($id)
    {
        try {
            return $this->getOption($id);
        } catch (UnexpectedValueException $e) {
            throw new NotFoundException($id, $this->__('Key "%1$s" not found', [$id]), 0, $e, $this);
        } catch (Exception $e) {
            throw new ContainerException($this->__('Could not get value for key "%1$s', [$id]), 0, $e, $this);
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
            $this->getOption($id);
            return \true;
        } catch (UnexpectedValueException $e) {
            return \false;
        } catch (Exception $e) {
            throw new ContainerException($this->__('Could not check for key "%1$s"', [$id]), 0, $e, $this);
        }
    }
    /**
     * @inheritDoc
     */
    public function set(string $key, $value): void
    {
        try {
            $this->setOption($key, $value);
        } catch (Exception $e) {
            throw new ContainerException($this->__('Could not set value for key "%1$s"', [$key]), 0, $e, $this);
        }
    }
    /**
     * @inheritDoc
     */
    public function unset(string $key): void
    {
        $blogId = $this->blogId;
        $result = $blogId === null ? delete_option($key) : delete_blog_option($blogId, $key);
        if ($result === \false) {
            throw new ContainerException($this->__('Could not delete option "%1$s" for blog %2$s', [$key, $blogId === null ? 'null' : "#{$blogId}"]), 0, null, $this);
        }
    }
    /**
     * Retrieves an option value.
     *
     * @param string $name The name of the option to retrieve.
     *
     * @return mixed The option value.
     *
     * @throws UnexpectedValueException If the option value matches the configured default.
     * @throws RuntimeException If problem retrieving.
     * @throws Exception If problem running.
     */
    protected function getOption(string $name)
    {
        $blogId = $this->blogId;
        $default = $this->default;
        /** @psalm-suppress PossiblyNullArgument */
        $value = $blogId === null ? get_option($name, $default) : get_blog_option($blogId, $name, $default);
        if ($value === $default) {
            throw new UnexpectedValueException($this->__('Option "%1$s" for blog %2$s does not exist', [$name, $blogId === null ? 'null' : "#{$blogId}"]));
        }
        return $value;
    }
    /**
     * Assigns a value to an option.
     *
     * @param string $name The name of the option to set the value for.
     * @param mixed $value The value to set.
     *
     * @throws UnexpectedValueException If new option value does not match what was being set.
     * @throws RuntimeException If problem setting.
     * @throws Exception If problem running.
     */
    protected function setOption(string $name, $value): void
    {
        $blogId = $this->blogId;
        /** @psalm-suppress PossiblyNullArgument */
        $isSuccessful = $blogId === null ? update_option($name, $value) : update_blog_option($blogId, $name, $value);
        if (!$isSuccessful) {
            $newValue = $this->getOption($name);
            $isSuccessful = $value === $newValue;
        }
        /** @psalm-suppress PossiblyUndefinedVariable If not successful, it will always be defined */
        if (!$isSuccessful) {
            throw new UnexpectedValueException($this->__('New option value did not match the intended value in blog %3$s: "%1$s" VS "%2$s"', [print_r($value, \true), print_r($newValue, \true), $blogId === null ? 'null' : "#{$blogId}"]));
        }
    }
}
