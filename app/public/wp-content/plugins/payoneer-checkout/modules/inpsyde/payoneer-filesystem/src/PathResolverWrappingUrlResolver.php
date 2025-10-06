<?php

declare (strict_types=1);
namespace Syde\Vendor\Inpsyde\PayoneerForWoocommerce\Filesystem;

use Syde\Vendor\Psr\Http\Message\UriFactoryInterface;
use Syde\Vendor\Psr\Http\Message\UriInterface;
/**
 * Uses a path resolver for relative resolution, then ensures it's a URL via a default protocol.
 */
class PathResolverWrappingUrlResolver implements UrlResolverInterface
{
    use RegexTrait;
    /**
     * @var PathResolverInterface
     */
    protected $pathResolver;
    /**
     * @var UriFactoryInterface
     */
    protected $uriFactory;
    /**
     * @var string
     */
    protected $defaultProtocol;
    public function __construct(PathResolverInterface $pathResolver, UriFactoryInterface $uriFactory, string $defaultProtocol)
    {
        $this->pathResolver = $pathResolver;
        $this->uriFactory = $uriFactory;
        $this->defaultProtocol = $defaultProtocol;
    }
    /**
     * @inheritDoc
     */
    public function resolveUrl(string $path) : UriInterface
    {
        $path = $this->pathResolver->resolvePath($path);
        $defaultPrefix = "{$this->defaultProtocol}://";
        if (!$this->pregMatch('!^[\\w\\d]+://!', $path)) {
            $path = "{$defaultPrefix}{$path}";
        }
        $url = $this->uriFactory->createUri($path);
        return $url;
    }
}
