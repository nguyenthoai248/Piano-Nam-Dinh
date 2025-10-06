<?php

declare (strict_types=1);
namespace Syde\Vendor\Inpsyde\PayoneerForWoocommerce\Filesystem;

use HashContext;
use RangeException;
use UnexpectedValueException;
/**
 * A hash context that uses PHP's native {@link HashContext}.
 */
class NativeHashContext implements HashContextInterface
{
    /**
     * @var string
     */
    protected $algo;
    /**
     * @var HashContext|resource|null
     */
    protected $context;
    public function __construct(string $algo)
    {
        $this->algo = $algo;
    }
    /**
     * @inheritDoc
     */
    public function init() : void
    {
        if ($this->context !== null) {
            throw new RangeException('Context with algo "%1$s" cannot be re-initialized');
        }
        $algo = $this->algo;
        $context = hash_init($this->algo);
        if (!$context) {
            throw new UnexpectedValueException(sprintf('Failed to initialize hashing context with algo "%1$s"', $algo));
        }
        $this->context = $context;
    }
    /**
     * @inheritDoc
     */
    public function update(string $data) : void
    {
        $context = $this->context;
        $algo = $this->algo;
        if ($context === null) {
            throw new RangeException(sprintf('Context with algo "%1$s" must be initialized before it can be updated', $algo));
        }
        /** @psalm-suppress PossiblyInvalidArgument */
        $result = hash_update($context, $data);
        if (!$result) {
            throw new UnexpectedValueException(sprintf('Failed to update context with algo "%1$s" with %2$d bits of data', $algo, strlen($data)));
        }
    }
    /**
     * @inheritDoc
     */
    public function copy() : HashContextInterface
    {
        $new = clone $this;
        $context = $this->context;
        /** @psalm-suppress PossiblyInvalidArgument */
        $newContext = hash_copy($context);
        $new->context = $newContext;
        return $new;
    }
    /**
     * @inheritDoc
     */
    public function finalize(bool $isHex) : string
    {
        $algo = $this->algo;
        $context = $this->context;
        if ($context === null) {
            throw new RangeException(sprintf('Context with algo "%1$s" is not initialized', $algo));
        }
        /** @psalm-suppress PossiblyInvalidArgument */
        $hash = hash_final($context, !$isHex);
        return $hash;
    }
}
