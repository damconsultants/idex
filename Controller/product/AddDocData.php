<?php
namespace DamConsultants\Idex\Controller\Product;

use DamConsultants\Idex\Model\ResourceModel\Collection\BynderTempDocDataCollectionFactory;

class AddDocData extends \Magento\Framework\App\Action\Action
{
    /**
     * @var string $_pageFactory;
     */
    protected $_pageFactory;
    /**
     * @var $_product
     */
    protected $_product;
    /**
     * @var $file
     */
    protected $file;
    /**
     * @var $resultJsonFactory
     */
    protected $resultJsonFactory;
    /**
     * @var $driverFile
     */
    protected $driverFile;
    /**
     * @var $storeManagerInterface
     */
    protected $storeManagerInterface;
    /**
     * @var $cookieManager
     */
    protected $cookieManager;
    /**
     * @var $productActionObject
     */
    protected $productActionObject;
    /**
     * @var $_registry
     */
    protected $_registry;
    /**
     * @var $_resource
     */
    protected $_resource;
    /**
     * @var $cookieMetadataFactory
     */
    protected $cookieMetadataFactory;
    /**
     * @var $bynderTempDocData
     */
    protected $bynderTempDocData;
    /**
     * @var $bynderTempDocDataCollectionFactory
     */
    protected $bynderTempDocDataCollectionFactory;
    /**
     * Add Data.
     *
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Framework\View\Result\PageFactory $pageFactory
     * @param \Magento\Catalog\Model\Product $product
     * @param \Magento\Framework\Filesystem\Io\File $file
     * @param \Magento\Framework\Filesystem\Driver\File $driverFile
     * @param \Magento\Framework\Controller\Result\JsonFactory $jsonFactory
     * @param \Magento\Catalog\Model\Product\Action $productActionObject
     * @param \Magento\Framework\Stdlib\CookieManagerInterface $cookieManager
     * @param \Magento\Store\Model\StoreManagerInterface $storeManagerInterface
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\App\ResourceConnection $resource
     * @param \Magento\Framework\Stdlib\Cookie\CookieMetadataFactory $cookieMetadataFactory
     * @param \DamConsultants\Idex\Model\BynderTempDocDataFactory $bynderTempDocData
     * @param BynderTempDocDataCollectionFactory $bynderTempDocDataCollectionFactory
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $pageFactory,
        \Magento\Catalog\Model\Product $product,
        \Magento\Framework\Filesystem\Io\File $file,
        \Magento\Framework\Filesystem\Driver\File $driverFile,
        \Magento\Framework\Controller\Result\JsonFactory $jsonFactory,
        \Magento\Catalog\Model\Product\Action $productActionObject,
        \Magento\Framework\Stdlib\CookieManagerInterface $cookieManager,
        \Magento\Store\Model\StoreManagerInterface $storeManagerInterface,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\App\ResourceConnection $resource,
        \Magento\Framework\Stdlib\Cookie\CookieMetadataFactory $cookieMetadataFactory,
        \DamConsultants\Idex\Model\BynderTempDocDataFactory $bynderTempDocData,
        BynderTempDocDataCollectionFactory $bynderTempDocDataCollectionFactory,
    ) {
        $this->_pageFactory = $pageFactory;
        $this->_product = $product;
        $this->file = $file;
        $this->resultJsonFactory = $jsonFactory;
        $this->driverFile = $driverFile;
        $this->storeManagerInterface = $storeManagerInterface;
        $this->cookieManager = $cookieManager;
        $this->productActionObject = $productActionObject;
        $this->_registry = $registry;
        $this->_resource = $resource;
        $this->cookieMetadataFactory = $cookieMetadataFactory;
        $this->bynderTempDocData = $bynderTempDocData;
        $this->bynderTempDocDataCollectionFactory = $bynderTempDocDataCollectionFactory;
        return parent::__construct($context);
    }
    /**
     * Execute
     *
     * @return $this
     */
    public function execute()
    {
        $product_id = $this->getRequest()->getParam('product_id');
        $coockie_id = $this->getRequest()->getParam('doc_coockie_id');
        $bynder_doc = $this->getRequest()->getParam('doc');
        $storeId = $this->storeManagerInterface->getStore()->getId();
        $product = $this->_product->load($product_id);
        $bynder_value = $product->getData('bynder_document');
        $item_old_value = json_decode($bynder_value, true);
        $item_old_value = $item_old_value["asset_list"];
        $item_old_asset_value = json_decode($bynder_value, true);
        $old_asset_detail_array = $item_old_asset_value['assets_extra_details'];
        $ajax_value = json_decode($bynder_doc, true);
        $ajax_hash_id = [];
        $bynder_data = [];
        $bynder_extra_data = [];
        //echo "<pre>"; print_r($ajax_value);
        //echo "<pre>"; print_r($item_old_value);
        foreach ($ajax_value as $a_value) {
            $ajax_hash_id[] = $a_value['hash_id'];
        }
        foreach ($item_old_value as $value) {
            if (in_array($value['hash_id'], $ajax_hash_id)) {
                $bynder_data[] = $value;
            }
        }
        foreach ($old_asset_detail_array as $hash_id_key => $value_data) {
            //echo "<pre> extara "; print_r($hash_id_key);
            if (in_array($hash_id_key, $ajax_hash_id)) {
                $bynder_extra_data[$hash_id_key] = $value_data;
            }
        }
        $update_latest_code = [
            "asset_list" => $bynder_data,
            "assets_extra_details" => $bynder_extra_data
        ];
        $new_value_array = json_encode($update_latest_code, true);
        $updated_values = [
            'bynder_document' => $new_value_array
        ];
        $this->productActionObject->updateAttributes(
            [$product_id],
            $updated_values,
            $storeId
        );
       
        $new_bynder_value = $product->getResource()->getAttributeRawValue(
            $product_id,
            'bynder_document',
            $storeId
        );
        //echo "<pre>"; print_r($new_bynder_value);
        /*if ($coockie_id == 0) {
            $data = [
                "value" => $bynder_doc,
                "product_id" => $product_id
            ];
            $bynderTempDocData = $this->bynderTempDocData->create();
            $bynderTempDocData->setData($data);
            $bynderTempDocData->save();
            $collectionData = $this->bynderTempDocDataCollectionFactory->create()->load();
            if (!empty($collectionData)) {
                $lastAddedId = "";
                foreach ($collectionData as $data) {
                    $lastAddedId = $data['id'];
                }
            }
        } else {
            $records = $this->bynderTempDocDataCollectionFactory->create();
            $records->addFieldToFilter('product_id', ['eq' => [$product_id]])->load();
            if (empty($records)) {
                $data = [
                    "value" => $bynder_doc,
                    "product_id" => $product_id
                ];
                $bynderTempDocData = $this->bynderTempDocData->create();
                $bynderTempDocData->setData($data);
                $bynderTempDocData->save();
                $collectionData = $this->bynderTempDocDataCollectionFactory->create()->load();
                if (!empty($collectionData)) {
                    $lastAddedId = "";
                    foreach ($collectionData as $data) {
                        $lastAddedId = $data['id'];
                    }
                }
            } else {
                $new_data = [
                    "value" => $bynder_doc,
                    "product_id" => $product_id
                ];
                $bynderTempDocData = $this->bynderTempDocData->create();
                $bynderTempDocData->load($coockie_id);
                $bynderTempDocData->setData($new_data);
                $bynderTempDocData->save();
                $lastAddedId = $coockie_id;
            }
        }
        $publicCookieMetadata = $this->cookieMetadataFactory->createPublicCookieMetadata();
        $publicCookieMetadata->setDurationOneYear();
        $publicCookieMetadata->setPath('/');
        $publicCookieMetadata->setHttpOnly(false);
        $this->cookieManager->setPublicCookie(
            'doc_coockie_id',
            $lastAddedId,
            $publicCookieMetadata
        );*/
        $resultJson = $this->resultJsonFactory->create();
        return $resultJson->setData([
            'new_bynder_value' => $new_bynder_value,
            'success' => true
        ]);
    }
}
