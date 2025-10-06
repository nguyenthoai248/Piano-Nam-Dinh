<?php

declare (strict_types=1);
namespace Syde\Vendor\Inpsyde\PayoneerForWoocommerce\AssetCustomizer;

use Exception;
use Syde\Vendor\Inpsyde\PayoneerForWoocommerce\Cache\FileCacheInterface;
use Syde\Vendor\Inpsyde\PayoneerForWoocommerce\Filesystem\FileStreamFactoryInterface;
use Syde\Vendor\Inpsyde\PayoneerForWoocommerce\Filesystem\HasherInterface;
use Syde\Vendor\Inpsyde\PayoneerForWoocommerce\Filesystem\PathResolverInterface;
use Syde\Vendor\Inpsyde\PayoneerForWoocommerce\Filesystem\UrlResolverInterface;
use Syde\Vendor\Inpsyde\PayoneerForWoocommerce\Template\PathTemplateFactoryInterface;
use Syde\Vendor\Inpsyde\PayoneerForWoocommerce\Template\TemplateInterface;
use Syde\Vendor\Psr\Http\Message\StreamInterface;
use Syde\Vendor\Psr\Http\Message\UriInterface;
use RuntimeException;
use Stringable;
/**
 * @psalm-type MapOfScalars = array<string, Stringable|scalar|array<string, Stringable|scalar>>
 * @psalm-type Options = array<string, Stringable|scalar|MapOfScalars>
 * @psalm-type Context = array<string, Stringable|scalar|Options>
 */
class FileTemplateAssetProcessor implements AssetProcessorInterface
{
    /**
     * @var PathResolverInterface
     */
    protected $pathResolver;
    /**
     * @var UrlResolverInterface
     */
    protected $urlResolver;
    /**
     * @var FileCacheInterface
     */
    protected $fileCache;
    /**
     * @var HasherInterface
     */
    protected $hasher;
    /**
     * @var FileStreamFactoryInterface
     */
    protected $fileStreamFactory;
    /**
     * @var PathTemplateFactoryInterface
     */
    protected $pathTemplateFactory;
    public function __construct(PathResolverInterface $pathResolver, UrlResolverInterface $urlResolver, FileCacheInterface $fileCache, HasherInterface $hasher, FileStreamFactoryInterface $fileStreamFactory, PathTemplateFactoryInterface $pathTemplateFactory)
    {
        $this->pathResolver = $pathResolver;
        $this->urlResolver = $urlResolver;
        $this->fileCache = $fileCache;
        $this->hasher = $hasher;
        $this->fileStreamFactory = $fileStreamFactory;
        $this->pathTemplateFactory = $pathTemplateFactory;
    }
    /**
     * @inheritDoc
     */
    public function process(string $path, array $options) : UriInterface
    {
        try {
            $sourcePath = $this->resolveSourcePath($path);
            $fileHash = $this->getFileHash($sourcePath);
            $realPath = $this->getCachedFilePath($path, ['options' => $options, 'content_hash' => $fileHash], function () use($sourcePath, $options) : StreamInterface {
                $template = $this->createTemplateFromPath($sourcePath);
                $processedAsset = $template->render($options);
                return $processedAsset;
            });
            $url = $this->resolveDestinationUrl($realPath);
            return $url;
        } catch (Exception $exc) {
            if (!$exc instanceof RuntimeException) {
                $exc = new RuntimeException(sprintf('Could not process asset "%1$s"', $path), 0, $exc);
            }
            throw $exc;
        }
    }
    /**
     * Resolves a path to a filesystem path.
     *
     * @param string $path The path to resolve.
     *
     * @return string The resolved path.
     * @throws RuntimeException If problem resolving.
     */
    protected function resolveSourcePath(string $path) : string
    {
        return $this->pathResolver->resolvePath($path);
    }
    /**
     * Resolves a path to a URL.
     *
     * @param string $path The path to resolve.
     *
     * @return UriInterface The resolved URL.
     * @throws RuntimeException If problem resolving.
     */
    protected function resolveDestinationUrl(string $path) : UriInterface
    {
        return $this->urlResolver->resolveUrl($path);
    }
    /**
     * Retrieves a path to the cached file with generated contents
     * for the specified key and context.
     *
     * @param string $key The key to get the file path for.
     * @param array<string, mixed> $context The file context.
     * @psalm-param Context $context
     * @param callable(mixed): StreamInterface $generator The generator of contents.
     * @psalm-param callable(Context): StreamInterface $generator
     *
     * @return string The path to the cached file.
     * @throws RuntimeException If problem retrieving.
     */
    protected function getCachedFilePath(string $key, array $context, callable $generator) : string
    {
        return $this->fileCache->getCachedFile($key, $context, $generator);
    }
    /**
     * Retrieves the hash of a file at the specified path.
     *
     * @param string $path The path to the file to hash.
     *
     * @return string The hash.
     * @throws RuntimeException If problem hashing.
     */
    protected function getFileHash(string $path) : string
    {
        $stream = $this->fileStreamFactory->createStreamFromFile($path, 'r');
        try {
            $hash = $this->hasher->getHash($stream);
        } catch (Exception $exc) {
            throw new RuntimeException(sprintf('Could not hash file at path "%1"', $path), 0, $exc);
        }
        return $hash;
    }
    /**
     * Creates a template from the specified path.
     *
     * @param string $path The path to the template.
     *
     * @return TemplateInterface The new template.
     */
    protected function createTemplateFromPath(string $path) : TemplateInterface
    {
        return $this->pathTemplateFactory->createTemplateFromPath($path);
    }
}
