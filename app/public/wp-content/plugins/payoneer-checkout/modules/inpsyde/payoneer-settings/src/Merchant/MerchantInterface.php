<?php

declare (strict_types=1);
namespace Syde\Vendor\Inpsyde\PayoneerForWoocommerce\Settings\Merchant;

use Syde\Vendor\Psr\Http\Message\UriInterface;
use RuntimeException;
/**
 * Represents a Payoneer merchant
 */
interface MerchantInterface
{
    /**
     * Retrieves the ID associated with this instance.
     *
     * @return ?int The ID.
     * @psalm-return ?positive-int
     *
     * @throws RuntimeException If problem retrieving.
     */
    public function getId(): ?int;
    /**
     * Assigns the specified ID to a new instance.
     *
     * @param ?int $id The ID.
     * @psalm-param ?positive-int $id
     *
     * @return static The new instance.
     *
     * @throws RuntimeException If problem assigning.
     */
    public function withId(?int $id): MerchantInterface;
    /**
     * Retrieves the label associated with this instance.
     *
     * @return string The human-readable label.
     *
     * @throws RuntimeException If problem retrieving.
     */
    public function getLabel(): string;
    /**
     * Assigns the specified label to a new instance.
     *
     * @param string $label The label.
     *
     * @return static The new instance.
     *
     * @throws RuntimeException If problem assigning.
     */
    public function withLabel(string $label): MerchantInterface;
    /**
     * Retrieves the environment associated with this instance.
     *
     * @return string The merchant environment.
     *
     * @throws RuntimeException If problem retrieving.
     */
    public function getEnvironment(): string;
    /**
     * Assigns the specified environment to a new instance.
     *
     * @param string $environment The environment.
     *
     * @return static The new instance.
     *
     * @throws RuntimeException If problem assigning.
     */
    public function withEnvironment(string $environment): MerchantInterface;
    /**
     * Retrieves the code associated with this instance.
     *
     * @return string The code.
     *
     * @throws RuntimeException If problem retrieving.
     */
    public function getCode(): string;
    /**
     * Assigns the specified code to a new instance.
     *
     * @param string $code The code.
     *
     * @return static The new instance.
     *
     * @throws RuntimeException If problem assigning.
     */
    public function withCode(string $code): MerchantInterface;
    /**
     * Retrieves the division associated with this instance.
     *
     * @return string The code.
     *
     * @throws RuntimeException If problem retrieving.
     */
    public function getDivision(): string;
    /**
     * Assigns the specified division to a new instance.
     *
     * @param string $division The code.
     *
     * @return static The new instance.
     *
     * @throws RuntimeException If problem assigning.
     */
    public function withDivision(string $division): MerchantInterface;
    /**
     * Retrieves the token associated with this instance.
     *
     * @return string The token.
     *
     * @throws RuntimeException If problem retrieving.
     */
    public function getToken(): string;
    /**
     * Assigns the specified token to a new instance.
     *
     * @param string $token The token.
     *
     * @return static The new instance.
     *
     * @throws RuntimeException If problem assigning.
     */
    public function withToken(string $token): MerchantInterface;
    /**
     * Retrieves the base URL associated with this instance.
     *
     * @return UriInterface The base URL of the API which the merchant belongs to.
     *
     * @throws RuntimeException If problem retrieving.
     */
    public function getBaseUrl(): UriInterface;
    /**
     * Assigns the specified base URL to a new instance.
     *
     * @param string|UriInterface $baseUrl The URL.
     *
     * @return static The new instance.
     *
     * @throws RuntimeException If problem assigning.
     */
    public function withBaseUrl($baseUrl): MerchantInterface;
    /**
     * Retrieves the transaction URL template associated with this instance.
     *
     * @return string The template that can be used to derive a transaction URL.
     *     Placeholders:
     *  - %1$d: the ID of the transaction, for which to retrieve the URL.
     *
     * @throws RuntimeException If problem retrieving.
     */
    public function getTransactionUrlTemplate(): string;
    /**
     * Assigns the specified base URL to a new instance.
     *
     * @param string $transactionUrlTemplate The template.
     *
     * @return static The new instance.
     *
     * @throws RuntimeException If problem assigning.
     */
    public function withTransactionUrlTemplate(string $transactionUrlTemplate): MerchantInterface;
}
