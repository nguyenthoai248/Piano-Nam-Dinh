<?php

declare (strict_types=1);
namespace Syde\Vendor\Inpsyde\PayoneerForWoocommerce\Checkout\MisconfigurationDetector;

use Syde\Vendor\Inpsyde\PayoneerSdk\Api\Command\ResponseValidator\ValidationFailure;
use Throwable;
class MisconfigurationDetector implements MisconfigurationDetectorInterface
{
    protected const RETURN_CODE_NAME = 'INVALID_CONFIGURATION';
    /**
     * @inheritDoc
     */
    public function isCausedByMisconfiguration(Throwable $throwable): bool
    {
        $initial = $this->getInitialThrowable($throwable);
        if ($initial->getCode() === 401) {
            return \true;
        }
        if (!$initial instanceof ValidationFailure) {
            return \false;
        }
        $resultCode = $this->getReturnCodeName($initial);
        return $resultCode === self::RETURN_CODE_NAME;
    }
    protected function getInitialThrowable(Throwable $throwable): Throwable
    {
        do {
            $previous = $throwable->getPrevious();
            $throwable = $previous ?: $throwable;
        } while ($previous instanceof Throwable);
        return $throwable;
    }
    /**
     * @param ValidationFailure $exception
     *
     * @return string
     */
    protected function getReturnCodeName(ValidationFailure $exception): string
    {
        $response = $exception->getSubject();
        $response->getBody()->rewind();
        $responseData = json_decode($response->getBody()->getContents(), \true);
        if (is_array($responseData) && isset($responseData['returnCode']['name'])) {
            return (string) $responseData['returnCode']['name'];
        }
        return '';
    }
}
