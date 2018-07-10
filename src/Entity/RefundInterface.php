<?php

declare(strict_types=1);

namespace Sylius\RefundPlugin\Entity;

use Sylius\Component\Resource\Model\ResourceInterface;

interface RefundInterface extends ResourceInterface
{
    public function getOrderNumber(): ?string;

    public function setOrderNumber(?string $orderNumber): void;

    public function getAmount(): ?int;

    public function setAmount(?int $amount): void;

    public function getRefundedUnitId(): ?int;

    public function setRefundedUnitId(?int $refundedUnitId): void;
}