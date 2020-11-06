<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagUserPrice\Bundle\StoreFrontBundle\Service\Core;

use Shopware\Bundle\StoreFrontBundle\Service\CheapestPriceServiceInterface;
use Shopware\Bundle\StoreFrontBundle\Service\Core\CheapestPriceService;
use Shopware\Bundle\StoreFrontBundle\Struct;
use Shopware\Bundle\StoreFrontBundle\Service;
use Shopware\Bundle\StoreFrontBundle\Struct\ListProduct;
use Shopware\Bundle\StoreFrontBundle\Struct\Product\PriceRule;
use Shopware\Bundle\StoreFrontBundle\Struct\ProductContextInterface;
use SwagUserPrice\Components\AccessValidator;
use SwagUserPrice\Components\ServiceHelper;

/**
 * Plugin CheapestUserPriceService class.
 *
 * This class is an extension to the default CheapestPriceService.
 * We need this to inject the plugin-prices to the detail- and listing-page.
 *
 * @category Shopware
 * @package Shopware\Plugin\SwagUserPrice
 * @copyright Copyright (c) shopware AG (http://www.shopware.de)
 */
class CheapestUserPriceService implements CheapestPriceServiceInterface
{
    /**
     * @var CheapestPriceService
     */
    private $service;

    /**
     * @var AccessValidator
     */
    private $validator;

    /**
     * @var ServiceHelper
     */
    private $helper;

    public function __construct(
        CheapestPriceServiceInterface $service,
        AccessValidator $validator,
        ServiceHelper $helper
    ) {
        $this->service = $service;
        $this->validator = $validator;
        $this->helper = $helper;
    }

    /**
     * {@inheritdoc}
     */
    public function get(ListProduct $product, ProductContextInterface $context)
    {
        $cheapestPrices = $this->getList([$product], $context);

        return array_shift($cheapestPrices);
    }

    /**
     * {@inheritdoc}
     */
    public function getList($products, ProductContextInterface $context)
    {
        $products = $this->service->getList($products, $context);

        foreach ($products as $number => &$rule) {
            if (!$this->validator->validateProduct($number)) {
                continue;
            }
            $rule = $this->getCustomRule($rule, $number);
        }

        return $products;
    }

    /**
     * Builds a custom rule-struct.
     *
     * @param $rule PriceRule
     * @param $number
     * @return PriceRule
     */
    private function getCustomRule($rule, $number)
    {
        $price = $this->helper->getPrice($number);

        $customRule = $this->helper->buildRule($price);
        $customRule->setCustomerGroup($rule->getCustomerGroup());
        $customRule->setUnit($rule->getUnit());

        return $customRule;
    }
}
