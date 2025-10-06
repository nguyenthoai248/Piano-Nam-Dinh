<?php

declare (strict_types=1);
namespace Syde\Vendor\Inpsyde\PayoneerSdk\Api\Error;

use RuntimeException;
use Throwable;
/**
 * An exception builder.
 *
 * @template E of Throwable
 */
interface ErrorInterface
{
    /**
     * Configures a new instance with the specified message.
     *
     * @param string $message The message.
     *
     * @return static The new instance.
     *
     * @throws RuntimeException If problem configuring.
     */
    public function withMessage(string $message): self;
    /**
     * Configures a new instance with the specified inner exception.
     *
     * @param Throwable $exception The exception.
     * @return static The new instance.
     *
     * @throws RuntimeException If problem configuring.
     */
    public function withInnerException(Throwable $exception): self;
    /**
     * Configures a new instance with the specified exception code.
     *
     * @param int $code The code.
     * @return static The new instance.
     *
     * @throws RuntimeException If problem configuring.
     */
    public function withCode(int $code): self;
    /**
     * Retrieves a new exception, according to this instance's configuration.
     *
     * @return E The new exception.
     *
     * @throws RuntimeException If problem retrieving.
     */
    public function createException(): Throwable;
}
