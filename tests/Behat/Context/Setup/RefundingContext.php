<?php

declare(strict_types=1);

namespace Tests\Sylius\RefundPlugin\Behat\Context\Setup;

use Behat\Behat\Context\Context;
use Prooph\ServiceBus\CommandBus;
use Sylius\Component\Core\Model\AdjustmentInterface;
use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Core\Model\OrderItemUnitInterface;
use Sylius\Component\Core\Model\ShipmentInterface;
use Sylius\Component\Core\Repository\OrderRepositoryInterface;
use Sylius\RefundPlugin\Command\RefundUnits;
use Webmozart\Assert\Assert;

final class RefundingContext implements Context
{
    /** @var OrderRepositoryInterface */
    private $orderRepository;

    /** @var CommandBus */
    private $commandBus;

    public function __construct(OrderRepositoryInterface $orderRepository, CommandBus $commandBus)
    {
        $this->orderRepository = $orderRepository;
        $this->commandBus = $commandBus;
    }

    /**
     * @Given /^(\d)st "([^"]+)" product from order "#([^"]+)" has been already refunded$/
     */
    public function productFromOrderHasBeenAlreadyRefunded(int $unitNumber, string $productName, string $orderNumber): void
    {
        /** @var OrderInterface $order */
        $order = $this->orderRepository->findOneByNumber($orderNumber);
        Assert::notNull($order);

        $unitsWithProduct = $order->getItemUnits()->filter(function(OrderItemUnitInterface $unit) use ($productName): bool {
            return $unit->getOrderItem()->getProductName() === $productName;
        });

        $unit = $unitsWithProduct->get($unitNumber-1);

        $this->commandBus->dispatch(new RefundUnits($orderNumber, [$unit->getId()], []));
    }

    /**
     * @Given all units from the order :orderNumber are refunded
     */
    public function allUnitsFromOrderAreRefunded(string $orderNumber): void
    {
        /** @var OrderInterface $order */
        $order = $this->orderRepository->findOneByNumber($orderNumber);
        Assert::notNull($order);

        $orderItemUnits = array_map(function(OrderItemUnitInterface $unit) {
            return $unit->getId();
        }, $order->getItemUnits()->getValues());

        $this->commandBus->dispatch(new RefundUnits($orderNumber, $orderItemUnits, []));
    }

    /**
     * @Given the shipping of the order :orderNumber is refunded
     */
    public function shippingOfTheOrderIsRefunded(string $orderNumber): void
    {
        /** @var OrderInterface $order */
        $order = $this->orderRepository->findOneByNumber($orderNumber);
        Assert::notNull($order);

        $shipment = $order->getAdjustments(AdjustmentInterface::SHIPPING_ADJUSTMENT)->first();

        $this->commandBus->dispatch(new RefundUnits($orderNumber, [], [$shipment->getId()]));
    }
}
