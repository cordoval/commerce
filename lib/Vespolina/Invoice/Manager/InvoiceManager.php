<?php
/**
 * (c) 2012 Vespolina Project http://www.vespolina-project.org
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Vespolina\Invoice\Manager;

use Vespolina\Entity\InvoiceInterface;
use Vespolina\Entity\Partner\PartnerInterface;
use Vespolina\Invoice\Gateway\InvoiceGateway;

/**
 * @author Richard Shank <develop@zestic.com>
 */
class InvoiceManager implements InvoiceManagerInterface
{
    private $invoiceClass;
    protected $gateway;

    public function __construct(InvoiceGateway $gateway, $invoiceClass)
    {
        if (!class_exists($invoiceClass)) {
            throw new InvalidConfigurationException(sprintf("Class '%s' not found", $invoiceClass));
        }

        $this->gateway = $gateway;
        $this->invoiceClass = $invoiceClass;
    }

    /**
     * @inheritdoc
     */
    public function createInvoice()
    {
        $invoice = new $this->invoiceClass();

        return $invoice;
    }

    /**
     * @inheritdoc
     */
    public function findById($id)
    {
        return $this->createSelectQuery()
            ->filterEqual('id', $id)
            ->one()
        ;
    }

    /**
     * @inheritdoc
     */
    public function findAllInvoicesByPartner(PartnerInterface $partner)
    {
        $query = $this->gateway->createQuery('Select');
        $query->filterEqual('partner', $partner)
            ->sort('periodEnd', 'desc')
            ->all()
        ;
    }

    /**
     * @inheritdoc
     */
    public function findInvoicesByPartnerAndPeriod($partner, $periodStart, $periodEnd)
    {
        $query = $this->gateway->createQuery('Select');
        $query->filterEqual('partner', $partner)
            ->filterEqual('periodStart', $periodStart)
            ->filterEqual('periodEnd', $periodEnd)
            ->one()
        ;
    }
}
