<?php

namespace VaxLtd\ProductFeed\Helper;

use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use VaxLtd\ProductFeed\Model\SftpWrapper;
use VaxLtd\ProductFeed\Helper\Data;
use VaxLtd\ProductFeed\Helper\Config;
use \Magento\Store\Model\StoreManagerInterface;
use Psr\Log\LoggerInterface;

class SftpExport extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * @var SftpWrapper
     */
    protected $sftp;

    /**
     * @var Data
     */
    protected $helper;


    /**
     * @var Config
     */
    protected $config;

    /**
     * @var CollectionFactory
     */
    protected $collectionFactory;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    protected $logger;


    public function __construct(
        SftpWrapper $sftp,
        Data $helper,
        Config $config,
        CollectionFactory $collectionFactory,
        StoreManagerInterface $storeManager,
        \Magento\Framework\App\Helper\Context $context,
        LoggerInterface $logger
    ) {
        $this->sftp = $sftp;
        $this->helper = $helper;
        $this->config = $config;
        $this->collectionFactory = $collectionFactory;
        $this->storeManager = $storeManager;
        $this->logger = $logger;
        parent::__construct($context);
    }

    public function export()
    {
        $productCollection = $this->collectionFactory->create();
        $productCollection->addStoreFilter(3);
        $productCollection->addAttributeToFilter('visibility', [
            \Magento\Catalog\Model\Product\Visibility::VISIBILITY_BOTH,
            \Magento\Catalog\Model\Product\Visibility::VISIBILITY_IN_CATALOG
        ]);

        $productCollection->addAttributeToFilter('enable_feed',  ['eq' => 1]);
        $productCollection->addAttributeToFilter('uk_item_condition_ebay_only',  ['eq' => 1000]);
        $productCollection->addFinalPrice();
        $productCollection->addAttributeToSelect('description')
            ->addAttributeToSelect('short_description')
            ->addAttributeToSelect('name')
            ->addAttributeToSelect('google_product_category')
            ->addAttributeToSelect('attribute_set_name')
            ->addAttributeToSelect('url_key')
            ->addAttributeToSelect('image')
            ->addAttributeToSelect('quantity')
            ->addAttributeToSelect('product_url')
            ->addAttributeToSelect('is_in_stock')
            ->addAttributeToSelect('uk_barcode')
            ->addAttributeToSelect('hero_cp_image')
            ->addAttributeToSelect('special_price')
            ->addAttributeToSelect('ean_code');

        $rows = [["id", "title", "description", "google_product_category", "product_type", "link", "image_link", "condition", "availability", "price", "sale_price", "brand", "mpn", "gtin", "custom_label_0", "is_bundle", "custom_label_1", "promotion_id", "energy_efficiency_class", "additional_image_link"]];

        foreach ($productCollection as $product) {
            if ($product->getDescription()) $description = strip_tags(htmlspecialchars_decode($product->getDescription()) ?? '');
            elseif ($product->getShortDescription()) $description = strip_tags(htmlspecialchars_decode($product->getShortDescription()) ?? '');
            else continue;

            $description = preg_replace('/&lt;style((.|\n|\r)*?)&lt;\/style&gt;/', '', $description);

            $additional_image = $product->getHeroCpImage() ? $this->storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA) . 'catalog/product' . $product->getHeroCpImage() : '';
            $image = $product->getImage() ? $this->storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA) . 'catalog/product' . $product->getImage() : '';

            $is_bundle = (stripos($product->getSku(), '1-P-') !== false ? "1" : ($product->getType() == "bundle")) ? 1 : 0;

            // stock
            $stock_status = $product->getStockStatus() ? "IN STOCK" : "OUT OF STOCK";
            $row = [
                $product->getSku(),
                $product->getName(),
                $description,
                htmlspecialchars($product->getGoogleProductCategory() ?? ''),
                $this->helper->getAttSetGoogleCat($product->getAttributeSetId()),
                $product->getProductUrl(),
                $image,
                'new',
                $stock_status,
                number_format($product->getPrice(), 2) . " GBP",
                $product->getSpecialPrice() ? number_format($product->getSpecialPrice(), 2) . " GBP" : '',
                'Vax',
                $product->getSku(),
                $product->getUkBarcode() ?? $product->getEanCode(),
                $this->helper->getAttSetGoogleCat($product->getAttributeSetId()),
                $is_bundle,
                '',  //custom_label1
                '',  //promotion id
                '', // energy efficiency

                $additional_image
            ];
            $rows[] = $row;
        }
        $filePath = $this->config->getLocalSavePath();

        if (!file_exists($filePath)) {
            mkdir($filePath, 0755, true);
        }
        $target =  "{$filePath}feedoptimise.csv";
        $fp = fopen($target, 'w');
        foreach ($rows as $product) {
            fputcsv($fp, $product);
        }
        fclose($fp);

        return $this->sftp->uploadfile($target);
    }
}
