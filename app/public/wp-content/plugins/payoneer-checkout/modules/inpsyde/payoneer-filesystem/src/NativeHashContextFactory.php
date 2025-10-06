<?php

declare (strict_types=1);
namespace Syde\Vendor\Inpsyde\PayoneerForWoocommerce\Filesystem;

use RangeException;
use RuntimeException;
/**
 * Can create a native hash context.
 */
class NativeHashContextFactory implements HashContextFactoryInterface
{
    /**
     * @var string
     */
    protected $algo;
    public function __construct(string $algo)
    {
        $this->validateAlgo($algo);
        $this->algo = $algo;
    }
    /**
     * @inheritDoc
     */
    public function createHashContext() : HashContextInterface
    {
        return new NativeHashContext($this->algo);
    }
    /**
     * Validates a hashing algo name.
     *
     * @param string $algo The name of the algo.
     *
     * @throws RangeException If algo is invalid.
     * @throws RuntimeException If problem validating.
     */
    protected function validateAlgo(string $algo) : void
    {
        $algos = hash_algos();
        if (!in_array($algo, $algos, \true)) {
            throw new RangeException(sprintf('Algo "%1$s" not in list "%2$s"', $algo, implode(', ', $algos)));
        }
    }
}
