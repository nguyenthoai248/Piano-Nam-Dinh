<?php

declare (strict_types=1);
namespace Syde\Vendor\Inpsyde\PayoneerForWoocommerce\Cache;

use Syde\Vendor\Inpsyde\PayoneerForWoocommerce\Filesystem\FileSaverInterface;
/**
 * Can create a file cache.
 */
class BaseDirFileCacheFactory implements FileCacheFactoryInterface
{
    /**
     * @var BaseDirFilePathResolverFactoryInterface
     */
    protected $filePathResolverFactory;
    /**
     * @var CacheFileValidatorInterface
     */
    protected $fileValidator;
    /**
     * @var FileSaverInterface
     */
    protected $fileSaver;
    public function __construct(BaseDirFilePathResolverFactoryInterface $filePathResolverFactory, CacheFileValidatorInterface $fileValidator, FileSaverInterface $fileSaver)
    {
        $this->filePathResolverFactory = $filePathResolverFactory;
        $this->fileValidator = $fileValidator;
        $this->fileSaver = $fileSaver;
    }
    /**
     * @inheritDoc
     */
    public function createFileCacheFromBaseDir(string $baseDir) : FileCacheInterface
    {
        $filePathResolver = $this->filePathResolverFactory->createFilePathResolverFromBaseDir($baseDir);
        $product = new FileCache($filePathResolver, $this->fileValidator, $this->fileSaver);
        return $product;
    }
}
