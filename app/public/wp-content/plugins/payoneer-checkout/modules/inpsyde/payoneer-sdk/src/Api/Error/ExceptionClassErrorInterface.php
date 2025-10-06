<?php

declare (strict_types=1);
namespace Syde\Vendor\Inpsyde\PayoneerSdk\Api\Error;

use RuntimeException;
use Throwable;
/**
 * An error that can have its exception class configured.
 *
 * @template E of Throwable
 */
interface ExceptionClassErrorInterface extends ErrorInterface
{
    /**
     * Configures a new instance with the specified exception FQCN.
     *
     * @param class-string<E> $fqcn The exception FQCN.
     *
     * @return static The new instance.
     *
     * @throws RuntimeException If problem configuring.
     */
    public function withExceptionClass(string $fqcn): self;
}
