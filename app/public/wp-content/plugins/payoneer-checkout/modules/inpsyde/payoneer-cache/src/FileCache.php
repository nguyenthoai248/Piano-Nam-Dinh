<?php

declare (strict_types=1);
namespace Syde\Vendor\Inpsyde\PayoneerForWoocommerce\Cache;

use Exception;
use Syde\Vendor\Inpsyde\PayoneerForWoocommerce\Filesystem\FileSaverInterface;
use Syde\Vendor\Psr\Http\Message\StreamInterface;
use RangeException;
use RuntimeException;
use Stringable;
/**
 * A generic file cache.
 *
 * Allows customization of the cache file path resolution, file verification, and
 * persistence strategies via dependencies.
 *
 * @psalm-type MapOfScalars = array<string, Stringable|scalar|array<string, Stringable|scalar>>
 * @psalm-type Options = array<string, Stringable|scalar|MapOfScalars>
 * @psalm-type Context = array<string, Stringable|scalar|Options>
 */
class FileCache implements FileCacheInterface
{
    /**
     * @var CacheFilePathResolverInterface
     */
    protected $filePathResolver;
    /**
     * @var CacheFileValidatorInterface
     */
    protected $fileValidator;
    /**
     * @var FileSaverInterface
     */
    protected $fileSaver;
    /**
     * @param CacheFilePathResolverInterface $filePathResolver
     */
    public function __construct(CacheFilePathResolverInterface $filePathResolver, CacheFileValidatorInterface $fileValidator, FileSaverInterface $fileSaver)
    {
        $this->filePathResolver = $filePathResolver;
        $this->fileValidator = $fileValidator;
        $this->fileSaver = $fileSaver;
    }
    /**
     * @inheritDoc
     */
    public function getCachedFile(string $key, array $context, callable $generator) : string
    {
        $filePath = $this->getCacheFilePath($key, $context);
        try {
            $this->validateCacheFile($filePath);
        } catch (RangeException $validationError) {
            try {
                $content = $generator($context);
            } catch (Exception $generationError) {
                throw new RuntimeException(sprintf('Could not generate cache content for key "%1$s"', $key), 0, $generationError);
            }
            $this->save($content, $filePath);
        }
        return $filePath;
    }
    /**
     * Retrieve the path to the cache file.
     *
     * @param string $key The cache key.
     * @param array<string, mixed> $context The cache context.
     * @psalm-param Context $context
     *
     * @throws RuntimeException If problem retrieving.
     */
    protected function getCacheFilePath(string $key, array $context) : string
    {
        $fileName = $this->filePathResolver->resolveCacheFilePath($key, $context);
        return $fileName;
    }
    /**
     * Validates a cache file.
     *
     * @param string $filePath The path to the file.
     *
     * @throws RangeException If the path is invalid.
     * @throws RuntimeException If problem validating.
     */
    protected function validateCacheFile(string $filePath) : void
    {
        $this->fileValidator->validateCacheFile($filePath);
    }
    /**
     * Saves content to a file at the specified path.
     *
     * If the file does not exist, it will be created first.
     * Otherwise, it will be completely overwritten.
     *
     * @param StreamInterface $content The content to save.
     *
     * @throws RangeException If path is invalid.
     * @throws RuntimeException If problem saving.
     */
    protected function save(StreamInterface $content, string $filePath) : void
    {
        $this->fileSaver->saveFile($filePath, $content);
    }
}
