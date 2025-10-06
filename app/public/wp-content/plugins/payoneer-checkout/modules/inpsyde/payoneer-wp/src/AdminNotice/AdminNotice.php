<?php

declare (strict_types=1);
namespace Syde\Vendor\Inpsyde\PayoneerForWoocommerce\Wp\AdminNotice;

// phpcs:disable Inpsyde.CodeQuality.NoAccessors.NoSetter -- this is a DTO, setters are intentional
// phpcs:disable Inpsyde.CodeQuality.NoAccessors.NoGetter -- also getters are intentional
class AdminNotice
{
    public const TYPE_SUCCESS = 'notice-success';
    public const TYPE_WARNING = 'notice-warning';
    public const TYPE_ERROR = 'notice-error';
    public const TYPE_INFO = 'notice-info';
    public const VALID_TYPES = [self::TYPE_SUCCESS, self::TYPE_WARNING, self::TYPE_ERROR, self::TYPE_INFO];
    public const STYLE_ALT = 'notice-alt';
    public const STYLE_INLINE = 'below-h2';
    private string $content = '';
    private string $type = self::TYPE_INFO;
    private array $additionalClasses = [];
    public function setContent(string $content): self
    {
        $this->content = $content;
        return $this;
    }
    public function setType(string $type): self
    {
        if (!in_array($type, self::VALID_TYPES, \true)) {
            throw new \InvalidArgumentException("Invalid notice type: {$type}");
        }
        $this->type = $type;
        return $this;
    }
    public function addClass(string $class): self
    {
        $this->additionalClasses[] = $class;
        return $this;
    }
    public function getContent(): string
    {
        return $this->content;
    }
    public function getClasses(): array
    {
        return array_merge([$this->type], $this->additionalClasses);
    }
}
