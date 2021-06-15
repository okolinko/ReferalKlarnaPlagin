<?php

namespace Luxinten\ReferalKlarnaPlagin\Plagin;

use Magento\Framework\Exception\LocalizedException as KlarnaApiException;
use Magento\Checkout\Model\Session as CheckoutSession;
use Klarna\Kp\Model\Api\RequestFactory;

class Builder
{
    private $checkoutSession;
    private $requestFactory;

    public function __construct(
        RequestFactory $requestFactory,
        CheckoutSession $checkoutSession
    ) {
        $this->checkoutSession = $checkoutSession;
        $this->requestFactory = $requestFactory;
    }


    public function aroundValidate($requiredAttributes, $type)
    {
        $missingAttributes = [];
        $requestData = $this->getRequest()->toArray();
        foreach ($requiredAttributes as $requiredAttribute) {
            if ('orderlines' == $requiredAttribute) {
                if (is_array($requestData['order_lines']) && count($requestData['order_lines']) === 0) {
                    $missingAttributes[] = $requiredAttribute;
                    continue;
                }
            }
            if (!isset($requestData[$requiredAttribute])) {
                continue ;
            }

            if (null === $requestData[$requiredAttribute]) {
                $missingAttributes[] = $requiredAttribute;
            }
            if (is_array($requestData[$requiredAttribute]) && count($requestData[$requiredAttribute]) === 0) {
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

        $quote = $this->checkoutSession->getQuote();
        $total = $quote->getGrandTotal();
        $total = intval(str_replace('.', '', number_format($total, 2, ".", '')));

        if ($total  !== $requestData['order_amount']) {
            throw new KlarnaApiException(
                __('Order line totals do not total order_amount - %1 != %2', $total, $requestData['order_amount'])
            );
        }

        return $this;
    }
}
