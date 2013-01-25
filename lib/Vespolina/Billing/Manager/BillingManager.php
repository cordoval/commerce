<?php
/**
 * (c) 2013 Vespolina Project http://www.vespolina-project.org
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Vespolina\Billing\Manager;

use Vespolina\Entity\Billing\BillingAgreement;
use Vespolina\Entity\Order\OrderInterface;
use Vespolina\Entity\Partner\PartnerInterface;
use Vespolina\Order\Manager\OrderManager;
use Vespolina\Billing\Gateway\BillingAgreementGatewayInterface;

class BillingManager implements BillingManagerInterface
{
    protected $gateway;
    protected $orderManager;

    public function __construct(BillingAgreementGatewayInterface $gateway, OrderManager $orderManager)
    {
        $this->gateway = $gateway;
        $this->orderManager = $orderManager;
    }

    /**
     * @inheritdoc
     */
    public function processOrder(OrderInterface $order)
    {
        $billingAgreements = $this->createBillingAgreements($order);

        // order already processed
        if (count($billingAgreements) == 0) {
            return false;
        }

        // process further

        return true;
     }

    /**
     * @inheritdoc
     */
    public function createBillingAgreements(OrderInterface $order)
    {
        /** @var $item */
        foreach ($order->getItems() as $item) {

            $pricingSet = $item->getPricing();
            $startDate = new \DateTime('now');

            $billingAgreement = new BillingAgreement();
            $billingAgreement
                ->setPaymentGateway($order->getAttribute('payment_gateway'))
                ->setPartner($order->getOwner())
                ->setInitialBillingDate(new \DateTime('now'))
                ->setBillingAmount($recurringValue)
                ->setOrderItem($item)
            ;
        }

        return array();
    }

    /**
     * @inheritdoc
     */
    function createBillingRequest(PartnerInterface $partner)
    {
        $orderArray = $this->orderManager->findClosedOrdersByOwner($partner);
        //$orderArray->toArray();
    }

    /**
     * @inheritdoc
     */
    public function findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
    {
        return $this->doFindBy($criteria, $orderBy, $limit, $offset);
    }

    /**
     * Finds billing agreements that are due
     *
     * @return array
     */
    public function findEligibleBillingAgreements($limit, $page = 1)
    {
        $offset = ($page - 1) * $limit;

        /** @var \Molino\Doctrine\ORM\SelectQuery $query  */
        $query = $this->gateway->createQuery('select');
        $qb = $query->getQueryBuilder();

        $now = new \DateTime();

        return $qb
            ->andWhere('m.active = ?1')
            ->andWhere('m.nextBillingDate <= ?2')
            ->setParameters(array(
                    1 => 1,
                    2 => $now->format('Y-m-d H:i:s')
                ))
            ->setFirstResult($offset)
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * @todo add implementation, please don't forget to call $em->clear() after each batch
     * @param array $billingAgreements
     */
    public function processEligibleBillingAgreements(array $billingAgreements)
    {

    }


    /**
     * @inheritdoc
     */
    protected function doFindBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
    {
        /** @var \Molino\Doctrine\ORM\SelectQuery $query  */
        $query = $this->gateway->createQuery('Select');
        $qb = $query->getQueryBuilder();

        foreach($criteria as $field => $value) {
            $qb->field($field)->equals($value);
        }
        if ($orderBy) {
            $qb->orderBy($orderBy);
        }
        if ($limit) {
            $qb->limit($limit);
        }
        if ($offset) {
            $qb->offset($offset);
        }
        $query = $qb->getQuery();

        return $this->gateway->findOrders($query);
    }
}
