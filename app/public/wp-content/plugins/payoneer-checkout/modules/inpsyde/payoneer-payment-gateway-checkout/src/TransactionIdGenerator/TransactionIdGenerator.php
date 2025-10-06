<?php

declare (strict_types=1);
namespace Syde\Vendor\Inpsyde\PayoneerForWoocommerce\Checkout\TransactionIdGenerator;

class TransactionIdGenerator implements TransactionIdGeneratorInterface
{
    /**
     * @return string
     */
    public function generateTransactionId(): string
    {
        try {
            return $this->generateUuid();
        } catch (\Exception $exception) {
            $randomNumber = rand(0, getrandmax());
            return md5((string) $randomNumber);
        }
    }
    /**
     * @return string
     * @throws \Exception
     *
     * @see https://stackoverflow.com/a/15875555
     */
    protected function generateUuid(): string
    {
        $randomBytes = random_bytes(16);
        $randomBytes[6] = chr(ord($randomBytes[6]) & 0xf | 0x40);
        $randomBytes[8] = chr(ord($randomBytes[8]) & 0x3f | 0x80);
        return (string) vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($randomBytes), 4));
    }
}
