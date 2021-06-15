<?php

namespace Luxinten\ReferalKlarnaPlagin\Plagin;

use Magento\Framework\Exception\LocalizedException as KlarnaApiException;
use Magento\Checkout\Model\Session as CheckoutSession;

class Builder
{
    private $checkoutSession;

    public function __construct(
        CheckoutSession $checkoutSession
    ) {
        $this->checkoutSession = $checkoutSession;
    }


    public function aroundValidate($requiredAttributes, $type)
    {
        $missingAttributes = [];
        foreach ($requiredAttributes as $requiredAttribute) {
            if (null === $this->$requiredAttribute) {
                $missingAttributes[] = $requiredAttribute;
            }
            if (is_array($this->$requiredAttribute) && count($this->$requiredAttribute) === 0) {
                $missingAttributes[] = $requiredAttribute;
            }
        }
        if (!empty($missingAttributes)) {
            throw new KlarnaApiException(
                __(
                    'Missing required attribute(s) on %1: "%2".',
                    $type,
                    implode(', ', $missingAttributes)
                )
            );
        }
//        $total = 0;
//        foreach ($this->orderlines as $orderLine) {
//            $total += (int)$orderLine->getTotal();
//        }
        $quote = $this->checkoutSession->getQuote();
        $total = $quote->getGrandTotal();
        $total = intval(str_replace('.', '', number_format($total, 2, ".", '')));

        if ($total !== $this->order_amount) {
            throw new KlarnaApiException(
                __('Order line totals do not total order_amount - %1 != %2', $total, $this->order_amount)
            );
        }

        return $this;
    }
}
