<?php

declare (strict_types=1);
namespace Syde\Vendor\Inpsyde\PayoneerForWoocommerce\WebSdk\Security;

/**
 * Service for managing Subresource Integrity (SRI) validation of the WebSDK.
 *
 * This service handles:
 * 1. Mapping environment names (sandbox/live) to specific SDK versions
 * 2. Providing integrity hashes for script validation
 * 3. Resolving the appropriate hash based on script version and format (.js/.min.js)
 *
 * Versions and hashes that require SRI hashes are documented here:
 * https://checkoutdocs.payoneer.com/docs/getting-started-checkout-web-sdk#pci-dss-compliance
 */
class SdkIntegrityService
{
    /**
     * @var string
     */
    private string $jsExtension;
    /**
     * @var string|null
     */
    private ?string $version = null;
    /**
     * Translate environments to their corresponding version.
     *
     * @var array<string, string>
     */
    private array $environmentVersionMap;
    /**
     * Provides two functions:
     *
     * First, defines a list of valid versions that can be used.
     * Second, provides the SRI validation hash for each version.
     *
     * @var array<string, array<string, string>>
     */
    private array $integrityHashes;
    /**
     * @param string $jsExtension
     * @param array<string, string> $environmentVersionMap
     * @param array<string, array<string, string>> $integrityHashes
     */
    public function __construct(string $jsExtension, array $environmentVersionMap, array $integrityHashes)
    {
        $this->jsExtension = $jsExtension;
        $this->environmentVersionMap = $environmentVersionMap;
        $this->integrityHashes = $integrityHashes;
    }
    /**
     * Set version based on environment or explicit version.
     *
     * The param can be a specific version number, or an environment name, which is mapped to an
     * absolute version number.
     *
     * @param string $environmentOrVersion
     *
     * @return void
     */
    public function setVersion(string $environmentOrVersion): void
    {
        // Try to map an environment name to a SDK version.
        if (isset($this->environmentVersionMap[$environmentOrVersion])) {
            $environmentOrVersion = $this->environmentVersionMap[$environmentOrVersion];
        }
        // Check, if the provided or mapped script version is valid.
        if (isset($this->integrityHashes[$environmentOrVersion])) {
            $this->version = $environmentOrVersion;
            return;
        }
        // The version is invalid, fall back to the latest version.
        $this->version = null;
    }
    /**
     * Get the resolved version, always a "X.Y.Z" version, or null, if an invalid (or no) version
     * was set.
     *
     * @return string|null
     */
    public function getVersion(): ?string
    {
        return $this->version;
    }
    /**
     * Get the integrity hash for the current version and JS extension
     *
     * @return string|null
     */
    public function getHash(): ?string
    {
        if ($this->version === null || !isset($this->integrityHashes[$this->version])) {
            return null;
        }
        return $this->integrityHashes[$this->version][$this->jsExtension] ?? null;
    }
}
