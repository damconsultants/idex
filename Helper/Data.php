<?php

namespace DamConsultants\Idex\Helper;

use \Magento\Framework\App\Helper\AbstractHelper;
use Magento\Store\Model\ScopeInterface;
use DamConsultants\Idex\Model\ResourceModel\Collection\BrandOptionCollectionFactory;
use DamConsultants\Idex\Model\ResourceModel\Collection\CustomerVisibilityOptionCollectionFactory;
use DamConsultants\Idex\Model\ResourceModel\Collection\FileCategoryOptionCollectionFactory;

class Data extends AbstractHelper
{
    /**
     * @var $storeScope
     */
    protected $storeScope;

    /**
     * @var \Magento\Framework\Filesystem
     */
    protected $filesystem;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $_scopeConfig;

    /**
     * @var $by_redirecturl
     */
    public $by_redirecturl;

    /**
     * @var $bynderDomain
     */
    public $bynderDomain = "";

    /**
     * @var $permanent_token
     */
    public $permanent_token = "";
    /**
     * @var $permanent_token
     */
    protected $cookieMetadataFactory;
    /**
     * @var $permanent_token
     */
    protected $cookieManager;
    /**
     * @var $permanent_token
     */
    protected $productrepository;
    /**
     * @var $permanent_token
     */
    protected $_storeManager;
    /**
     * @var $permanent_token
     */
    protected $_curl;
    /**
     * @var $permanent_token
     */
    protected $_bulk;
	/**
     * @var $brandOptionCollectionFactory
     */
    protected $brandOptionCollectionFactory;
	/**
     * @var $customerVisibilityOptionCollectionFactory
     */
    protected $customerVisibilityOptionCollectionFactory;
	/**
     * @var $fileCategoryOptionCollectionFactory
     */
    protected $fileCategoryOptionCollectionFactory;
    /**
     * @var $permanent_token
     */
    protected $_registry;

    public const BYNDER_DOMAIN = 'bynderconfig/bynder_credential/bynderdomain';
    public const PERMANENT_TOKEN = 'bynderconfig/bynder_credential/permanent_token';
    public const LICENCE_TOKEN = 'bynderconfig/bynder_credential/licenses_key';
    public const RADIO_BUTTON = 'byndeimageconfig/bynder_image/selectimage';
    public const PRODUCT_SKU_LIMIT = 'cronimageconfig/set_limit_product_sku/product_sku_limt';
    public const FETCH_CRON = 'cronimageconfig/configurable_cron/fetch_enable';
    public const AUTO_CRON = 'cronimageconfig/auto_add_bynder/auto_enable';
    public const API_CALLED = 'https://developer.thedamconsultants.com/';
    public const DELETE_CRON = 'cronimageconfig/delete_cron_bynder/delete_enable';

    /**
     * Data Helper
     * @param \Magento\Framework\Stdlib\Cookie\CookieMetadataFactory $cookieMetadataFactory
     * @param \Magento\Framework\Stdlib\CookieManagerInterface $cookieManager
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\Catalog\Api\ProductRepositoryInterface $productrepository
     * @param \Magento\Framework\Filesystem $filesystem
     * @param \Magento\Framework\HTTP\Client\Curl $curl
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\ConfigurableProduct\Block\Adminhtml\Product\Steps\Bulk $bulk
     */
    public function __construct(
        \Magento\Framework\Stdlib\Cookie\CookieMetadataFactory $cookieMetadataFactory,
        \Magento\Framework\Stdlib\CookieManagerInterface $cookieManager,
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Catalog\Api\ProductRepositoryInterface $productrepository,
        \Magento\Framework\Filesystem $filesystem,
        \Magento\Framework\HTTP\Client\Curl $curl,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Registry $registry,
        \Magento\ConfigurableProduct\Block\Adminhtml\Product\Steps\Bulk $bulk,
		BrandOptionCollectionFactory $brandOptionCollectionFactory,
		CustomerVisibilityOptionCollectionFactory $customerVisibilityOptionCollectionFactory,
		FileCategoryOptionCollectionFactory $fileCategoryOptionCollectionFactory
    ) {
        $this->cookieMetadataFactory = $cookieMetadataFactory;
        $this->cookieManager = $cookieManager;
        $this->productrepository = $productrepository;
        $this->filesystem = $filesystem;
        $this->_scopeConfig = $context->getScopeConfig();
        $this->_storeManager = $storeManager;
        $this->_curl = $curl;
        $this->_bulk = $bulk;
        $this->_registry = $registry;
		$this->brandOptionCollectionFactory = $brandOptionCollectionFactory;
		$this->customerVisibilityOptionCollectionFactory = $customerVisibilityOptionCollectionFactory;
		$this->fileCategoryOptionCollectionFactory = $fileCategoryOptionCollectionFactory;
        parent::__construct($context);
    }
    /**
     * Get Bulk Image Roll
     *
     * @return $this
     */
    public function getBulkImageRoll()
    {
        return $this->_bulk->getMediaAttributes();
    }
    /**
     * Get Image Roll
     *
     * @return $this
     * @param string $currentProduct
     */
    public function getProduct($currentProduct)
    {
        return $this->_registry->registry($currentProduct);
    }
    /**
     * Get Product Id
     *
     * @return $this
     * @param string $productId
     */
    public function getProductById($productId)
    {

        return $this->productrepository->getById($productId);
    }
    /**
     * Get Fetch cron enable
     *
     * @return $this
     */
    public function getFetchCronEnable()
    {
        return $this->getConfig(self::FETCH_CRON);
    }
    /**
     * Get Permanent Token
     *
     * @param string $path
     * @return $this
     */
    public function getDeleteCron($path)
    {
        return (string) $this->getStoreConfig($path);
    }
    /**
     * Get Auto cron enable
     *
     * @return $this
     */
    public function getDeleteCronEnable()
    {
        return $this->getConfig(self::DELETE_CRON);
    }
    /**
     * Get Auto cron enable
     *
     * @return $this
     */
    public function getAutoCronEnable()
    {
        return $this->getConfig(self::AUTO_CRON);
    }
    /**
     * Get Store Config
     *
     * @return $this
     * @param string $storePath
     * @param string $storeId
     */
    public function getStoreConfig($storePath, $storeId = null)
    {
        return $this->_scopeConfig->getValue($storePath, ScopeInterface::SCOPE_STORE, $storeId);
    }
    /**
     * Get Bynder Domain
     *
     * @return $this
     */
    public function getBynderDomain()
    {
        return (string) $this->getStoreConfig(self::BYNDER_DOMAIN);
    }
    /**
     * Get Permanent Token
     *
     * @return $this
     */
    public function getPermanentToken()
    {
        return (string) $this->getStoreConfig(self::PERMANENT_TOKEN);
    }
    /**
     * Get Licence Token
     *
     * @return $this
     */
    public function getLicenceToken()
    {
        return (string) $this->getStoreConfig(self::LICENCE_TOKEN);
    }
    /**
     * Bynde Image Config
     *
     * @return $this
     */
    public function byndeimageconfig()
    {
        return (string) $this->getStoreConfig(self::RADIO_BUTTON);
    }
    /**
     * Get Product Sku Limit Config
     *
     * @return $this
     */
    public function getProductSkuLimitConfig()
    {
        return (string) $this->getStoreConfig(self::PRODUCT_SKU_LIMIT);
    }
    /**
     * Get Bynder Dom
     *
     * @return $this
     */
    public function getBynderDom()
    {
        return (string) $this->getConfig(self::BYNDER_DOMAIN);
    }
    /**
     * Get Permanen Token
     *
     * @return $this
     */
    public function getPermanenToken()
    {
        return (string) $this->getConfig(self::PERMANENT_TOKEN);
    }
    /**
     * Get Load Credential
     *
     * @return $this
     */
    public function getLoadCredential()
    {

        $this->bynderDomain = $this->getBynderDom();
        $this->permanent_token = $this->getPermanenToken();
        $this->by_redirecturl = $this->getRedirecturl();
        if (!empty($this->bynderDomain) && !empty($this->permanent_token) && !empty($this->by_redirecturl)) {
            return 1;
        } else {
            return "Bynder authentication failed | Please check your credential";
        }
    }
    /**
     * Get Redirecturl
     *
     * @return $this
     */
    public function getRedirecturl()
    {
        return (string) $this->getbaseurl() . "bynder/redirecturl";
    }

    /**
     * Get baseurl
     *
     * @return $this
     */
    public function getbaseurl()
    {
        $url = $this->_storeManager->getStore()->getBaseUrl();
        return $url;
    }
    /**
     * Get Config
     *
     * @return $this
     * @param string $path
     */
    public function getConfig($path)
    {
        return $this->_scopeConfig->getValue($path, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }
    /**
     * Get CheckBynder
     *
     * @return $this
     */
    public function getCheckBynder()
    {
        $fields = [
            'base_url' => $this->getbaseurl(),
            'licence_token' => $this->getLicenceToken()
        ];
        $jsonData = '{}';
        $fields = json_encode($fields);

        $this->_curl->setOption(CURLOPT_URL, self::API_CALLED . 'idex-check-bynder-license');
        $this->_curl->setOption(CURLOPT_RETURNTRANSFER, true);
        $this->_curl->setOption(CURLOPT_TIMEOUT, 0);
        $this->_curl->setOption(CURLOPT_ENCODING, '');
        $this->_curl->setOption(CURLOPT_MAXREDIRS, 10);
        $this->_curl->setOption(CURLOPT_FOLLOWLOCATION, true);
        $this->_curl->setOption(CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
        $this->_curl->setOption(CURLOPT_POSTFIELDS, $fields);

        $this->_curl->addHeader("Content-Type", "application/json");

        $this->_curl->post(self::API_CALLED . 'idex-check-bynder-license', $jsonData);

        $response = $this->_curl->getBody();
        return $response;
    }
    /**
     * Get DerivativesImage
     *
     * @return $this
     * @param array $bynder_auth
     */
    public function getDerivativesImage($bynder_auth)
    {

        $fields = [
            'bynder_domain' => $bynder_auth['bynderDomain'],
            'redirectUri' => $bynder_auth['redirectUri'],
            'permanent_token' => $bynder_auth['token'],
            'databaseId' => $bynder_auth['og_media_ids'],
            'daatasetType' => $bynder_auth['dataset_types'],
            'base_url' => $this->getbaseurl(),
            'licence_token' => $this->getLicenceToken(),
            'bynder_metaproperty_collection' => $bynder_auth['collection_data_value']
        ];
        $jsonData = '{}';
        $fields = json_encode($fields);

        $this->_curl->setOption(CURLOPT_URL, self::API_CALLED . 'idex-magento-derivatives');
        $this->_curl->setOption(CURLOPT_RETURNTRANSFER, true);
        $this->_curl->setOption(CURLOPT_TIMEOUT, 0);
        $this->_curl->setOption(CURLOPT_ENCODING, '');
        $this->_curl->setOption(CURLOPT_MAXREDIRS, 10);
        $this->_curl->setOption(CURLOPT_FOLLOWLOCATION, true);
        $this->_curl->setOption(CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
        $this->_curl->setOption(CURLOPT_POSTFIELDS, $fields);

        $this->_curl->addHeader("Content-Type", "application/json");

        $this->_curl->post(self::API_CALLED . 'idex-magento-derivatives', $jsonData);

        $response = $this->_curl->getBody();

        return $response;
    }
    /**
     * Get LicenceKey
     *
     * @return $this
     */
    public function getLicenceKey()
    {
        $fields = [
            'domain_name' => $this->getbaseurl()
        ];
        $jsonData = '{}';
        $fields = json_encode($fields);

        $this->_curl->setOption(CURLOPT_URL, self::API_CALLED . 'idex-get-license-key');
        $this->_curl->setOption(CURLOPT_RETURNTRANSFER, true);
        $this->_curl->setOption(CURLOPT_TIMEOUT, 0);
        $this->_curl->setOption(CURLOPT_ENCODING, '');
        $this->_curl->setOption(CURLOPT_MAXREDIRS, 10);
        $this->_curl->setOption(CURLOPT_FOLLOWLOCATION, true);
        $this->_curl->setOption(CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
        $this->_curl->setOption(CURLOPT_POSTFIELDS, $fields);

        $this->_curl->addHeader("Content-Type", "application/json");

        $this->_curl->post(self::API_CALLED . 'idex-get-license-key', $jsonData);

        $response = $this->_curl->getBody();
        return $response;
    }
    /**
     * Get BynderChangemetadataAssets
     *
     * @return $this
     * @param string $product_url
     * @param string $url_data
     */
    public function getBynderChangemetadataAssets($product_url, $url_data)
    {

        $fields = [
            'domain_name' => $this->getbaseurl(),
            'bynder_domain' => $this->getBynderDom(),
            'permanent_token' => $this->getPermanenToken(),
            'licence_token' => $this->getLicenceToken(),
            'product_url' => $product_url,
            'bynder_multi_img' => $url_data
        ];
        $jsonData = '{}';
        $fields = json_encode($fields);

        $this->_curl->setOption(CURLOPT_URL, self::API_CALLED . 'idex-change-metadata-magento');
        $this->_curl->setOption(CURLOPT_RETURNTRANSFER, true);
        $this->_curl->setOption(CURLOPT_TIMEOUT, 0);
        $this->_curl->setOption(CURLOPT_ENCODING, '');
        $this->_curl->setOption(CURLOPT_MAXREDIRS, 10);
        $this->_curl->setOption(CURLOPT_FOLLOWLOCATION, true);
        $this->_curl->setOption(CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
        $this->_curl->setOption(CURLOPT_POSTFIELDS, $fields);

        $this->_curl->addHeader("Content-Type", "application/json");

        $this->_curl->post(self::API_CALLED . 'idex-change-metadata-magento', $jsonData);

        $response = $this->_curl->getBody();
        return $response;
    }
    /**
     * Get BynderChangemetadataAssetsDoc
     *
     * @return $this
     * @param string $product_url
     * @param string $url_data
     */
    public function getBynderChangemetadataAssetsDoc($product_url, $url_data)
    {

        $fields = [
            'domain_name' => $this->getbaseurl(),
            'bynder_domain' => $this->getBynderDom(),
            'permanent_token' => $this->getPermanenToken(),
            'licence_token' => $this->getLicenceToken(),
            'product_url' => $product_url,
            'bynder_multi_img' => $url_data
        ];
        $jsonData = '{}';
        $fields = json_encode($fields);

        $this->_curl->setOption(CURLOPT_URL, self::API_CALLED . 'idex-change-metadata-magento-doc');
        $this->_curl->setOption(CURLOPT_RETURNTRANSFER, true);
        $this->_curl->setOption(CURLOPT_TIMEOUT, 0);
        $this->_curl->setOption(CURLOPT_ENCODING, '');
        $this->_curl->setOption(CURLOPT_MAXREDIRS, 10);
        $this->_curl->setOption(CURLOPT_FOLLOWLOCATION, true);
        $this->_curl->setOption(CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
        $this->_curl->setOption(CURLOPT_POSTFIELDS, $fields);

        $this->_curl->addHeader("Content-Type", "application/json");

        $this->_curl->post(self::API_CALLED . 'idex-change-metadata-magento-doc', $jsonData);

        $response = $this->_curl->getBody();
        return $response;
    }
    /**
     * Get BynderChangemetadataAssetsVideo
     *
     * @return $this
     * @param string $product_url
     * @param string $url_data
     */
    public function getBynderChangemetadataAssetsVideo($product_url, $url_data)
    {

        $fields = [
            'domain_name' => $this->getbaseurl(),
            'bynder_domain' => $this->getBynderDom(),
            'permanent_token' => $this->getPermanenToken(),
            'licence_token' => $this->getLicenceToken(),
            'product_url' => $product_url,
            'bynder_multi_img' => $url_data
        ];
        $jsonData = '{}';
        $fields = json_encode($fields);

        $this->_curl->setOption(CURLOPT_URL, self::API_CALLED . 'idex-change-metadata-magento-video');
        $this->_curl->setOption(CURLOPT_RETURNTRANSFER, true);
        $this->_curl->setOption(CURLOPT_TIMEOUT, 0);
        $this->_curl->setOption(CURLOPT_ENCODING, '');
        $this->_curl->setOption(CURLOPT_MAXREDIRS, 10);
        $this->_curl->setOption(CURLOPT_FOLLOWLOCATION, true);
        $this->_curl->setOption(CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
        $this->_curl->setOption(CURLOPT_POSTFIELDS, $fields);

        $this->_curl->addHeader("Content-Type", "application/json");

        $this->_curl->post(self::API_CALLED . 'idex-change-metadata-magento-video', $jsonData);

        $response = $this->_curl->getBody();
        return $response;
    }
    /**
     * Get BynderDataCmsPage
     *
     * @return $this
     * @param string $CMSPageURL
     * @param string $url_data
     */
    public function getBynderDataCmsPage($CMSPageURL, $url_data)
    {

        $fields = [
            'domain_name' => $this->getbaseurl(),
            'bynder_domain' => $this->getBynderDom(),
            'permanent_token' => $this->getPermanenToken(),
            'licence_token' => $this->getLicenceToken(),
            'cmspage_url' => $CMSPageURL,
            'bynder_multi_img' => $url_data
        ];
        $jsonData = '{}';
        $fields = json_encode($fields);

        $this->_curl->setOption(CURLOPT_URL, self::API_CALLED . 'idex-change-metadata-magento-cms-page');
        $this->_curl->setOption(CURLOPT_RETURNTRANSFER, true);
        $this->_curl->setOption(CURLOPT_TIMEOUT, 0);
        $this->_curl->setOption(CURLOPT_ENCODING, '');
        $this->_curl->setOption(CURLOPT_MAXREDIRS, 10);
        $this->_curl->setOption(CURLOPT_FOLLOWLOCATION, true);
        $this->_curl->setOption(CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
        $this->_curl->setOption(CURLOPT_POSTFIELDS, $fields);

        $this->_curl->addHeader("Content-Type", "application/json");

        $this->_curl->post(self::API_CALLED . 'idex-change-metadata-magento-cms-page', $jsonData);

        $response = $this->_curl->getBody();
        return $response;
    }
    /**
     * Get BynderMetaProperites
     *
     * @return $this
     */
    public function getBynderMetaProperites()
    {
        $fields = [
            'domain_name' => $this->getbaseurl(),
            'bynder_domain' => $this->getBynderDom(),
            'permanent_token' => $this->getPermanenToken(),
            'licence_token' => $this->getLicenceToken()
        ];
        $jsonData = '{}';
        $fields = json_encode($fields);
        $this->_curl->setOption(CURLOPT_URL, self::API_CALLED . 'idex-get-bynder-meta-properites');
        $this->_curl->setOption(CURLOPT_RETURNTRANSFER, true);
        $this->_curl->setOption(CURLOPT_TIMEOUT, 0);
        $this->_curl->setOption(CURLOPT_ENCODING, '');
        $this->_curl->setOption(CURLOPT_MAXREDIRS, 10);
        $this->_curl->setOption(CURLOPT_FOLLOWLOCATION, true);
        $this->_curl->setOption(CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
        $this->_curl->setOption(CURLOPT_POSTFIELDS, $fields);

        $this->_curl->addHeader("Content-Type", "application/json");

        $this->_curl->post(self::API_CALLED . 'idex-get-bynder-meta-properites', $jsonData);

        $response = $this->_curl->getBody();
        return $response;
    }
    /**
     * Get ImageSyncWithProperties
     *
     * @return $this
     * @param string $sku_id
     * @param string $property_id
     * @param string $collection_data_value
     */
    public function getImageSyncWithProperties($sku_id, $property_id, $collection_data_value)
    {
        $fields = [
            'domain_name' => $this->getbaseurl(),
            'bynder_domain' => $this->getBynderDom(),
            'permanent_token' => $this->getPermanenToken(),
            'licence_token' => $this->getLicenceToken(),
            'sku_id' => $sku_id,
            'property_id' => $property_id,
            'bynder_metaproperty_collection' => $collection_data_value
        ];

        $jsonData = '{}';
        $fields = json_encode($fields);
        $this->_curl->setOption(CURLOPT_URL, self::API_CALLED . 'idex-bynder-skudetails-new');
        $this->_curl->setOption(CURLOPT_RETURNTRANSFER, true);
        $this->_curl->setOption(CURLOPT_TIMEOUT, 0);
        $this->_curl->setOption(CURLOPT_ENCODING, '');
        $this->_curl->setOption(CURLOPT_MAXREDIRS, 10);
        $this->_curl->setOption(CURLOPT_FOLLOWLOCATION, true);
        $this->_curl->setOption(CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
        $this->_curl->setOption(CURLOPT_POSTFIELDS, $fields);

        $this->_curl->addHeader("Content-Type", "application/json");

        $this->_curl->post(self::API_CALLED . 'idex-bynder-skudetails-new', $jsonData);

        $response = $this->_curl->getBody();
        return $response;
    }
    /**
     * Get DataRemoveForMagento
     *
     * @return $this
     * @param string $sku_id
     * @param string $media_Id
     * @param string $metaProperty_id
     */
    public function getDataRemoveForMagento($sku_id, $media_Id, $metaProperty_id)
    {
        $fields = [
            'domain_name' => $this->getbaseurl(),
            'bynder_domain' => $this->getBynderDom(),
            'permanent_token' => $this->getPermanenToken(),
            'licence_token' => $this->getLicenceToken(),
            'sku_id' => $sku_id,
            'media_id' => $media_Id,
            'property_id' => $metaProperty_id
        ];
        $jsonData = '{}';
        $fields = json_encode($fields);

        $this->_curl->setOption(CURLOPT_URL, self::API_CALLED . 'idex-sku-data-remove-for-magento');
        $this->_curl->setOption(CURLOPT_RETURNTRANSFER, true);
        $this->_curl->setOption(CURLOPT_TIMEOUT, 0);
        $this->_curl->setOption(CURLOPT_ENCODING, '');
        $this->_curl->setOption(CURLOPT_MAXREDIRS, 10);
        $this->_curl->setOption(CURLOPT_FOLLOWLOCATION, true);
        $this->_curl->setOption(CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
        $this->_curl->setOption(CURLOPT_POSTFIELDS, $fields);

        $this->_curl->addHeader("Content-Type", "application/json");

        $this->_curl->post(self::API_CALLED . 'idex-sku-data-remove-for-magento', $jsonData);

        $response = $this->_curl->getBody();
        return $response;
    }
    /**
     * Get DataRemoveForMagento
     *
     * @return $this
     * @param string $sku_id
     * @param string $media_Id
     * @param string $metaProperty_id
     */
    public function getAddedCompactviewSkuFromBynder($sku_id, $media_Id, $metaProperty_id)
    {
        $fields = [
            'domain_name' => $this->getbaseurl(),
            'bynder_domain' => $this->getBynderDom(),
            'permanent_token' => $this->getPermanenToken(),
            'licence_token' => $this->getLicenceToken(),
            'sku_id' => $sku_id,
            'media_id' => $media_Id,
            'property_id' => $metaProperty_id
        ];
        $jsonData = '{}';
        $fields = json_encode($fields);

        $this->_curl->setOption(CURLOPT_URL, self::API_CALLED . 'idex-added-compactview-sku-from-bynder');
        $this->_curl->setOption(CURLOPT_RETURNTRANSFER, true);
        $this->_curl->setOption(CURLOPT_TIMEOUT, 0);
        $this->_curl->setOption(CURLOPT_ENCODING, '');
        $this->_curl->setOption(CURLOPT_MAXREDIRS, 10);
        $this->_curl->setOption(CURLOPT_FOLLOWLOCATION, true);
        $this->_curl->setOption(CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
        $this->_curl->setOption(CURLOPT_POSTFIELDS, $fields);

        $this->_curl->addHeader("Content-Type", "application/json");

        $this->_curl->post(self::API_CALLED . 'idex-added-compactview-sku-from-bynder', $jsonData);

        $response = $this->_curl->getBody();
        return $response;
    }

    /**
     * Get DataRemoveForMagento
     *
     * @return $this
     * @param string $product_sku_key
     * @param string $metaProperty_Collections
     * @param string $image
     */
    public function getUpdateBynderImageRoleAndAltText($product_sku_key, $metaProperty_Collections, $image)
    {
        $fields = [
            'domain_name' => $this->getbaseurl(),
            'bynder_domain' => $this->getBynderDom(),
            'permanent_token' => $this->getPermanenToken(),
            'licence_token' => $this->getLicenceToken(),
            'sku_id' => $product_sku_key,
            'metaProperty_Collections' => $metaProperty_Collections,
            'bynder_changes_details' => $image
        ];
        $jsonData = '{}';
        $fields = json_encode($fields);

        $this->_curl->setOption(CURLOPT_URL, self::API_CALLED . 'idex-update-bynderImageRole-and-altText');
        $this->_curl->setOption(CURLOPT_RETURNTRANSFER, true);
        $this->_curl->setOption(CURLOPT_TIMEOUT, 0);
        $this->_curl->setOption(CURLOPT_ENCODING, '');
        $this->_curl->setOption(CURLOPT_MAXREDIRS, 10);
        $this->_curl->setOption(CURLOPT_FOLLOWLOCATION, true);
        $this->_curl->setOption(CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
        $this->_curl->setOption(CURLOPT_POSTFIELDS, $fields);

        $this->_curl->addHeader("Content-Type", "application/json");

        $this->_curl->post(self::API_CALLED . 'idex-update-bynderImageRole-and-altText', $jsonData);

        $response = $this->_curl->getBody();
        return $response;
    }
    /**
     * Change Bynder Assets Details
     *
     * @param array $bynder_auth
     * @return $this
     */
    public function changeBynderAssetsDetails($bynder_auth)
    {
        $fields = [
            'domain_name' => $this->getbaseurl(),
            'bynder_domain' => $bynder_auth['bynderDomain'],
            'permanent_token' => $bynder_auth['token'],
            'new_value_obj' => $bynder_auth['new_value_obj'],
            'base_url' => $this->getbaseurl(),
            'licence_token' => $this->getLicenceToken(),
            'bynder_metaproperty_collection' => $bynder_auth['collection_data_value']
        ];
        $jsonData = '{}';
        $fields = json_encode($fields);
        $this->_curl->setOption(CURLOPT_URL, self::API_CALLED . 'idex-sync-assets-details');
        $this->_curl->setOption(CURLOPT_RETURNTRANSFER, true);
        $this->_curl->setOption(CURLOPT_TIMEOUT, 0);
        $this->_curl->setOption(CURLOPT_ENCODING, '');
        $this->_curl->setOption(CURLOPT_MAXREDIRS, 10);
        $this->_curl->setOption(CURLOPT_FOLLOWLOCATION, true);
        $this->_curl->setOption(CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
        $this->_curl->setOption(CURLOPT_POSTFIELDS, $fields);

        $this->_curl->addHeader("Content-Type", "application/json");

        $this->_curl->post(self::API_CALLED . 'idex-sync-assets-details', $jsonData);

        $response = $this->_curl->getBody();
        return $response;
    }
    /**
     * Change Popup Bynder Assets Details
     *
     * @param array $bynder_auth
     * @return $this
     */
    public function changePopupBynderAssetsDetails($bynder_auth)
    {
        $getBaseUrl = $this->getbaseurl();
        $fields = [
            'domain_name' => $getBaseUrl,
            'bynder_domain' => $bynder_auth['bynderDomain'],
            'permanent_token' => $bynder_auth['token'],
            'new_value_obj' => $bynder_auth['new_value_obj'],
            'base_url' => $getBaseUrl,
            'licence_token' => $this->getLicenceToken(),
            'bynder_metaproperty_collection' => $bynder_auth['collection_data_value']
        ];
        $jsonData = '{}';
        $fields = json_encode($fields);
        $this->_curl->setOption(CURLOPT_URL, self::API_CALLED . 'sync-idex-popup-assets-details');
        $this->_curl->setOption(CURLOPT_RETURNTRANSFER, true);
        $this->_curl->setOption(CURLOPT_TIMEOUT, 0);
        $this->_curl->setOption(CURLOPT_ENCODING, '');
        $this->_curl->setOption(CURLOPT_MAXREDIRS, 10);
        $this->_curl->setOption(CURLOPT_FOLLOWLOCATION, true);
        $this->_curl->setOption(CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
        $this->_curl->setOption(CURLOPT_POSTFIELDS, $fields);

        $this->_curl->addHeader("Content-Type", "application/json");

        $this->_curl->post(self::API_CALLED . 'sync-idex-popup-assets-details', $jsonData);

        $response = $this->_curl->getBody();
       
        return $response;
    }
    /**
     * Remove Role DAM
     *
     * @param array $bynder_auth
     * @return $this
     */
    public function removeSkuOrRoleDAM($bynder_auth)
    {
        $getBaseUrl = $this->getbaseurl();
        $fields = [
            'domain_name' => $getBaseUrl,
            'bynder_domain' => $bynder_auth['bynderDomain'],
            'permanent_token' => $bynder_auth['token'],
            'new_value_obj' => $bynder_auth['changes_details'],
            'base_url' => $getBaseUrl,
            'licence_token' => $this->getLicenceToken(),
            'bynder_metaproperty_collection' => $bynder_auth['collection_data_value']
        ];
        $jsonData = '{}';
        $fields = json_encode($fields);
        $this->_curl->setOption(CURLOPT_URL, self::API_CALLED . 'idex-remove-sku-role-from-dam');
        $this->_curl->setOption(CURLOPT_RETURNTRANSFER, true);
        $this->_curl->setOption(CURLOPT_TIMEOUT, 0);
        $this->_curl->setOption(CURLOPT_ENCODING, '');
        $this->_curl->setOption(CURLOPT_MAXREDIRS, 10);
        $this->_curl->setOption(CURLOPT_FOLLOWLOCATION, true);
        $this->_curl->setOption(CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
        $this->_curl->setOption(CURLOPT_POSTFIELDS, $fields);

        $this->_curl->addHeader("Content-Type", "application/json");

        $this->_curl->post(self::API_CALLED . 'idex-remove-sku-role-from-dam', $jsonData);

        $response = $this->_curl->getBody();
        return $response;
    }
    /**
     * CheckBynderSideDeleteData
     *
     * @param array $bynder_auth
     */
    public function getCheckBynderSideDeleteData($bynder_auth)
    {
        $getBaseUrl = $this->getbaseurl();
        $fields = [
            'domain_name' => $this->getbaseurl(),
            'bynder_domain' => $this->getBynderDom(),
            'permanent_token' => $this->getPermanenToken(),
            'licence_token' => $this->getLicenceToken(),
            'base_url' => $getBaseUrl,
            'last_cron_time' => $bynder_auth['last_cron_time']
        ];
        $jsonData = '{}';
        $fields = json_encode($fields);
        $this->_curl->setOption(CURLOPT_URL, self::API_CALLED . 'idex-remove-assets-deleted-data-from-dam');
        $this->_curl->setOption(CURLOPT_RETURNTRANSFER, true);
        $this->_curl->setOption(CURLOPT_TIMEOUT, 0);
        $this->_curl->setOption(CURLOPT_ENCODING, '');
        $this->_curl->setOption(CURLOPT_MAXREDIRS, 10);
        $this->_curl->setOption(CURLOPT_FOLLOWLOCATION, true);
        $this->_curl->setOption(CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
        $this->_curl->setOption(CURLOPT_POSTFIELDS, $fields);

        $this->_curl->addHeader("Content-Type", "application/json");

        $this->_curl->post(self::API_CALLED . 'idex-remove-assets-deleted-data-from-dam', $jsonData);

        $response = $this->_curl->getBody();
        return $response;
        /*$response = '{"status":1,"data":[{"id":"48DADC72-8775-4CCC-81764EC55395E178"},{"id":"D49C3C3C-8091-4CA0-8D27C36BA14B15D7"}]}';
        return $response;*/
    }

    /**
     * Get searchFromBynder
     * not in use at the moments
     * @return $this
     * @param string $keyword
     * @param string $extra_details
     */
    public function searchFromBynder($keyword, $extra_details)
    {
        $collection_data_value = $extra_details["collection_data_value"];
        //$property_id = $extra_details["property_id"]; 
        $permanent_token = $this->getPermanenToken();
        $bynder_domain = $this->getBynderDom();
        $fields = [
            'search_term' => $keyword,
            'bynder_metaproperty_collection' => $collection_data_value
        ];
        $remove = array("http://","https://","/");
        $bynder_domain = str_replace($remove,"",$bynder_domain);

        $send_fields = [
            'Year' => "2024",
            'bynder_metaproperty_collection' => $collection_data_value
        ];
		
		
		$queryString = http_build_query($send_fields);
		$url = 'https://' . $bynder_domain . '/api/v4/media/?' . $queryString;

		// Get Magento's Curl client
		$this->_curl->setOption(CURLOPT_URL, $url);
		$this->_curl->setOption(CURLOPT_RETURNTRANSFER, true);
		$this->_curl->setOption(CURLOPT_TIMEOUT, 0);
		$this->_curl->setOption(CURLOPT_ENCODING, '');
		$this->_curl->setOption(CURLOPT_MAXREDIRS, 10);
		$this->_curl->setOption(CURLOPT_FOLLOWLOCATION, true);
		$this->_curl->setOption(CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);

		// Set the Authorization header
		$this->_curl->addHeader("Authorization", "Bearer " . $permanent_token);

		// Execute the GET request
		$this->_curl->get($url);

		// Retrieve the response
		$response = $this->_curl->getBody();
		//echo "<pre>"; print_r(json_decode($response)); exit;
		return $response;
		
    }

    /**
     * Get searchPriceListFromBynder
     *
     * @return $this
     * @param string $keyword
     * @param string $extra_details
     */
    public function searchPriceListFromBynder($search_details,$search_values, $extra_details)
    {
        $collection_data_value = $extra_details["collection_data_value"];
        // search details and collection_data_value will be use for log
        $permanent_token = $this->getPermanenToken();
        $bynder_domain = $this->getBynderDom();
        $remove = array("http://","https://","/");
        $bynder_domain = str_replace($remove,"",$bynder_domain);

        $query_String = http_build_query($search_values);
       
		$url = 'https://' . $bynder_domain . '/api/v4/media/?' . $query_String;

		// Get Magento's Curl client
		$this->_curl->setOption(CURLOPT_URL, $url);
		$this->_curl->setOption(CURLOPT_RETURNTRANSFER, true);
		$this->_curl->setOption(CURLOPT_TIMEOUT, 0);
		$this->_curl->setOption(CURLOPT_ENCODING, '');
		$this->_curl->setOption(CURLOPT_MAXREDIRS, 10);
		$this->_curl->setOption(CURLOPT_FOLLOWLOCATION, true);
		$this->_curl->setOption(CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);

        $this->_curl->addHeader("Content-Type", "application/json");
		// Set the Authorization header
		$this->_curl->addHeader("Authorization", "Bearer " . $permanent_token);

		// Execute the GET request
		$this->_curl->get($url);

		// Retrieve the response
		$response = $this->_curl->getBody();

		return $response;
    }

    /**
     * Get getMediaInfo
     *
     * @return $this
     * @param string $keyword
     * @param string $extra_details
     */
    public function getMediaInfo($og_media_id)
    {
        $permanent_token = $this->getPermanenToken();
        $bynder_domain = $this->getBynderDom();
        $remove = array("http://","https://","/");
        $bynder_domain = str_replace($remove,"",$bynder_domain);

        /* $send_fields = [
            'Year' => "2024",
            'bynder_metaproperty_collection' => $collection_data_value
        ]; */
        
		$url = 'https://' . $bynder_domain . '/api/v4/media/'.$og_media_id.'?versions=1';

		// Get Magento's Curl client
		$this->_curl->setOption(CURLOPT_URL, $url);
		$this->_curl->setOption(CURLOPT_RETURNTRANSFER, true);
		$this->_curl->setOption(CURLOPT_TIMEOUT, 0);
		$this->_curl->setOption(CURLOPT_ENCODING, '');
		$this->_curl->setOption(CURLOPT_MAXREDIRS, 10);
		$this->_curl->setOption(CURLOPT_FOLLOWLOCATION, true);
		$this->_curl->setOption(CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);

        $this->_curl->addHeader("Content-Type", "application/json");
		// Set the Authorization header
		$this->_curl->addHeader("Authorization", "Bearer " . $permanent_token);

		// Execute the GET request
		$this->_curl->get($url);

		// Retrieve the response
		$response = $this->_curl->getBody();
		
        return $response;
    }

    /**
     * Get getMediaDownloadLocationForAssetItem
     *
     * @return $this
     * @param string $keyword
     * @param string $extra_details
     */
    public function getMediaDownloadLocationForAssetItem($og_media_id, $og_media_id_new)
    {
        $permanent_token = $this->getPermanenToken();
        $bynder_domain = $this->getBynderDom();
        $remove = array("http://","https://","/");
        $bynder_domain = str_replace($remove,"",$bynder_domain);

        /* $send_fields = [
            'property_Asset_Sub-Type' => $keyword,
            'Year' => "2024",
            'bynder_metaproperty_collection' => $collection_data_value
        ]; */
        
		$url = 'https://' . $bynder_domain . '/api/v4/media/' . $og_media_id . '/download/' . $og_media_id_new . '/';

		// Get Magento's Curl client
		$this->_curl->setOption(CURLOPT_URL, $url);
		$this->_curl->setOption(CURLOPT_RETURNTRANSFER, true);
		$this->_curl->setOption(CURLOPT_TIMEOUT, 0);
		$this->_curl->setOption(CURLOPT_ENCODING, '');
		$this->_curl->setOption(CURLOPT_MAXREDIRS, 10);
		$this->_curl->setOption(CURLOPT_FOLLOWLOCATION, true);
		$this->_curl->setOption(CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);

        $this->_curl->addHeader("Content-Type", "application/json");
		// Set the Authorization header
		$this->_curl->addHeader("Authorization", "Bearer " . $permanent_token);

		// Execute the GET request
		$this->_curl->get($url);

		// Retrieve the response
		$response = $this->_curl->getBody();
		
        echo "<pre>";
        print_r($response);
        exit;
		return $response;
    }

    

	/**
     * Get brandName
     *
     * @return $this
     * @param string $name
     */
    public function getBrandName($name)
	{
		$collection = $this->brandOptionCollectionFactory->create();
		$collection->addFieldToFilter('option_name', ['eq' => $name]);
		return $collection->getData();
	}
	/**
     * Get Customer Visibility Name
     *
     * @return $this
     * @param string $name
     */
    public function getCustomerVisibilityName($name)
	{
		$collection = $this->customerVisibilityOptionCollectionFactory->create();
		$collection->addFieldToFilter('option_name', ['eq' => $name]);
		return $collection->getData();
	}
	/**
     * Get file Catagoty Name
     *
     * @return $this
     * @param string $name
     */
    public function getFileCatagoryName($name)
	{
		$collection = $this->fileCategoryOptionCollectionFactory->create();
		$collection->addFieldToFilter('option_name', ['eq' => $name]);
		return $collection->getData();
	}

    /**
     * Get BrandLabel
     *
     * @return $this
     * @param string $label
     */
    public function getBrandLabel($label)
	{
		$collection = $this->brandOptionCollectionFactory->create();
		$collection->addFieldToFilter('option_label', ['eq' => $label]);
		return $collection->getData();
	}

    /**
     * Get CustomerVisibilityLabel
     *
     * @return $this
     * @param string $label
     */
    public function getCustomerVisibilityLabel($label)
	{
		$collection = $this->customerVisibilityOptionCollectionFactory->create();
		$collection->addFieldToFilter('option_label', ['eq' => $label]);
		return $collection->getData();
	}

    /**
     * Get getFileCatagory Label
     *
     * @return $this
     * @param string $label
     */
    public function getFileCatagoryLabel($label)
	{
		$collection = $this->fileCategoryOptionCollectionFactory->create();
		$collection->addFieldToFilter('option_label', ['eq' => $label]);
		return $collection->getData();
	}
}
