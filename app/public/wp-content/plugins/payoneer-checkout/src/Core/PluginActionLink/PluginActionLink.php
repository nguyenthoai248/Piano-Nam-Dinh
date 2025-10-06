<?php

declare (strict_types=1);
namespace Syde\Vendor\Inpsyde\PayoneerForWoocommerce\Core\PluginActionLink;

use Syde\Vendor\Psr\Http\Message\UriInterface;
class PluginActionLink
{
    /**
     * @var string
     */
    private $slug;
    /**
     * @var string
     */
    private $label;
    /**
     * @var UriInterface
     */
    private $uri;
    public function __construct(string $slug, string $label, UriInterface $uri)
    {
        $this->slug = $slug;
        $this->label = $label;
        $this->uri = $uri;
    }
    public function slug(): string
    {
        return $this->slug;
    }
    public function __toString(): string
    {
        return sprintf('<a href="%s">%s</a>', esc_url((string) $this->uri), esc_html($this->label));
    }
}
