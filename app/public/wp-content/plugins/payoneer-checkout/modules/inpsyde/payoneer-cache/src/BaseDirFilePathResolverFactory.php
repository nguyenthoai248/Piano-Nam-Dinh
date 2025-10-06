<?php

declare (strict_types=1);
namespace Syde\Vendor\Inpsyde\PayoneerForWoocommerce\Cache;

use Syde\Vendor\Inpsyde\PayoneerForWoocommerce\Filesystem\HasherInterface;
use Syde\Vendor\Inpsyde\PayoneerForWoocommerce\Filesystem\StringStreamFactoryInterface;
/**
 * Can create a path resolver from a base directory.
 */
class BaseDirFilePathResolverFactory implements BaseDirFilePathResolverFactoryInterface
{
    /**
     * @var HasherInterface
     */
    protected $hasher;
    /**
     * @var string
     */
    protected $segmentSeparator;
    /**
     * @var StringStreamFactoryInterface
     */
    protected $stringStreamFactory;
    public function __construct(HasherInterface $hasher, string $segmentSeparator, StringStreamFactoryInterface $stringStreamFactory)
    {
        $this->hasher = $hasher;
        $this->segmentSeparator = $segmentSeparator;
        $this->stringStreamFactory = $stringStreamFactory;
    }
    /**
     * @inheritDoc
     */
    public function createFilePathResolverFromBaseDir(string $baseDir) : CacheFilePathResolverInterface
    {
        $product = new BaseDirHashingCacheFilePathResolver($baseDir, $this->hasher, $this->segmentSeparator, $this->stringStreamFactory);
        $product = new KeyHashPrependingCacheFilePathResolver($product, $this->hasher, $this->segmentSeparator, $this->stringStreamFactory);
        $product = new ExtensionPreservingCacheFilePathResolver($product);
        return $product;
    }
}
