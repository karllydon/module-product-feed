<?php

namespace VaxLtd\ProductFeed\Model;

use \Magento\Store\Model\StoreManagerInterface;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use VaxLtd\ProductFeed\Helper\Data;
use \Magento\CatalogInventory\Helper\Stock;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Tax\Model\Calculation;
use Magento\Tax\Model\Config;
use Magento\Catalog\Model\ResourceModel\Product;
use Magento\Catalog\Helper\Image;
use Magento\CatalogRule\Model\ResourceModel\Rule;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Eav\Model\Entity\Attribute\SetFactory;
use Magento\Framework\App\ProductMetadataInterface;
use Magento\Catalog\Model\Product\Gallery\ReadHandler;
use Magento\Framework\App\ResourceConnection;

class ProductFeed
{

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var CollectionFactory
     */
    protected $collectionFactory;

    /**
     * @var Data
     */
    protected $helper;


    /**
     * @var Stock
     */
    protected $stockHelper;

    /**
     * @var DirectoryList
     */
    protected $directoryList;

    /**
     * @var ProductRepositoryInterface
     */
    protected $productRepo;

    /**
     * @var Config
     */
    protected $taxConfig;

    /**
     * @var Calculation
     */
    protected $taxCalculation;

    /**
     * @var Product
     */
    protected $resourceProduct;

    /**
     * @var Image
     */
    protected $imageHelper;

    /**
     * @var  \VaxLtd\ProductFeed\Model\Profile 
     */
    protected $profile;

    /**
     * @var Rule
     */
    protected $ruleResource;

    /**
     * @var TimezoneInterface
     */
    protected $timezone;


    /**
     * @var SetFactory
     */
    protected $attributeSetFactory;

    /**
     * @var ProductMetadataInterface
     */
    protected $productMetadata;

    /**
     * @var ReadHandler
     */
    protected $galleryReadHandler;

    /**
     * @var ResourceConnection
     */
    protected $resourceConnection;

    /**
     * @var array
     */
    protected $writeArray;


    protected $fieldsToFetch = false;
    protected $fieldsNotFound = [];
    protected $fieldsFound = [];
    protected static $attributeSetCache = [];
    protected static $mediaGalleryBackend = false;





    /**
     * @param StoreManagerInterface $storeManager
     * @param CollectionFactory $collectionFactory
     * @param Data $helper
     * @param Stock $stockHelper
     * @param DirectoryList $directoryList
     * @param ProductRepositoryInterface $productRepo
     * @param Config $taxConfig
     * @param Calculation $taxCalculation
     * @param TimezoneInterface $timezone
     * @param SetFactory $attributeSetFactory
     * @param ProductMetadataInterface $productMetadata
     * @param ReadHandler $galleryReadHandler
     * @param ResourceConnection $resourceConnection
     */
    public function __construct(StoreManagerInterface $storeManager, CollectionFactory $collectionFactory, Data $helper, Stock $stockHelper, DirectoryList $directoryList, ProductRepositoryInterface $productRepo, Config $taxConfig, Calculation $taxCalculation, Product $resourceProduct, Image $imageHelper, Rule $ruleResource, TimezoneInterface $timezone, SetFactory $attributeSetFactory, ProductMetadataInterface $productMetadata, ReadHandler $galleryReadHandler, ResourceConnection $resourceConnection)
    {
        $this->storeManager = $storeManager;
        $this->collectionFactory = $collectionFactory;
        $this->helper = $helper;
        $this->stockHelper = $stockHelper;
        $this->directoryList = $directoryList;
        $this->productRepo = $productRepo;
        $this->taxConfig = $taxConfig;
        $this->taxCalculation = $taxCalculation;
        $this->resourceProduct = $resourceProduct;
        $this->imageHelper = $imageHelper;
        $this->ruleResource = $ruleResource;
        $this->timezone = $timezone;
        $this->attributeSetFactory = $attributeSetFactory;
        $this->productMetadata = $productMetadata;
        $this->galleryReadHandler = $galleryReadHandler;
        $this->resourceConnection = $resourceConnection;
    }

    // /**
    //  * @param \VaxLtd\ProductFeed\Model\Profile $profile
    //  * @return array
    //  */
    // public function generateProductFeed($profile)
    // {

    //     $this->profile = $profile;


    //     $productCollection = $this->collectionFactory->create();
    //     $productCollection->addStoreFilter($profile->getStoreId());

    //     if ($profile->getExportFilterInstockOnly()) {
    //         $this->stockStatus->addStockDataToCollection($productCollection, true);
    //         $productCollection->setFlag('has_stock_status_filter', true);
    //     } else {
    //         $this->stockStatus->addStockDataToCollection($productCollection, false);
    //         $productCollection->setFlag('has_stock_status_filter', true);
    //     }

    //     if ($profile->getExportProductVisibility()) {
    //         $productCollection->addAttributeToFilter('visibility', explode(',', $profile->getExportProductVisibility()));
    //     }

    //     if ($profile->getExportFilterProductType()) {
    //         $productCollection->addAttributeToFilter('type_id', explode(',', $profile->getExportFilterProductType()));
    //     }


    //     $productCollection->addAttributeToFilter('enable_feed', ['eq' => 1]);
    //     $productCollection->addAttributeToFilter('uk_item_condition_ebay_only', ['eq' => 1000]);
    //     $productCollection->addFinalPrice();
    //     $productCollection->addAttributeToSelect('description')
    //         ->addAttributeToSelect('short_description')
    //         ->addAttributeToSelect('name')
    //         ->addAttributeToSelect('google_product_category')
    //         ->addAttributeToSelect('attribute_set_name')
    //         ->addAttributeToSelect('url_key')
    //         ->addAttributeToSelect('image')
    //         ->addAttributeToSelect('quantity')
    //         ->addAttributeToSelect('product_url')
    //         ->addAttributeToSelect('is_in_stock')
    //         ->addAttributeToSelect('uk_barcode')
    //         ->addAttributeToSelect('hero_cp_image')
    //         ->addAttributeToSelect('special_price')
    //         ->addAttributeToSelect('ean_code');

    //     $rows = [["id", "title", "description", "google_product_category", "product_type", "link", "image_link", "condition", "availability", "price", "sale_price", "brand", "mpn", "gtin", "custom_label_0", "is_bundle", "custom_label_1", "promotion_id", "energy_efficiency_class", "additional_image_link"]];

    //     foreach ($productCollection as $product) {
    //         if ($product->getDescription())
    //             $description = strip_tags(htmlspecialchars_decode($product->getDescription()) ?? '');
    //         elseif ($product->getShortDescription())
    //             $description = strip_tags(htmlspecialchars_decode($product->getShortDescription()) ?? '');
    //         else
    //             continue;

    //         $description = preg_replace('/&lt;style((.|\n|\r)*?)&lt;\/style&gt;/', '', $description);

    //         $additional_image = $product->getHeroCpImage() ? $this->storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA) . 'catalog/product' . $product->getHeroCpImage() : '';
    //         $image = $product->getImage() ? $this->storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA) . 'catalog/product' . $product->getImage() : '';

    //         $is_bundle = (stripos($product->getSku(), '1-P-') !== false ? "1" : ($product->getType() == "bundle")) ? 1 : 0;

    //         // stock
    //         $stock_status = $product->getStockStatus() ? "IN STOCK" : "OUT OF STOCK";
    //         $row = [
    //             $product->getSku(),
    //             $product->getName(),
    //             $description,
    //             htmlspecialchars($product->getGoogleProductCategory() ?? ''),
    //             $this->helper->getAttSetGoogleCat($product->getAttributeSetId()),
    //             $product->getProductUrl(),
    //             $image,
    //             'new',
    //             $stock_status,
    //             number_format($product->getPrice(), 2) . " GBP",
    //             $product->getSpecialPrice() ? number_format($product->getSpecialPrice(), 2) . " GBP" : '',
    //             'Vax',
    //             $product->getSku(),
    //             $product->getUkBarcode() ?? $product->getEanCode(),
    //             $this->helper->getAttSetGoogleCat($product->getAttributeSetId()),
    //             $is_bundle,
    //             '',  //custom_label1
    //             '',  //promotion id
    //             '', // energy efficiency

    //             $additional_image
    //         ];
    //         $rows[] = $row;
    //     }
    //     return $rows;
    // }
    /**
     * @param \VaxLtd\ProductFeed\Model\Profile $profile
     * @param int|null $entity_id_from
     * @param int|null $entity_id_to
     * @return array
     */
    public function generateProductFeed($profile, $entity_id_from = null, $entity_id_to = null)
    {
        $this->profile = $profile;
        $previousStoreId = false;
        /**
         * @var \Magento\Catalog\Model\ResourceModel\Product\Collection
         */
        $collection = $this->collectionFactory->create();
        if ($profile->getStoreId()) {
            $profileStoreId = $profile->getStoreId();
            if ($this->storeManager->getStore()->getId() != $profileStoreId) {
                $previousStoreId = $this->storeManager->getStore()->getId();
                $this->storeManager->setCurrentStore($profileStoreId);
            }
            $store = $this->storeManager->getStore($profileStoreId);
            if ($store->getId()) {
                $websiteId = $store->getWebsiteId();
            } else {
                throw new \Exception("Product export failed. The specified store_id {$profileStoreId} does not exist anymore. Please update the profile and select a valid store view.");
            }
            $collection->getSelect()->joinLeft(
                $this->resourceConnection->getTableName('catalog_product_index_price') . ' AS price_index',
                'price_index.entity_id=e.entity_id AND customer_group_id=' . intval($profile->getCustomerGroupId() ? $profile->getCustomerGroupId() : 0) . ' AND price_index.website_id=' . $websiteId,
                [
                    'min_price' => 'min_price',
                    'max_price' => 'max_price',
                    'tier_price' => 'tier_price',
                    'final_price' => 'final_price'
                ]
            );
            $collection->addStoreFilter($profileStoreId);
            $collection->setStore($profileStoreId);
            $collection->addAttributeToSelect("tax_class_id");
        }

        $attributes = ['entity_id', 'sku', 'price', 'name', 'status', 'url_key', 'type_id', 'image'];
        $attributesToSelect = null;
        if ($profile->getAttributesToSelect()) {
            $attributesToSelect = explode(",", $profile->getAttributesToSelect());
        }


        if (!empty($attributesToSelect) && !(isset($attributesToSelect[0]) && empty($attributesToSelect[0]))) {
            // Get all attributes which should be always fetched
            $attributes = array_merge($attributes, $attributesToSelect);
            $attributes = array_unique($attributes);
        }
        $collection->addAttributeToSelect($attributes);

        if ($entity_id_from) {
            $collection->addAttributeToFilter('entity_id', ['gt' => $entity_id_from]);
        }

        if ($entity_id_to) {
            $collection->addAttributeToFilter('entity_id', ['lt' => $entity_id_to]);
        }


        if ($profile->getExportProductVisibility() && $profile->getExportProductVisibility() != '') {
            $collection->addAttributeToFilter('visibility', explode(',', $profile->getExportProductVisibility()));
        }

        if ($profile->getExportFilterProductType() && $profile->getExportFilterProductType() != '') {

            $hiddenProductTypes = explode(",", $profile->getExportFilterProductType());
            if (!empty($hiddenProductTypes)) {
                $collection->addAttributeToFilter('type_id', ['nin' => $hiddenProductTypes]);
            }
        }

        if ($profile->getExportFilterProductStatus() != '') {
            $collection->addAttributeToFilter(
                'status',
                ['in' => explode(",", $profile->getExportFilterProductStatus())]
            );
        }

        if ($profile->getExportFilterInstockOnly() === "1") {
            $this->stockHelper->addInStockFilterToCollection($collection);
        }


        $collectionCount = $collection->getSize();
        $currLine = 1;
        $rows = [];

        foreach ($collection as $product) {
            $rows[] = $this->getExportData($product, $currLine, $collectionCount);
            $currLine++;
        }

        if ($previousStoreId) {
            $this->storeManager->setCurrentStore($this->storeManager->getStore($previousStoreId));
        }

        return $rows;

    }


    /**
     * @param \Magento\Catalog\Model\Product $product
     */
    public function getExportData($product, $lineNumber, $count)
    {
        $returnArray = [];
        $this->writeArray = &$returnArray; // Write directly on product level



        $prodTypeFilter = $this->profile->getExportFilterProductType() ?: "";

        if ($product->getTypeId() && $this->profile && in_array($product->getTypeId(), explode(",", $prodTypeFilter))) {
            return $returnArray; // Product type should be not exported
        }
        // Timestamps of creation/update

        $this->writeValue('created_at_timestamp', $this->helper->convertDateToStoreTimestamp($product->getCreatedAt()));
        $this->writeValue('updated_at_timestamp', $this->helper->convertDateToStoreTimestamp($product->getUpdatedAt()));

        // Which line is this?
        $this->writeValue('line_number', $lineNumber);
        $this->writeValue('count', $count);

        // Export information
        $this->writeValue('export_id', $this->helper->getLastLogId() ? $this->helper->getLastLogId() + 1 : 0);


        $this->exportProductData($product, $returnArray);

        return $returnArray;


    }






    /**
     * @param \Magento\Catalog\Model\Product $product
     */
    public function exportProductData($product, &$returnArray)
    {
        $removePubFolder = false;
        if ($this->profile->getRemovePubFolderFromUrls()) {
            $removePubFolder = true;
        } else if ($this->directoryList->getUrlPath('pub') == '') {
            $removePubFolder = true;
        }

        $exportAllFields = false;
        if ($this->profile->getOutputType() == 'xml') {
            $exportAllFields = true;
        }


        foreach ($product->getData() as $key => $value) {

            if ($key == 'entity_id') {
                $this->writeValue('id', $value);
                continue;
            }

            if ($key == 'sku') {
                $this->writeValue('sku', $value);
                continue;
            }

            if ($key == 'price') {
                $this->writeValue('original_price', number_format($product->getPrice(), 2) . " GBP");
                continue;
            }

            if ($key == 'cost') {
                $this->writeValue('cost', $this->resourceProduct->getAttributeRawValue($product->getId(), 'cost', $this->profile->getStoreId()));
                continue;
            }
            if ($key == 'min_price' || $key == 'max_price' || $key == 'special_price') {
                $value = $this->addTax($product, $value, $key);
            }
            if ($key == 'qty') {
                $value = sprintf('%d', $value);
            }
            if ($key == 'image' || $key == 'small_image' || $key == 'thumbnail') {
                $this->writeValue($key . '_raw', $value);
                $imageUrl = $this->storeManager->getStore($this->profile->getStoreId())
                    ->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA) . 'catalog/product/' . ltrim(
                    $value,
                    '/'
                );
                if ($removePubFolder) {
                    // Remove /pub/ from URL
                    $imageUrl = str_replace('/pub/', '/', $imageUrl);
                }
                $this->writeValue($key, $imageUrl);
                if (!empty($value)) {
                    $cacheUrl = $this->imageHelper->init($product, $key)->setImageFile($value)->getUrl();
                    if ($removePubFolder) {
                        // Remove /pub/ from URL
                        $cacheUrl = str_replace('/pub/', '/', $cacheUrl);
                    }
                    $this->writeValue($key . '_cache_url', $cacheUrl);
                }
                continue;
            }

            $attribute = $product->getResource()->getAttribute($key);
            if ($attribute instanceof \Magento\Catalog\Model\ResourceModel\Eav\Attribute) {
                $attribute->setStoreId($product->getStoreId());
            }
            $attrText = '';
            if ($attribute) {
                if ($attribute->getFrontendInput() === 'media_image') {
                    $this->writeValue($key . '_raw', $value);
                    $imageLink = $value ? $this->storeManager->getStore($this->profile->getStoreId())
                        ->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA) . 'catalog/product/' . ltrim(
                        $value,
                        '/'
                    ) : '';
                    if ($removePubFolder) {
                        // Remove /pub/ from URL
                        $imageLink = str_replace('/pub/', '/', $imageLink);
                    }
                    $this->writeValue($key, $imageLink);
                    continue;
                }
                if ($attribute->getFrontendInput() === 'weee' || $attribute->getFrontendInput() === 'media_gallery') {
                    // Don't export certain frontend_input values
                    continue;
                }
                if ($attribute->usesSource()) {
                    try {
                        $attrText = $product->getAttributeText($key);
                    } catch (\Exception $e) {
                        //echo "Problem with attribute $key: ".$e->getMessage();
                        continue;
                    }
                }
            }
            if (!empty($attrText)) {
                if (is_array($attrText)) {
                    // Multiselect:
                    foreach ($attrText as $index => $val) {
                        if (!is_array($index) && !is_array($val)) {
                            $this->writeValue("{$key}_{$value}_{$index}", $val);
                        }
                    }
                    $this->writeValue($key, implode(",", $attrText));
                } else {
                    if ($attribute->getFrontendInput() == 'multiselect') {
                        $this->writeValue("{$key}_value_0", $attrText);
                    }
                    $this->writeValue($key, $attrText);
                }
            } else {
                $this->writeValue($key, $value);
            }
            if ($key == 'visibility' || $key == 'status' || $key == 'tax_class_id') {
                $this->writeValue("{$key}_raw", $value);
            }
        }

        $googleCatValue = $this->helper->getAttSetGoogleCat($product->getAttributeSetId());
        $this->writeValue('google_product_category', $googleCatValue);
        $this->writeValue('product_type', $product->getTypeId());


        $productUrl = $product->getProductUrl(false);
        if ($this->profile->getExportUrlRemoveStore()) {
            $productUrl = strtok($productUrl, '?');
        }
        $this->writeValue('product_url', $productUrl);

        $price = $product->getPrice();
        $this->writeValue('price', $price);
        $this->writeValue('price_incl_tax', $product->getPriceInclTax());
        $this->writeValue('final_price', $product->getPriceInfo()->getPrice('final_price')->getValue());





        // Unfortunately the code in CatalogRule\Pricing\Price\CatalogPriceRule ignores the current scope :(
        $catalogRulePrice = $this->ruleResource
            ->getRulePrice(
                $this->timezone->scopeDate($this->profile->getStoreId()),
                $this->storeManager->getStore($this->profile->getStoreId())->getWebsiteId(),
                $this->profile->getCustomerGroupId() ?: 0,
                $product->getId()
            );
        $catalogRulePrice = $catalogRulePrice ? floatval($catalogRulePrice) : null;
        $this->writeValue('catalogrule_price', $catalogRulePrice);

        $attributeSetId = $product->getAttributeSetId();
        if (!array_key_exists($attributeSetId, self::$attributeSetCache)) {
            $attributeSet = $this->attributeSetFactory->create()->load($attributeSetId);
            $attributeSetName = '';
            if ($attributeSet->getId()) {
                $attributeSetName = $attributeSet->getAttributeSetName();
                $this->writeValue('attribute_set_name', $attributeSetName);
            }
            self::$attributeSetCache[$attributeSetId] = $attributeSetName;
        } else {
            $this->writeValue('attribute_set_name', self::$attributeSetCache[$attributeSetId]);
        }

        // Upsell product IDs / SKUs
        $this->writeValue('upsell_product_ids', implode(",", $product->getUpSellProductIds()));


        $skus = [];
        foreach ($product->getUpSellProductCollection() as $upsellProduct) {
            $skus[] = $upsellProduct->getSku();
        }
        $this->writeValue('upsell_product_skus', implode(",", $skus));

        // Cross-Sell product IDs / SKUs

        $this->writeValue('cross_sell_product_ids', implode(",", $product->getCrossSellProductIds()));


        $skus = [];
        foreach ($product->getCrossSellProductCollection() as $crosssellProduct) {
            $skus[] = $crosssellProduct->getSku();
        }
        $this->writeValue('cross_sell_product_skus', implode(",", $skus));

        // Related product IDs / SKUs

        $this->writeValue('related_product_ids', implode(",", $product->getRelatedProductIds()));


        $skus = [];
        foreach ($product->getRelatedProductCollection() as $relatedProduct) {
            $skus[] = $relatedProduct->getSku();
        }
        $this->writeValue('related_product_skus', implode(",", $skus));

        $websiteCodes = [];
        foreach ($product->getWebsiteIds() as $websiteId) {
            $websiteCode = $this->storeManager->getWebsite($websiteId)->getCode();
            $websiteCodes[$websiteCode] = $websiteCode;
        }
        $this->writeValue('website_codes', join(',', $websiteCodes));

        // Is special price active?
        $dateToday = $this->timezone->date();
        $dateToday->setTime(0, 0, 0);
        $isSpecialPriceActive = true;
        if ($product->getSpecialFromDate()) {
            $fromDate = $this->timezone->date(new \DateTime($product->getSpecialFromDate()));
            $fromDate->setTime(0, 0, 0);
            if ($dateToday < $fromDate) {
                $isSpecialPriceActive = false;
            }
        } else {
            $isSpecialPriceActive = false;
        }
        if ($product->getSpecialToDate()) {
            $toDate = $this->timezone->date(new \DateTime($product->getSpecialToDate()));
            $toDate->setTime(0, 0, 0);
            if ($dateToday > $toDate) {
                $isSpecialPriceActive = false;
            }
        }
        $this->writeValue('special_price_active', (int) $isSpecialPriceActive);

        $returnArray['images'] = [];
        $originalWriteArray = &$this->writeArray;
        $this->writeArray = &$returnArray['images'];
        if (version_compare($this->productMetadata->getVersion(), '2.1', '<')) {
            if (self::$mediaGalleryBackend === false) {
                $attributes = $product->getTypeInstance()->getSetAttributes($product);
                if (isset($attributes['media_gallery'])) {
                    self::$mediaGalleryBackend = $attributes['media_gallery']->getBackend();
                }
            }
            if (self::$mediaGalleryBackend !== false) {
                self::$mediaGalleryBackend->afterLoad($product);
                $mediaGalleryImages = $product->getMediaGalleryImages();
                if (is_array($mediaGalleryImages)) {
                    foreach ($mediaGalleryImages as $mediaGalleryImage) {
                        $this->writeArray = &$returnArray['images'][];
                        foreach ($mediaGalleryImage->getData() as $key => $value) {
                            $this->writeValue($key, $value);
                        }
                    }
                }
            }
        } else {
            $this->galleryReadHandler->execute($product);
            $mediaGalleryImages = $product->getMediaGalleryImages();
            // ReadHandler only loads disabled=0 images, meaning, hidden images are not exported. To fix this, you must load the full product like this:
            /*
            $product = $product->load($product->getId());
            $directory = $this->objectManager->get('\Magento\Framework\Filesystem')->getDirectoryRead(\Magento\Framework\App\Filesystem\DirectoryList::MEDIA);
            $mediaGalleryImages = $this->objectManager->create('\Magento\Framework\Data\CollectionFactory')->create();
            $mediaConfig = $this->objectManager->get('\Magento\Catalog\Model\Product\Media\Config');
            foreach ($product->getMediaGallery('images') as $image) {
                if (empty($image['value_id']) || $mediaGalleryImages->getItemById($image['value_id']) != null) {
                    continue;
                }
                $image['url'] = $mediaConfig->getMediaUrl($image['file']);
                $image['id'] = $image['value_id'];
                $image['path'] = $directory->getAbsolutePath($mediaConfig->getMediaPath($image['file']));
                $mediaGalleryImages->addItem(new \Magento\Framework\DataObject($image));
            }
            */
            if (!empty($mediaGalleryImages)) {
                foreach ($mediaGalleryImages as $mediaGalleryImage) {
                    $this->writeArray = &$returnArray['images'][];
                    foreach ($mediaGalleryImage->getData() as $key => $value) {
                        if ($key == 'url' && $removePubFolder) {
                            $value = str_replace('/pub/', '/', $value);
                        }
                        $this->writeValue($key, $value);
                    }
                    // Get correct image URL for store
                    /*$storeImageUrl = $this->storeManager->getStore($this->getStoreId())->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA) . 'catalog/product/' . ltrim(str_replace('\\', '/', $mediaGalleryImage['file']), '/');
                    $this->writeValue('url_store', $storeImageUrl);*/
                }
            }
        }
        $this->writeArray = &$originalWriteArray;

        $returnArray['custom_options'] = [];
        $originalWriteArray = &$this->writeArray;
        $this->writeArray = &$returnArray['custom_options'];
        // Unfortunately you can only fetch custom options with the product being loaded. No way to add all the fields on collection load.
        $productCopy = clone $product;
        $productCopy->clearInstance()->setStoreId($this->profile->getStoreId())->load($product->getId());
        // NOTE: If this doesn't work, we should try emulating environment like in the M1 version
        $productOptions = $productCopy->getOptions();
        if (is_array($productOptions)) {
            foreach ($productOptions as $productOption) {
                $customOption = &$returnArray['custom_options'][];
                $this->writeArray = &$customOption;
                foreach ($productOption->getData() as $key => $value) {
                    $this->writeValue($key, $value);
                }
                $optionValues = $productOption->getValues();
                if (is_array($optionValues)) {
                    $this->writeArray = &$customOption['values'];
                    foreach ($optionValues as $optionValue) {
                        $this->writeArray = &$customOption['values'][];
                        foreach ($optionValue->getData() as $key => $value) {
                            $this->writeValue($key, $value);
                        }
                    }
                }
            }
        }
        $this->writeArray = &$originalWriteArray;


        // Group prices
        $returnArray['group_prices'] = [];
        $originalWriteArray = &$this->writeArray;
        $this->writeArray = &$returnArray['group_prices'];
        $attribute = $product->getResource()->getAttribute('group_price');

        if ($attribute) {
            $attribute->getBackend()->afterLoad($product);
            $groupPrices = $product->getData('group_price');
            if (is_array($groupPrices)) {
                foreach ($groupPrices as $groupPrice) {
                    $groupPriceNode = &$returnArray['group_prices'][];
                    $this->writeArray = &$groupPriceNode;
                    foreach ($groupPrice as $key => $value) {
                        $this->writeValue($key, $value);
                    }
                }
            }
        }
        $this->writeArray = &$originalWriteArray;

    }


    public function writeValue($field, $value, $customWriteArray = false)
    {
        if (!is_object($value)) {
            if (($field !== null && !is_array($value) && $value !== null && $value !== '') || !is_array($value)) {
                if ($this->profile->getExportReplaceNlBr() != 0) {
                    if ($this->profile->getExportReplaceNlBr() == 3) {
                        $value = str_replace(["\r\n", "\r", "\n"], "", (string) $value);
                    } else if ($this->profile->getExportReplaceNlBr() == 2) {
                        $value = str_replace(["\r\n", "\r", "\n"], " ", (string) $value);
                    } else if ($this->profile->getExportReplaceNlBr() == 1) {
                        $value = str_replace(["\r\n", "\r", "\n"], "<br />", (string) $value);
                    }
                }
                if ($this->profile && $this->profile->getExportStripTags() && !is_array($value) && !is_object($value)) {
                    $value = strip_tags($value);
                }
                if (!$customWriteArray) {
                    $this->writeArray[$field] = $value;
                } else {
                    $this->writeArray[$customWriteArray][$field] = $value;
                }
            } else if (is_array($value)) {
                foreach ($value as $k => $v) {
                    if ($this->profile && $this->profile->getExportStripTags() && !is_array($v) && !is_object($v)) {
                        $v = strip_tags($v);
                    }
                    if (!is_array($v))
                        $this->writeValue($k, $v, $field);
                }
            }
        }
    }


    /**
     * @param $product \Magento\Catalog\Model\Product
     * @param float $price
     * @param string $key
     *
     * @return int
     */
    protected function addTax($product, $price, $key)
    {
        if ($product->getTaxPercent()) {
            $taxPercent = $product->getTaxPercent();
        } else {
            $taxPercent = false;
            if ($product->getTypeId() == 'grouped') {
                // Get tax_percent from child product
                $childProductIds = $product->getTypeInstance()->getChildrenIds($product->getId());
                if (is_array($childProductIds)) {
                    $childProductIds = array_shift($childProductIds);
                    if (is_array($childProductIds)) {
                        $childProductId = array_shift($childProductIds);
                        try {
                            $childProduct = $this->productRepo->getById($childProductId, false, $this->profile->getStoreId());
                            if ($childProduct->getId()) {
                                $request = $this->taxCalculation->getRateRequest(false, false, false, $product->getStore());
                                $taxPercent = $this->taxCalculation->getRate($request->setProductClassId($childProduct->getTaxClassId()));
                            }
                        } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
                        }
                    }
                }
            }
        }
        if ($taxPercent > 0) {
            if (!$this->taxConfig->priceIncludesTax($this->profile->getStoreId())) {
                // Write price excl. tax
                $this->writeValue("{$key}_excl_tax", $price);
                // Prices are excluding tax -> add tax
                $price *= 1 + $taxPercent / 100;
            } else {
                // Prices are including tax - do not add tax to price
                // Write price excl. tax
                $this->writeValue("{$key}_excl_tax", $price / (1 + $taxPercent / 100));
            }
        } else {
            $this->writeValue("{$key}_excl_tax", $price);
        }
        return $price;
    }










}