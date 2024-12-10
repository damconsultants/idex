<?php

namespace DamConsultants\Idex\Observer;

use Magento\Framework\Event\ObserverInterface;
use DamConsultants\Idex\Model\ResourceModel\Collection\MetaPropertyCollectionFactory;
use DamConsultants\Idex\Model\ResourceModel\Collection\BynderMediaTableCollectionFactory;
use DamConsultants\Idex\Model\ResourceModel\Collection\BynderTempDataCollectionFactory;
use DamConsultants\Idex\Model\ResourceModel\Collection\BynderTempDocDataCollectionFactory;

class ProductDataSaveAfter implements ObserverInterface
{
    /**
     * @var $cookieManager
     */
    protected $cookieManager;
    /**
     * @var $cookieManager
     */
    protected $cookieMetadataFactory;
    /**
     * @var $cookieManager
     */
    protected $productActionObject;
    /**
     * @var $cookieManager
     */
    protected $_byndersycData;
    /**
     * @var $cookieManager
     */
    protected $datahelper;
    /**
     * @var $cookieManager
     */
    protected $bynderMediaTable;
    /**
     * @var $cookieManager
     */
    protected $bynderMediaTableCollectionFactory;
    /**
     * @var $cookieManager
     */
    protected $metaPropertyCollectionFactory;
    /**
     * @var $cookieManager
     */
    protected $_collection;
    /**
     * @var $cookieManager
     */
    protected $bynderTempData;
    /**
     * @var $cookieManager
     */
    protected $bynderTempDataCollectionFactory;
    /**
     * @var $cookieManager
     */
    protected $bynderTempDocData;
    /**
     * @var $cookieManager
     */
    protected $bynderTempDocDataCollectionFactory;
    /**
     * @var $cookieManager
     */
    protected $_resource;
    /**
     * @var $cookieManager
     */
    protected $storeManagerInterface;
    /**
     * @var $cookieManager
     */
    protected $messageManager;
    /**
     * @var $cookieManager
     */
    protected $resultRedirectFactory;

    /**
     * Product save after
     * @param \Magento\Framework\Stdlib\CookieManagerInterface $cookieManager
     * @param \Magento\Framework\Stdlib\Cookie\CookieMetadataFactory $cookieMetadataFactory
     * @param \Magento\Catalog\Model\Product\Action $productActionObject
     * @param \DamConsultants\Idex\Model\BynderSycDataFactory $byndersycData
     * @param \DamConsultants\Idex\Model\ResourceModel\Collection\BynderSycDataCollectionFactory $collection
     * @param \DamConsultants\Idex\Model\BynderMediaTableFactory $bynderMediaTable
     * @param BynderMediaTableCollectionFactory $bynderMediaTableCollectionFactory
     * @param \DamConsultants\Idex\Model\BynderTempDataFactory $bynderTempData
     * @param BynderTempDataCollectionFactory $bynderTempDataCollectionFactory
     * @param \DamConsultants\Idex\Model\BynderTempDocDataFactory $bynderTempDocData
     * @param BynderTempDocDataCollectionFactory $bynderTempDocDataCollectionFactory
     * @param \Magento\Framework\App\ResourceConnection $resource
     * @param \DamConsultants\Idex\Helper\Data $DataHelper
     * @param MetaPropertyCollectionFactory $metaPropertyCollectionFactory
     * @param \Magento\Store\Model\StoreManagerInterface $storeManagerInterface
     * @param \Magento\Framework\Message\ManagerInterface $messageManager
     * @param \Magento\Backend\Model\View\Result\Redirect $resultRedirect
     */

    public function __construct(
        \Magento\Framework\Stdlib\CookieManagerInterface $cookieManager,
        \Magento\Framework\Stdlib\Cookie\CookieMetadataFactory $cookieMetadataFactory,
        \Magento\Catalog\Model\Product\Action $productActionObject,
        \DamConsultants\Idex\Model\BynderSycDataFactory $byndersycData,
        \DamConsultants\Idex\Model\ResourceModel\Collection\BynderSycDataCollectionFactory $collection,
        \DamConsultants\Idex\Model\BynderMediaTableFactory $bynderMediaTable,
        BynderMediaTableCollectionFactory $bynderMediaTableCollectionFactory,
        \DamConsultants\Idex\Model\BynderTempDataFactory $bynderTempData,
        BynderTempDataCollectionFactory $bynderTempDataCollectionFactory,
        \DamConsultants\Idex\Model\BynderTempDocDataFactory $bynderTempDocData,
        BynderTempDocDataCollectionFactory $bynderTempDocDataCollectionFactory,
        \Magento\Framework\App\ResourceConnection $resource,
        \DamConsultants\Idex\Helper\Data $DataHelper,
        MetaPropertyCollectionFactory $metaPropertyCollectionFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManagerInterface,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Magento\Backend\Model\View\Result\Redirect $resultRedirect
    ) {
        $this->cookieManager = $cookieManager;
        $this->cookieMetadataFactory = $cookieMetadataFactory;
        $this->productActionObject = $productActionObject;
        $this->_byndersycData = $byndersycData;
        $this->datahelper = $DataHelper;
        $this->bynderMediaTable = $bynderMediaTable;
        $this->bynderMediaTableCollectionFactory = $bynderMediaTableCollectionFactory;
        $this->metaPropertyCollectionFactory = $metaPropertyCollectionFactory;
        $this->_collection = $collection;
        $this->bynderTempData = $bynderTempData;
        $this->bynderTempDataCollectionFactory = $bynderTempDataCollectionFactory;
        $this->bynderTempDocData = $bynderTempDocData;
        $this->bynderTempDocDataCollectionFactory = $bynderTempDocDataCollectionFactory;
        $this->_resource = $resource;
        $this->storeManagerInterface = $storeManagerInterface;
        $this->messageManager = $messageManager;
        $this->resultRedirectFactory = $resultRedirect;
    }
    /**
     * Execute
     *
     * @return $this
     * @param \Magento\Framework\Event\Observer $observer
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $bdomain_chk_config = str_replace(
            "https://",
            "",
            $this->datahelper->getBynderDom()
        );
        $product = $observer->getProduct();
        $productId = $observer->getProduct()->getId();
        $product_sku_key = $product->getData('sku');

        $bynder_multi_img = $product->getData('bynder_multi_img');

        /**Doing new code and new requirements for theines */

        $bynder_document = $product->getData('bynder_document');
        $storeId = $this->storeManagerInterface->getStore()->getId();
        $all_meta_properties = $metaProperty_collection = $this->metaPropertyCollectionFactory->create()->getData();
        $collection_data_value = [];
        $collection_data_slug_val = [];
        $image_coockie_id = $this->cookieManager->getCookie('image_coockie_id');
        $doc_coockie_id = $this->cookieManager->getCookie('doc_coockie_id');
        $image = "";
        $document = "";
        if ($image_coockie_id != 0) {
            $bynderTempdata = $this->bynderTempDataCollectionFactory->create()
            ->addFieldToFilter('id', $image_coockie_id)->load();
            if (isset($bynderTempdata)) {
                foreach ($bynderTempdata as $record) {
                    $image = $record['value'];
                }
            }
        } else {
            $image = $bynder_multi_img;
        }
        if ($doc_coockie_id != 0) {
            $bynderTempdocdata = $this->bynderTempDocDataCollectionFactory->create()
            ->addFieldToFilter('id', $doc_coockie_id)->load();
            if (isset($bynderTempdocdata)) {
                foreach ($bynderTempdocdata as $recorddoc) {
                    $document = $recorddoc['value'];
                }
            }
        } else {
            $document = $bynder_document;
        }
        if (count($metaProperty_collection) >= 1) {
            foreach ($metaProperty_collection as $key => $collection_value) {
                $collection_data_value[] = [
                    'id' => $collection_value['id'],
                    'property_name' => $collection_value['property_name'],
                    'property_id' => $collection_value['property_id'],
                    'magento_attribute' => $collection_value['magento_attribute'],
                    'attribute_id' => $collection_value['attribute_id'],
                    'bynder_property_slug' => $collection_value['bynder_property_slug'],
                    'system_slug' => $collection_value['system_slug'],
                    'system_name' => $collection_value['system_name']
                ];
                $collection_data_slug_val[$collection_value['system_slug']] = [
                    'bynder_property_slug' => $collection_value['bynder_property_slug'],
                    'property_id' => $collection_value['property_id']
                ];
            }
        }
		try{
			if (isset($collection_data_slug_val["sku"]["property_id"])) {
				/******************************Document Section******************************************************************************** */
				if (!empty($document)) {
                    $m_id = [];
                    $new_changed_bynder_doc_attribute = json_decode($document, true);
                    $new_changed_bynder_doc_attribute = $new_changed_bynder_doc_attribute['asset_list'];
                    foreach ($new_changed_bynder_doc_attribute as $doc) {
                        $m_id[] = $doc['bynder_md_id'];
                        //$this->getDeleteMedaiDataTable($product_sku_key, $doc['bynder_md_id']);
                    }
                    //$this->getInsertMedaiDataTable($product_sku_key, $m_id);
					$this->productActionObject->updateAttributes([$productId], ['bynder_document' => $document], $storeId);
					$this->bynderTempDocData->create()->load($doc_coockie_id)->delete();
					$publicCookieMetadata = $this->cookieMetadataFactory->createPublicCookieMetadata();
					$publicCookieMetadata->setDurationOneYear();
					$publicCookieMetadata->setPath('/');
					$publicCookieMetadata->setHttpOnly(false);

					$this->cookieManager->setPublicCookie(
						'doc_coockie_id',
						0,
						$publicCookieMetadata
					);
				} else {
                    $this->productActionObject->updateAttributes([$productId], ['bynder_document' => ""], $storeId);
					$this->bynderTempDocData->create()->load($doc_coockie_id)->delete();
					$publicCookieMetadata = $this->cookieMetadataFactory->createPublicCookieMetadata();
					$publicCookieMetadata->setDurationOneYear();
					$publicCookieMetadata->setPath('/');
					$publicCookieMetadata->setHttpOnly(false);

					$this->cookieManager->setPublicCookie(
						'doc_coockie_id',
						0,
						$publicCookieMetadata
					);
                }
				/******************************************************************************************************************** */
				/***************************Video and Image Section ***************************************************************** */
				$flag = 0;
                if (isset($image)) {
                    $image_json = json_decode($image, true);
                    $image_json = $image_json['asset_list'];
                    $type = [];
                    if (!empty($image_json)) {
                        foreach ($image_json as $img) {
                            $type[] = $img['item_type'];
                        }
                        /*  IMAGE & VIDEO == 1
                        IMAGE == 2
                        VIDEO == 3 */
                        if (in_array("IMAGE", $type) && in_array("VIDEO", $type)) {
                            $flag = 1;
                        } elseif (in_array("IMAGE", $type)) {
                            $flag = 2;
                        } elseif (in_array("VIDEO", $type)) {
                            $flag = 3;
                        }
                    }
                    $m_id = [];
                    if (!empty($image)) {
                        $new_changed_bynder_img_attribute = json_decode($image, true);
                        $new_changed_bynder_img_attribute = $new_changed_bynder_img_attribute['asset_list'];
                        foreach ($new_changed_bynder_img_attribute as $img) {
                            $m_id[] = $img['bynder_md_id'];
                            $this->getDeleteMedaiDataTable($product_sku_key, $img['bynder_md_id']);
                        }
                        $this->getInsertMedaiDataTable($product_sku_key, $m_id);
                        $this->productActionObject->updateAttributes(
                            [$productId],
                            ['bynder_isMain' => $flag],
                            $storeId
                        );
                        $this->productActionObject->updateAttributes(
                            [$productId],
                            ['bynder_multi_img' => $image],
                            $storeId
                        );
                        $this->bynderTempData->create()->load($image_coockie_id)->delete();
                        $publicCookieMetadata = $this->cookieMetadataFactory->createPublicCookieMetadata();
                        $publicCookieMetadata->setDurationOneYear();
                        $publicCookieMetadata->setPath('/');
                        $publicCookieMetadata->setHttpOnly(false);
                        $this->cookieManager->setPublicCookie(
                            'image_coockie_id',
                            0,
                            $publicCookieMetadata
                        );
                    }
                } else {
                    $this->productActionObject->updateAttributes([$productId], ['bynder_isMain' => ""], $storeId);
                    $this->productActionObject->updateAttributes(
                        [$productId],
                        ['bynder_multi_img' => $image],
                        $storeId
                    );
                    $this->productActionObject->updateAttributes([$productId], ['bynder_cron_sync' => ""], $storeId);
                    $this->productActionObject->updateAttributes([$productId], ['bynder_auto_replace' => ""], $storeId);
                    $this->bynderTempData->create()->load($image_coockie_id)->delete();
                    $publicCookieMetadata = $this->cookieMetadataFactory->createPublicCookieMetadata();
                    $publicCookieMetadata->setDurationOneYear();
                    $publicCookieMetadata->setPath('/');
                    $publicCookieMetadata->setHttpOnly(false);
                    $this->cookieManager->setPublicCookie(
                        'image_coockie_id',
                        0,
                        $publicCookieMetadata
                    );
                }
			}
		} catch (\Exception $e) {
			$this->productActionObject->updateAttributes([$productId], ['bynder_isMain' => ""], $storeId);
			$this->productActionObject->updateAttributes([$productId], ['bynder_multi_img' => $image], $storeId);
			$this->productActionObject->updateAttributes([$productId], ['bynder_cron_sync' => ""], $storeId);
			$this->productActionObject->updateAttributes([$productId], ['bynder_auto_replace' => ""], $storeId);
			$this->productActionObject->updateAttributes([$productId], ['bynder_document' => $document], $storeId);
			$this->bynderTempData->create()->load($image_coockie_id)->delete();
			$this->bynderTempDocData->create()->load($doc_coockie_id)->delete();
			$publicCookieMetadata = $this->cookieMetadataFactory->createPublicCookieMetadata();
			$publicCookieMetadata->setDurationOneYear();
			$publicCookieMetadata->setPath('/');
			$publicCookieMetadata->setHttpOnly(false);
			$this->cookieManager->setPublicCookie(
				'image_coockie_id',
				0,
				$publicCookieMetadata
			);
			$this->cookieManager->setPublicCookie(
				'doc_coockie_id',
				0,
				$publicCookieMetadata
			);
		}
    }
    /**
     * Is Json
     *
     * @param string $sku
     * @param string $m_id
     * @return $this
     */
    public function getInsertMedaiDataTable($sku, $m_id)
    {
        $model = $this->bynderMediaTable->create();
        $modelcollection = $this->bynderMediaTableCollectionFactory->create()
        ->addFieldToFilter('sku', ['eq' => [$sku]])->load();
        $table_m_id = [];
        if (!empty($modelcollection)) {
            foreach ($modelcollection as $mdata) {
                $table_m_id[] = $mdata['media_id'];
            }
        }
        $media_diff = array_diff($m_id, $table_m_id);
        foreach ($media_diff as $new_data) {
            $data_image_data = [
                'sku' => $sku,
                'media_id' => trim($new_data),
                'status' => "1",
            ];
            $model->setData($data_image_data);
            $model->save();
        }
    }
    /**
     * Is Json
     *
     * @param string $sku
     * @param string $media_id
     * @return $this
     */
    public function getDeleteMedaiDataTable($sku, $media_id)
    {
        $model = $this->bynderMediaTableCollectionFactory->create()
        ->addFieldToFilter('sku', ['eq' => [$sku]])->load();
        foreach ($model as $mdata) {
            if ($mdata['media_id'] != $media_id) {
                $this->bynderMediaTable->create()->load($mdata['id'])->delete();

            }
        }
    }
}
