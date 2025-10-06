<?php

namespace Syde\Vendor\Inpsyde\PayoneerSdk\Api\Command;

use Syde\Vendor\Inpsyde\PayoneerSdk\Api\Entities\Style\StyleInterface;
interface CreateListCommandInterface extends ListCommandInterface
{
    /**
     * @param StyleInterface $style
     *
     * @return static
     */
    public function withStyle(StyleInterface $style): self;
    /**
     * @param string $operationType
     *
     * @return $this
     */
    public function withOperationType(string $operationType): self;
    /**
     * @param string $integrationType
     *
     * @return $this
     */
    public function withIntegrationType(string $integrationType): self;
    /**
     * @param bool $allowDelete
     *
     * @return $this
     */
    public function withAllowDelete(bool $allowDelete): self;
    /**
     * @param int $ttl List Session Time To Live in minutes.
     *
     * @return $this
     */
    public function withTtl(int $ttl): self;
    public function getStyle(): ?StyleInterface;
    public function getOperationType(): ?string;
    public function getIntegrationType(): ?string;
    public function isAllowDelete(): bool;
    public function getTtl(): ?int;
}
