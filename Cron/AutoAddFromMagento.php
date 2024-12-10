<?php

namespace DamConsultants\Idex\Cron;

use Exception;
use \Psr\Log\LoggerInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Catalog\Model\ProductRepository;
use Magento\Catalog\Model\Product\Action;
use DamConsultants\Idex\Model\BynderFactory;
use DamConsultants\Idex\Model\ResourceModel\Collection\MetaPropertyCollectionFactory;
use DamConsultants\Idex\Model\ResourceModel\Collection\BynderMediaTableCollectionFactory;

class AutoAddFromMagento
{
    /**
     * @var $logger
     */
    protected $logger;
    /**
     * @var $_productRepository
     */
    protected $_productRepository;
    /**
     * @var $collectionFactory
     */
    protected $collectionFactory;
    /**
     * @var $datahelper
     */
    protected $datahelper;
    /**
     * @var $action
     */
    protected $action;
    /**
     * @var $metaPropertyCollectionFactory
     */
    protected $metaPropertyCollectionFactory;
    /**
     * @var $bynderMediaTable
     */
    protected $bynderMediaTable;
    /**
     * @var $bynderMediaTableCollectionFactory
     */
    protected $bynderMediaTableCollectionFactory;
    /**
     * @var $storeManagerInterface
     */
    protected $storeManagerInterface;
    /**
     * @var $bynder
     */
    protected $bynder;
    /**
     * @var $_byndersycData
     */
    protected $_byndersycData;
	
    /**
     * Featch Null Data To Magento
     * @param LoggerInterface $logger
     * @param ProductRepository $productRepository
     * @param \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $collectionFactory
     * @param StoreManagerInterface $storeManagerInterface
     * @param \DamConsultants\Idex\Helper\Data $DataHelper
     * @param \DamConsultants\Idex\Model\BynderMediaTableFactory $bynderMediaTable
     * @param BynderMediaTableCollectionFactory $bynderMediaTableCollectionFactory
	 * @param \DamConsultants\Idex\Model\BynderSycDataFactory $byndersycData
     * @param Action $action
     * @param MetaPropertyCollectionFactory $metaPropertyCollectionFactory
     * @param BynderFactory $bynder
     */
    public function __construct(
        LoggerInterface $logger,
        ProductRepository $productRepository,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $collectionFactory,
        StoreManagerInterface $storeManagerInterface,
        \DamConsultants\Idex\Helper\Data $DataHelper,
        \DamConsultants\Idex\Model\BynderMediaTableFactory $bynderMediaTable,
        BynderMediaTableCollectionFactory $bynderMediaTableCollectionFactory,
		\DamConsultants\Idex\Model\BynderSycDataFactory $byndersycData,
        Action $action,
        MetaPropertyCollectionFactory $metaPropertyCollectionFactory,
        BynderFactory $bynder
    ) {

        $this->logger = $logger;
        $this->_productRepository = $productRepository;
        $this->collectionFactory = $collectionFactory;
        $this->datahelper = $DataHelper;
        $this->action = $action;
		$this->_byndersycData = $byndersycData;
        $this->metaPropertyCollectionFactory = $metaPropertyCollectionFactory;
        $this->bynderMediaTable = $bynderMediaTable;
        $this->bynderMediaTableCollectionFactory = $bynderMediaTableCollectionFactory;
        $this->storeManagerInterface = $storeManagerInterface;
        $this->bynder = $bynder;
    }
    /**
     * Execute
     *
     * @return boolean
     */
    public function execute()
    {
        $writer = new \Zend_Log_Writer_Stream(BP . '/var/log/AutoAddFromMagento.log');
        $logger = new \Zend_Log();
        $logger->addWriter($writer);
        $logger->info("Auto Add Image Value");
        $enable = $this->datahelper->getAutoCronEnable();
        if (!$enable) {
            return false;
        }
        $product_collection = $this->collectionFactory->create();
        $product_sku_limit = (int)$this->datahelper->getProductSkuLimitConfig();
        if (!empty($product_sku_limit)) {
            $product_collection->getSelect()->limit($product_sku_limit);
        } else {
            $product_collection->getSelect()->limit(50);
        }

        $product_collection->addAttributeToSelect('*')
            ->addAttributeToFilter(
                [
                    ['attribute' => 'bynder_multi_img', 'notnull' => true]
                ]
            )
            ->addAttributeToFilter(
                [
                    ['attribute' => 'bynder_auto_replace', 'null' => true]
                ]
            )
            ->load();

        $property_id = null;
        $collection = $this->metaPropertyCollectionFactory->create()->getData();
        $meta_properties = $this->getMetaPropertiesCollection($collection);

        $collection_value = $meta_properties['collection_data_value'];
        $collection_slug_val = $meta_properties['collection_data_slug_val'];

        $productSku_array = [];
        foreach ($product_collection->getData() as $product) {
            $productSku_array[] = $product['sku'];
        }
        $logger->info("sku -> ". json_encode($productSku_array, true));
        if (count($productSku_array) > 0) {
            foreach ($productSku_array as $sku) {
                if ($sku != "") {
                    //$bd_sku = trim(preg_replace('/[^A-Za-z0-9]/', '_', $sku));
                    $get_data = $this->datahelper->getImageSyncWithProperties($sku, $property_id, $collection_value);
                    if (!empty($get_data) && $this->getIsJSON($get_data)) {
                        $respon_array = json_decode($get_data, true);
                        if ($respon_array['status'] == 1) {
                            $convert_array = json_decode($respon_array['data'], true);
                            if ($convert_array['status'] == 1) {
                                $current_sku = $sku;
                                try {
                                    $this->getDataItem( $convert_array, $collection_slug_val, $current_sku);
                                } catch (Exception $e) {
                                    $insert_data = [
                                        "sku" => $sku,
                                        "message" => $e->getMessage(),
                                        'media_id' => "",
                                        "data_type" => ""
                                    ];
                                }
                                
                            } else {
                                $insert_data = [
                                    "sku" => $sku,
                                    "message" => $convert_array['data'],
                                    'media_id' => "",
                                    "data_type" => ""
                                ];
                            }
                        } else {
                            $insert_data = [
                                "sku" => $sku,
                                "message" => 'Please Select The Metaproperty First.....',
                                'media_id' => "",
                                "data_type" => ""
                            ];
                        }
                    } else {
                        $insert_data = [
                            "sku" => $sku,
                            "message" => "Something problem in DAM side please contact to developer.",
                            'media_id' => "",
                            "data_type" => ""
                        ];
                    }
                }
            }
        } else {
            $product_collection = $this->collectionFactory->create()
            ->addAttributeToSelect('*')
            ->addAttributeToFilter('visibility', \Magento\Catalog\Model\Product\Visibility::VISIBILITY_BOTH)
            ->addAttributeToFilter('status', \Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED)
            ->addAttributeToFilter(
                [
                    ['attribute' => 'bynder_auto_replace', 'notnull' => true]
                ]
            )
            ->load();
            $id = [];
            foreach ($product_collection as $product) {
                $id[] = $product->getId();
            }
            $storeId = $this->storeManagerInterface->getStore()->getId();
            $this->action->updateAttributes(
                $id,
                ['bynder_auto_replace' => ""],
                $storeId
            );
            $logger->info("bynder_auto_replace null ");
        }
        return true;
    }

    /**
     * Get Meta Properties Collection
     *
     * @param array $collection
     * @return array $response_array
     */
    public function getMetaPropertiesCollection($collection)
    {
        $collection_data_value = [];
        $collection_data_slug_val = [];
        if (count($collection) >= 1) {
            foreach ($collection as $key => $collection_value) {
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
                ];
            }
        }
        $response_array = [
            "collection_data_value" => $collection_data_value,
            "collection_data_slug_val" => $collection_data_slug_val
        ];
        return $response_array;
    }

    /**
     * Is int
     *
     * @return $this
     */
    public function getMyStoreId()
    {
        $storeId = $this->storeManagerInterface->getStore()->getId();
        return $storeId;
    }

    /**
     * Is Json
     *
     * @param string $string
     * @return $this
     */
    public function getIsJSON($string)
    {
        return ((json_decode($string)) === null) ? false : true;
    }
	/**
     * Is Json
     *
     * @param array $insert_data
     * @return $this
     */
    public function getInsertDataTable($insert_data)
    {
        $model = $this->_byndersycData->create();
        $data_image_data = [
            'sku' => $insert_data['sku'],
            'bynder_data' =>$insert_data['message'],
            'bynder_data_type' => $insert_data['data_type'],
            'media_id' => $insert_data['media_id'],
            'remove_for_magento' => $insert_data['remove_for_magento'],
            'added_on_cron_compactview' => $insert_data['added_on_cron_compactview'],
            'lable' => $insert_data['lable']
        ];

        $model->setData($data_image_data);
        $model->save();
    }
    /**
     * Is Json
     *
     * @param array $insert_data
     * @return $this
     */
    /*public function getInsertDataTable($insert_data)
    {
        $model = $this->_bynderAutoReplaceData->create();
        $data_image_data = [
            'sku' => $insert_data['sku'],
            'bynder_data' =>$insert_data['message'],
            'media_id' => $insert_data['media_id'],
            'bynder_data_type' => $insert_data['data_type']
        ];
        $model->setData($data_image_data);
        $model->save();
    }*/
    /**
     * Is Json
     *
     * @param mixed $sku
     * @param mixed $m_id
     * @param string $product_ids
     * @param mixed $storeId
     * @return $this
     */
    public function getInsertMedaiDataTable($sku, $m_id, $product_ids, $storeId)
    {
        $model = $this->bynderMediaTable->create();
        $modelcollection = $this->bynderMediaTableCollectionFactory->create();
        $modelcollection->addFieldToFilter('sku', ['eq' => [$sku]])->load();
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
        $updated_values = [
            'bynder_delete_cron' => 1
        ];
        $this->action->updateAttributes(
            [$product_ids],
            $updated_values,
            $storeId
        );
    }
    /**
     * Is Json
     *
     * @param mixed $sku
     * @param string $media_id
     * @return $this
     */
    public function getDeleteMedaiDataTable($sku, $media_id)
    {
        $model = $this->bynderMediaTableCollectionFactory->create()->addFieldToFilter('sku', ['eq' => [$sku]])->load();
        foreach ($model as $mdata) {
            if ($mdata['media_id'] != $media_id) {
                $this->bynderMediaTable->create()->load($mdata['id'])->delete();

            }
        }
    }
    /**
     * Get Data Item
     *
     * @param array $select_attribute
     * @param array $convert_array
     * @param array $collection_data_slug_val
     * @param string $current_sku
     */
    public function getDataItem($convert_array, $collection_data_slug_val, $current_sku)
    {
        $data_arr = [];
        $video_data_arr = [];
        $doc_data_arr = [];
        $data_val_arr = [];
        $video_data_val_arr = [];
        $doc_data_val_arr = [];
        $temp_arr = [];
        $bynder_image_role = [];
        $is_order = [];
        if ($convert_array['status'] != 0) {
			$assets_extra_details = array();
            $assets_extra_details_video = array();
            $assets_extra_details_doc = array();
			$type = [];
            foreach ($convert_array['data'] as $data_value) {
                $type[] = $data_value['type'];
                $bynder_media_id = $data_value['id'];
                $image_data = $data_value['thumbnails'];
                $bynder_image_role = $image_data['magento_role_options'];
                $bynder_alt_text = $image_data['img_alt_text'];
                $idHash = $data_value['idHash'];
                $sku_slug_name = "property_" . $collection_data_slug_val['sku']['bynder_property_slug'];
                /*$data_sku = $data_value[$sku_slug_name];*/
                $data_sku[0] = $current_sku;

                    /**
                 * Below code for multiple derivative according to image role
                 *
                 */
                $images_urls_list = [];
                $new_magento_role_list = [];
                $new_bynder_alt_text =[];
                $new_bynder_mediaid_text = [];
                $doc_new_bynder_mediaid_text = [];
                $video_new_bynder_mediaid_text = [];
                $hash_id = [];
                $video_hash_id = [];
                $doc_hash_id = [];
				$is_order = [];
                if ($data_value['type'] == "image") {
					$b = [];
					$c = [];
					$f = [];
					if(!empty($data_value["assets_extra_details"]['brands'])){
						foreach($data_value["assets_extra_details"]['brands'] as $brands) {
							$brandCollection = $this->datahelper->getBrandName($brands);
							$b['brands_lables'][] = $brandCollection[0]['option_label'];
						}
					}
					if(!empty($data_value["assets_extra_details"]['customer_visibility'])){
						foreach($data_value["assets_extra_details"]['customer_visibility'] as $customer) {
							$customerCollection = $this->datahelper->getCustomerVisibilityName($customer);
							$c['customer_visibility_lables'][] = $customerCollection[0]['option_label'];
						}
					}
					if(!empty($data_value["assets_extra_details"]['file_category'])){
						foreach($data_value["assets_extra_details"]['file_category'] as $file) {
							$fileCollection = $this->datahelper->getFileCatagoryName($file);
							$f['file_category_lables'][] = $fileCollection[0]['option_label'];
						}
					}
                    $assets_extra_details["assets_extra_details"][$data_value["idHash"]] = array_merge($data_value["assets_extra_details"],$b,$c,$f);
                    if (count($bynder_image_role) > 0) {
                        foreach ($bynder_image_role as $m_bynder_role) {
                            $lower_m_bynder_role = strtolower($m_bynder_role);
                            switch ($m_bynder_role) {
                                case "Catalog_Tile":
                                    $original_m_bynder_role = "Base";
                                    $original_m_bynder_role_slug = "Magento_Base";
                                    break;
                                case "Featured":
                                    $original_m_bynder_role = "Small";
                                    $original_m_bynder_role_slug = "Magento_Small";
                                    break;
                                case "Product_Page":
                                    $original_m_bynder_role = "Swatch";
                                    $original_m_bynder_role_slug = "Magento_Swatch";
                                    break;
                                case "Thumbnail":
                                    $original_m_bynder_role = "Thumbnail";
                                    $original_m_bynder_role_slug = "Magento_Thumbnail";
                                    break;
                                default:
                                    $original_m_bynder_role = $m_bynder_role;
                            }
                            if (isset($data_value["thumbnails"][$original_m_bynder_role_slug])) {
                                $images_urls_list[]= $data_value["thumbnails"][$original_m_bynder_role_slug]."\n";
                                $new_magento_role_list[] = $original_m_bynder_role."\n";

                                $alt_text_vl = $data_value["thumbnails"]["img_alt_text"];
                                if (is_array($data_value["thumbnails"]["img_alt_text"])) {
                                    $alt_text_vl = implode(" ", $data_value["thumbnails"]["img_alt_text"]);
                                }
                                $new_bynder_alt_text[] = (strlen($alt_text_vl) > 0)?$alt_text_vl."\n":"###\n";
                            } else {
                                if (isset($data_value["thumbnails"]["Magento_Original"])) {
                                    $images_urls_list[]= $data_value["thumbnails"]["Magento_Original"]."\n";
                                } else {
                                    $images_urls_list[]= $data_value["thumbnails"]["webimage"]."\n";
                                }
                                $new_magento_role_list[] = $original_m_bynder_role."\n";
                                $alt_text_vl = $data_value["thumbnails"]["img_alt_text"];
                                if (is_array($data_value["thumbnails"]["img_alt_text"])) {
                                    $alt_text_vl = implode(" ", $data_value["thumbnails"]["img_alt_text"]);
                                }
                                $new_bynder_alt_text[] = (strlen($alt_text_vl) > 0)?$alt_text_vl."\n":"###\n";
                            }
                            $new_bynder_mediaid_text[] = $bynder_media_id."\n";
                            $hash_id[] = $idHash."\n";
							if(isset($data_value["assets_extra_details"]["image_order"]) && !empty($data_value["assets_extra_details"]["image_order"])) {
                                foreach ($data_value["assets_extra_details"]["image_order"]  as $property_Magento_Media_Order) {
                                    $is_order[] = $property_Magento_Media_Order . "\n";
                                }
                            }
                        }
                    } else {
                        $new_magento_role_list[] = "###"."\n";
                        /* this part added because sometime role not avaiable but alt text will be there*/
                        $alt_text_vl = $data_value["thumbnails"]["img_alt_text"];
                        if (!empty($alt_text_vl)) {
                            $new_bynder_alt_text[] = $alt_text_vl."\n";
                        } else {
                            $new_bynder_alt_text[] = "###\n";
                        }
                        $new_bynder_mediaid_text[] = $bynder_media_id."\n";
                        $hash_id[] = $idHash."\n";
                        $magento_order_slug = "property_" . $collection_data_slug_val['image_order']['bynder_property_slug'];
                        if (isset($data_value["assets_extra_details"]["image_order"]) && !empty($data_value["assets_extra_details"]["image_order"])) {
                            foreach ($data_value["assets_extra_details"]["image_order"] as $property_Magento_Media_Order) {
                                $is_order[] = $property_Magento_Media_Order . "\n";
                            }
                        }
                    }
                    if (count($images_urls_list) == 0) {
                        if (isset($image_data["Magento_Original"])) {
                            $images_urls_list[] = $image_data["Magento_Original"]."\n";
                        } elseif (isset($data_value["thumbnails"]["webimage"])) {
                            $images_urls_list[] = $data_value["thumbnails"]["webimage"] . "\n";
                        } else {
                            $images_urls_list[] = "no image" . "\n";
                        }
                    }

                    /* chagne by kuldip ladola
                    OLD
                            "sku" => $data_sku[0],
                            'image_alt_text' => $bynder_alt_text,
                            "url" => $image_data["image_link"],
                            "type" => $data_value['type'],
                            'magento_image_role' => $bynder_image_rol
                    NEW

                    */
                    array_push($data_arr, $data_sku[0]);
                    $data_p = [
                        "sku" => $data_sku[0],
                        "url" => $images_urls_list, /* chagne by kuldip ladola for testing perpose */
                        "magento_image_role" => $new_magento_role_list,
                        "type" => $data_value['type'],
                        "image_alt_text" => $new_bynder_alt_text,
                        "bynder_media_id_new" => $new_bynder_mediaid_text,
                        'id_hash' => $hash_id,
						'is_order' => $is_order
                    ];
                    array_push($data_val_arr, $data_p);
                } elseif ($data_value['type'] == 'video') {
					$b = [];
					$c = [];
					$f = [];
					if(!empty($data_value["assets_extra_details"]['brands'])){
						foreach($data_value["assets_extra_details"]['brands'] as $brands) {
							$brandCollection = $this->datahelper->getBrandName($brands);
							$b['brands_lables'][] = $brandCollection[0]['option_label'];
						}
					}
					if(!empty($data_value["assets_extra_details"]['customer_visibility'])){
						foreach($data_value["assets_extra_details"]['customer_visibility'] as $customer) {
							$customerCollection = $this->datahelper->getCustomerVisibilityName($customer);
							$c['customer_visibility_lables'][] = $customerCollection[0]['option_label'];
						}
					}
					if(!empty($data_value["assets_extra_details"]['file_category'])){
						foreach($data_value["assets_extra_details"]['file_category'] as $file) {
							$fileCollection = $this->datahelper->getFileCatagoryName($file);
							$f['file_category_lables'][] = $fileCollection[0]['option_label'];
						}
					}
                    $assets_extra_details_video["assets_extra_details"][$data_value["idHash"]] = array_merge($data_value["assets_extra_details"],$b,$c,$f);
                    $video_link[] = $data_value["videoPreviewURLs"][0] . '@@' . $image_data["webimage"]."\n";
                    $video_new_bynder_mediaid_text[] = $bynder_media_id."\n";
                    $video_hash_id[] = $idHash."\n";
                    array_push($data_arr, $data_sku[0]);
                    $data_p = [
                        "sku" => $data_sku[0],
                        "url" => $video_link, /* chagne by kuldip ladola for testing perpose */
                        'magento_image_role' => $new_magento_role_list,
                        'image_alt_text' => $new_bynder_alt_text,
                        "type" => $data_value['type'],
                        'bynder_media_id_new' => $video_new_bynder_mediaid_text,
                        'id_hash' => $video_hash_id,
						'is_order' => $is_order
                    ];
                    array_push($data_val_arr, $data_p);

                } elseif($data_value['type'] == 'document') {
                    $assets_extra_details_doc["assets_extra_details"][$data_value["idHash"]] = $data_value["assets_extra_details"];
                    $doc_name = $data_value["name"];
                    $doc_name_with_space = preg_replace("/[^a-zA-Z]+/", "-", $doc_name);
                    $doc_link = $image_data["image_link"] . '@@' . $doc_name_with_space."\n";
                    $doc_new_bynder_mediaid_text[] = $bynder_media_id."\n";
                    $doc_hash_id[] = $idHash."\n";
                    array_push($doc_data_arr, $data_sku[0]);
                    $data_p = [
                        "sku" => $data_sku[0],
                        "url" => [$doc_link], /* chagne by kuldip ladola for testing perpose */
                        'magento_image_role' => $new_magento_role_list,
                        'image_alt_text' => $new_bynder_alt_text,
                        "type" => $data_value['type'],
                        'bynder_media_id_new' => $doc_new_bynder_mediaid_text,
                        'id_hash' => $doc_hash_id,
						'is_order' => $is_order
                    ];
                    array_push($doc_data_val_arr, $data_p);
                }
            }
        }
        $type = array_unique($type);
        //echo "<pre>"; print_r($assets_extra_details_doc); exit;
        /*
        else {
             $logger->info('No Data Found For API Side.');
        }
        */
		$bynder_extra_data = array(
            "extra_details" => $assets_extra_details
        );
        $bynder_extra_data_video = array(
            "extra_details" => $assets_extra_details_video
        );
        $bynder_extra_data_doc = array(
            "extra_details" => $assets_extra_details_doc
        );
        if (count($data_arr) > 0) {
            $this->getProcessItem($data_arr, $data_val_arr,$bynder_extra_data, $bynder_extra_data_video, $type);
        }
        if (count($doc_data_arr) > 0) {
            $this->getProcessDocItem($doc_data_arr, $doc_data_val_arr,$bynder_extra_data_doc);
        }
        /*
        else {
             $logger->info('No Data Found For API Side.');
        }
        */
    }
     /**
     * Get Process Item
     *
     * @param array $data_arr
     * @param array $data_val_arr
     */
    public function getProcessItem($data_arr, $data_val_arr ,$bynder_extra_data, $bynder_extra_data_video, $type)
    {
        /* $writer = new \Laminas\Log\Writer\Stream(BP . '/var/log/FeatchNullDataToMagento.log');
        $logger = new \Laminas\Log\Logger();
        $logger->addWriter($writer);
        $logger->info("getProcessItem funcation called"); */
        $image_value_details_role = [];
        $temp_arr = [];
        $byn_is_order = [];
        $image_alt_text = [];
        $byn_md_id_new = [];
		$is_hash = [];
		$byn_is_order = [];
        foreach ($data_arr as $key => $skus) {
            $temp_arr[$skus][] = implode("", $data_val_arr[$key]["url"]);
            $image_value_details_role[$skus][] = implode("", $data_val_arr[$key]["magento_image_role"]);
            $image_alt_text[$skus][] = implode("", $data_val_arr[$key]["image_alt_text"]);
            $bynder_media_id_new[$skus][] = implode("", $data_val_arr[$key]["bynder_media_id_new"]);
            $is_hash[$skus][] = implode("", $data_val_arr[$key]["id_hash"]);
            $img_type[$skus][] = $data_val_arr[$key]["type"];
			$byn_is_order[$skus][] = implode("", $data_val_arr[$key]["is_order"]);
        }
        //echo "<pre> image "; print_r($img_type);
        foreach ($temp_arr as $product_sku_key => $image_value) {
            $img_json = implode("", $image_value);
            $mg_role = implode("", $image_value_details_role[$product_sku_key]);
            $image_alt_text_value = implode("", $image_alt_text[$product_sku_key]);
            $bynder_media_id_value = implode("", $bynder_media_id_new[$product_sku_key]);
            $byd_hash_id = implode("", $is_hash[$product_sku_key]);
            $byd_type = implode(",", $img_type[$product_sku_key]);
			$byd_media_is_order = implode("", $byn_is_order[$product_sku_key]);
            $this->getUpdateImage(
                $img_json,
                $product_sku_key,
                $mg_role,
                $image_alt_text_value,
                $bynder_media_id_value,
				$bynder_extra_data,
                $bynder_extra_data_video,
				$byd_hash_id,
                $byd_type,
                $type,
				$byd_media_is_order
            );
        }
    }

     /**
     * Get Process Item
     *
     * @param array $data_arr
     * @param array $data_val_arr
     */
    public function getProcessDocItem($data_arr, $data_val_arr ,$bynder_extra_data)
    {
        $image_value_details_role = [];
        $temp_arr = [];
        $byn_is_order = [];
        $image_alt_text = [];
        $byn_md_id_new = [];
		$is_hash = [];
		$byn_is_order = [];
        foreach ($data_arr as $key => $skus) {
            $temp_arr[$skus][] = implode("", $data_val_arr[$key]["url"]);
            $image_value_details_role[$skus][] = implode("", $data_val_arr[$key]["magento_image_role"]);
            $image_alt_text[$skus][] = implode("", $data_val_arr[$key]["image_alt_text"]);
            $bynder_media_id_new[$skus][] = implode("", $data_val_arr[$key]["bynder_media_id_new"]);
            $is_hash[$skus][] = implode("", $data_val_arr[$key]["id_hash"]);
            $img_type[$skus][] = $data_val_arr[$key]["type"];
			$byn_is_order[$skus][] = implode("", $data_val_arr[$key]["is_order"]);
        }

        foreach ($temp_arr as $product_sku_key => $image_value) {
            $img_json = implode("", $image_value);
            $mg_role = implode("", $image_value_details_role[$product_sku_key]);
            $image_alt_text_value = implode("", $image_alt_text[$product_sku_key]);
            $bynder_media_id_value = implode("", $bynder_media_id_new[$product_sku_key]);
            $byd_hash_id = implode("", $is_hash[$product_sku_key]);
            $byd_type = implode(",", $img_type[$product_sku_key]);
			$byd_media_is_order = implode("", $byn_is_order[$product_sku_key]);
            $this->getUpdatedoc(
                $img_json,
                $product_sku_key,
                $mg_role,
                $image_alt_text_value,
                $bynder_media_id_value,
				$bynder_extra_data,
				$byd_hash_id,
                $byd_type,
				$byd_media_is_order
            );
        }
    }

    /**
     * Upate Item
     *
     * @return $this
     * @param string $img_json
     * @param string $product_sku_key
     * @param string $mg_img_role_option
     * @param string $img_alt_text
     * @param string $bynder_media_ids
     */
    public function getUpdateImage(
		$img_json,
		$product_sku_key,
		$mg_role,
		$image_alt_text_value,
		$bynder_media_id_value,
		$bynder_extra_data,
        $bynder_extra_data_video,
		$byd_hash_id,
		$byd_type,
        $all_type,
		$byd_media_is_order
	)
    {
        $diff_image_detail = [];
        $new_image_detail = [];
        $select_attribute = "image";
        $image_detail = [];
        $image_detail = [];
        $diff_image_detail = [];
        $video_detail_diff = [];
        $video_detail = [];
        try {
            
            $storeId = $this->storeManagerInterface->getStore()->getId();
            $_product = $this->_productRepository->get($product_sku_key);
            $product_ids = $_product->getId();
            $image_value = $_product->getBynderMultiImg();
            $auto_replace = $_product->getBynderAutoReplace();
            $img_type = explode(",", $byd_type);
			$img_type = array_unique($img_type);
            if (in_array("image", $all_type) || in_array("video", $all_type)) {
                $bynder_media_id = explode("\n", $bynder_media_id_value);
				$hashId = explode("\n", $byd_hash_id);
				$isOrder = explode("\n", $byd_media_is_order);
                if (!empty($image_value) && $auto_replace == null) {
                    $new_image_array = explode("\n", $img_json);
                    $new_alttext_array = explode("\n", $image_alt_text_value);
                    $new_magento_role_option_array = explode("\n", $mg_role);
                    $all_item_url = [];
                    $all_video_url = [];
                    $item_old_value = json_decode($image_value, true);
                    $item_old_value = $item_old_value["asset_list"];
					$item_old_asset_value = json_decode($image_value, true);
					$old_asset_detail_array = $item_old_asset_value['assets_extra_details'];
                    
					if (is_array($item_old_value)) {
						if (count($item_old_value) > 0) {
							foreach ($item_old_value as $img) {
                                if ($img['item_type'] == 'IMAGE') {
                                    $all_item_url[] = $img['item_url'];
                                } else {
                                    $all_video_url[] = $img['item_url'];
                                }
                            }
						}
                    }
                    foreach ($new_image_array as $vv => $new_image_value) {
                        if (trim($new_image_value) != "" && $new_image_value != "no image") {
                            if(strpos($new_image_value, '@@') == false) {
                                $item_url = explode("?", $new_image_value);
                                $media_image_explode = explode("/", $item_url[0]);
                                $img_altText_val = "";
                                if (isset($new_alttext_array[$vv])) {
                                    if ($new_alttext_array[$vv] != "###" && strlen(trim($new_alttext_array[$vv])) > 0) {
                                        $img_altText_val = $new_alttext_array[$vv];
                                    }
                                }

                                $curt_img_role = [];
                                if ($new_magento_role_option_array[$vv] != "###") {
                                    $curt_img_role = [$new_magento_role_option_array[$vv]];
                                }
                                $is_order = isset($isOrder[$vv]) ? $isOrder[$vv] : "";
                                $image_detail[] = [
                                    "item_url" => $new_image_value,
                                    "alt_text" => $img_altText_val,
                                    "image_role" => $curt_img_role,
                                    "item_type" => 'IMAGE',
                                    "thum_url" => $item_url[0],
                                    "bynder_md_id" => $bynder_media_id[$vv],
                                    "hash_id" => $hashId[$vv],
                                    "is_import" => 0,
									"is_order" => empty($is_order) ? "100" : $is_order
                                ];
								if (empty($all_item_url)) {
									$total_new_value = count($image_detail);
									if ($total_new_value > 1) {
										foreach ($image_detail as $nn => $n_img) {
											if ($n_img['item_type'] == "IMAGE" && $nn != ($total_new_value - 1)) {
												$new_mg_role_array = (array)$new_magento_role_option_array[$vv];
												if (count($n_img["image_role"]) > 0 && count($new_mg_role_array) > 0) {
													$result_val = array_diff($n_img["image_role"], $new_mg_role_array);
													$image_detail[$nn]["image_role"] = $result_val;
												}
											}
										}
									}
								}
                                if (!in_array($new_image_value, $all_item_url)) {
                                    $is_order = isset($isOrder[$vv]) ? $isOrder[$vv] : "";
                                    $diff_image_detail[] = [
                                        "item_url" => $new_image_value,
                                        "alt_text" => $img_altText_val,
                                        "image_role" => $curt_img_role,
                                        "item_type" => 'IMAGE',
                                        "thum_url" => $item_url[0],
                                        "bynder_md_id" => $bynder_media_id[$vv],
                                        "hash_id" => $hashId[$vv],
                                        "is_import" => 0,
										"is_order" => empty($is_order) ? "100" : $is_order
                                    ];
                                    $data_image_data = [
                                        'sku' => $product_sku_key,
                                        'message' => $new_image_value,
                                        'data_type' => '1',
                                        'media_id' => $bynder_media_id[$vv],
                                        'remove_for_magento' => '2',
                                        'added_on_cron_compactview' => '1',
                                        'lable' => 1
                                    ];
                                    $this->getInsertDataTable($data_image_data);
                                    if (is_array($item_old_value)) {
                                        if (count($item_old_value) > 0) {
                                            foreach ($item_old_value as $kv => $img) {
                                                if ($img['item_type'] == "IMAGE") {
                                                    /* here changes by me but not tested */
                                                    if ($new_magento_role_option_array[$vv] != "###") {
                                                        $new_mg_role_array = (array)$new_magento_role_option_array[$vv];
                                                        if (count($img["image_role"])>0 && count($new_mg_role_array)>0) {
                                                            $result_val=array_diff($img["image_role"], $new_mg_role_array);
                                                            $item_old_value[$kv]["image_role"] = $result_val;
                                                        }
                                                    }
                                                }
                                            }
                                        }
                                    }
                                    $total_new_value = count($diff_image_detail);
                                    if ($total_new_value > 1) {
                                        foreach ($diff_image_detail as $nn => $n_img) {
                                            if ($n_img['item_type'] == "IMAGE" && $nn != ($total_new_value - 1)) {
                                                if ($new_magento_role_option_array[$vv] != "###") {
                                                    $new_mg_role_array = (array)$new_magento_role_option_array[$vv];
                                                    if (count($n_img["image_role"]) > 0 && count($new_mg_role_array) > 0) {
                                                        $result_val=array_diff($n_img["image_role"], $new_mg_role_array);
                                                        $diff_image_detail[$nn]["image_role"] = $result_val;
                                                    }
                                                }
                                            }
                                        }
                                    }
                                }
                            } else {
                                $item_url = explode("@@", $new_image_value);
                                if (!empty($new_image_value)) {
									$is_order = isset($isOrder[$vv]) ? $isOrder[$vv] : "";
                                    $video_detail[] = [
                                        "item_url" => $item_url[0],
                                        "image_role" => null,
                                        "item_type" => 'VIDEO',
                                        "thum_url" => $item_url[1],
                                        "bynder_md_id" => $bynder_media_id[$vv],
                                        "hash_id" => $hashId[$vv],
										"is_order" => empty($is_order) ? "100" : $is_order
                                    ];
                                    if (!in_array($item_url[0], $all_video_url)) {
                                        $is_order = isset($isOrder[$vv]) ? $isOrder[$vv] : "";
                                        $video_detail_diff[] = [
                                            "item_url" => $item_url[0],
                                            "image_role" => null,
                                            "item_type" => 'VIDEO',
                                            "thum_url" => $item_url[1],
                                            "bynder_md_id" => $bynder_media_id[$vv],
                                            "hash_id" => $hashId[$vv],
											"is_order" => empty($is_order) ? "100" : $is_order
                                        ];
                                        $data_video_data = [
                                            'sku' => $product_sku_key,
                                            'message' => $item_url[0],
                                            'data_type' => '3',
                                            'media_id' => $bynder_media_id[$vv],
                                            'remove_for_magento' => '2',
                                            'added_on_cron_compactview' => '1',
                                            'lable' => 1
                                        ];
                                        $this->getInsertDataTable($data_video_data);
                                    }
                                }
                            }
                        }
                    }
					
                    $merge_both = array_merge($image_detail, $video_detail);
                    $merge_both_diff = array_merge($diff_image_detail, $video_detail_diff);
                    $d_img_roll = "";
                    $d_media_id = [];
					$image_dataa = [];
					$images = [];
                    if (count($merge_both_diff) > 0) {
                        foreach ($merge_both_diff as $d_img) {
                            if($d_img['item_type'] == "IMAGE") {
                                $d_img_roll = $d_img['image_role'];
                            }
                            $d_media_id[] =  $d_img['bynder_md_id'];
                        }
						foreach ($merge_both as $img) {
							if ($img['item_type'] == "IMAGE") {
								$image_dataa[] = $img;
								$images[] = $img['item_url'];
							} else {
								$video_data[] = $img;
								$video[] = $img['item_url'];
							}
						}
                        $this->getInsertMedaiDataTable($product_sku_key, $d_media_id, $product_ids, $storeId);
						$new_image_details = [];
						$new_image_detail_videos = [];
						if (is_array($item_old_value)) {
							foreach ($item_old_value as $img) {
								if ($img['item_type'] == 'IMAGE') {
									$item_img_url = $img['item_url'];
								
									if (in_array($item_img_url, $images)) {
										$item_key = array_search($img['item_url'], array_column($image_dataa, "item_url"));
										$new_image_details[] = [
											"item_url" => $item_img_url,
											"alt_text" => $img['alt_text'],
											"image_role" => $image_detail[$item_key]['image_role'],
											"item_type" => $img['item_type'],
											"thum_url" => $img['thum_url'],
											"bynder_md_id" => $img['bynder_md_id'],
											"hash_id" => $img['hash_id'],
											"is_import" => $img['is_import'],
											"is_order" => $img['is_order'],
										];
									} 
								} 
								if ($img['item_type'] == 'VIDEO') {
									$item_video_url = $img['item_url'];
									if (in_array($item_video_url, $video)) {
										$new_image_detail_videos[] = [
											"item_url" => $img['item_url'],
											"image_role" => $img['image_role'],
											"item_type" => $img['item_type'],
											"thum_url" => $img['thum_url'],
											"bynder_md_id" => $img['bynder_md_id'],
											"hash_id" => $img['hash_id'],
											"is_order" => $img['is_order'],
										];
									}
								}
							}
						}
						
						if (!empty($new_image_details) && !empty($new_image_detail_videos)) {
							$new_data = array_merge($new_image_details, $new_image_detail_videos);
							$array_merge = array_merge($new_data, $merge_both_diff);
						} else {
							$array_merge = array_merge($item_old_value, $merge_both_diff);
						}
                        
                    } else {
                        $new_image_detail = [];
                        $new_image_detail_image = [];
                        $new_image_detail_video = [];
                        $image = [];
                        $video = [];
                        $image_data = [];
                        $video_data = [];
                        if (count($merge_both) > 0) {
                            foreach ($merge_both as $img) {
                                if ($img['item_type'] == "IMAGE") {
                                    $image_data[] = $img; // Store the full data
                                    $image[] = $img['item_url'];
                                } else {
                                    $video_data[] = $img; // Store the full data
                                    $video[] = $img['item_url'];
                                }
                            }
                            if (is_array($item_old_value)) {
                                if (is_array($all_video_url) && count($all_video_url) == 0 && count($all_item_url) > 0) {
                                    foreach ($item_old_value as $img) {
                                        if ($img['item_type'] == 'IMAGE') {
                                            $item_img_url = $img['item_url'];
                                        }
                                        if (in_array($item_img_url, $image)) {
                                            $item_key = array_search($img['item_url'], array_column($image_data, "item_url"));
                                            $new_image_detail[] = [
                                                "item_url" => $item_img_url,
                                                "alt_text" => $img['alt_text'],
                                                "image_role" => $image_detail[$item_key]['image_role'],
                                                "item_type" => $img['item_type'],
                                                "thum_url" => $img['thum_url'],
                                                "bynder_md_id" => $img['bynder_md_id'],
                                                "hash_id" => $img['hash_id'],
                                                "is_import" => $img['is_import'],
												"is_order" => $img['is_order'],
                                            ];
                                        }
                                    }
                                    if(!empty($new_image_detail)) {
                                        $array_merge = array_merge($new_image_detail, $video_data);
                                    } else {
                                        $array_merge = array_merge($item_old_value, $video_data);
                                    }
                                } elseif (count($all_video_url) > 0 && count($all_item_url) == 0) {
                                    foreach ($item_old_value as $img) {
                                        if ($img['item_type'] == 'VIDEO') {
                                            $item_video_url = $img['item_url'];
                                        }
                                        if (in_array($item_video_url, $video)) {
                                            $new_image_detail_video[] = [
                                                "item_url" => $item_video_url,
                                                "image_role" => $img['image_role'],
                                                "item_type" => $img['item_type'],
                                                "thum_url" => $img['thum_url'],
                                                "bynder_md_id" => $img['bynder_md_id'],
                                                "hash_id" => $img['hash_id'],
												"is_order" => $img['is_order'],
                                            ];
                                        }
                                    }
                                    if(!empty($new_image_detail_video)) {
                                        $array_merge = array_merge($new_image_detail_video, $image_data);
                                    } else {
                                        $array_merge = array_merge($item_old_value, $image_data);
                                    }
                                } elseif (count($all_video_url) > 0 && count($all_item_url) > 0) {
                                    foreach ($item_old_value as $img) {
                                        if ($img['item_type'] == 'IMAGE') {
                                            $item_img_url = $img['item_url'];
                                            if (in_array($item_img_url, $image)) {
                                                $item_key = array_search($img['item_url'], array_column($image_data, "item_url"));
                                                
                                                $new_image_detail_image[] = [
                                                    "item_url" => $item_img_url,
                                                    "alt_text" => $img['alt_text'],
                                                    "image_role" => $image_detail[$item_key]['image_role'],
                                                    "item_type" => $img['item_type'],
                                                    "thum_url" => $img['thum_url'],
                                                    "bynder_md_id" => $img['bynder_md_id'],
                                                    "hash_id" => $img['hash_id'],
                                                    "is_import" => $img['is_import'],
													"is_order" => $img['is_order'],
                                                ];
                                            }
                                        }
										if ($img['item_type'] == 'VIDEO') {
                                            $item_video_url = $img['item_url'];
                                            if (in_array($item_video_url, $video)) {
                                                $new_image_detail_video[] = [
                                                    "item_url" => $img['item_url'],
                                                    "image_role" => $img['image_role'],
                                                    "item_type" => $img['item_type'],
                                                    "thum_url" => $img['thum_url'],
                                                    "bynder_md_id" => $img['bynder_md_id'],
                                                    "hash_id" => $img['hash_id'],
													"is_order" => $img['is_order'],
                                                ];
                                            }
                                        }
                                    }
                                    if (!empty($new_image_detail_image) && !empty($new_image_detail_video)) {
                                        $array_merge = array_merge($new_image_detail_image, $new_image_detail_video);
                                    } else {
                                        $array_merge = array_merge($item_old_value, $merge_both);
                                    }
                                }
                            }
                        }
                    }
                    $m_id = [];
                    foreach ($array_merge as $img) {
                        $type[] = $img['item_type'];
                        $m_id[] = $img['bynder_md_id'];
                        $this->getDeleteMedaiDataTable($product_sku_key, $img['bynder_md_id']);
                    }
                    $this->getInsertMedaiDataTable($product_sku_key, $m_id, $product_ids, $storeId);
                    
                    $flag = 0;
                    if (in_array("IMAGE", $type) && in_array("VIDEO", $type)) {
                        $flag = 1;
                    } elseif (in_array("IMAGE", $type)) {
                        $flag = 2;
                    } elseif (in_array("VIDEO", $type)) {
                        $flag = 3;
                    }
                    $by_extra_details = array();
                    $by_extra_details_video = array();
                    $isImage = true;
                    if(isset($bynder_extra_data["extra_details"]["assets_extra_details"])){
                        $by_extra_details = $bynder_extra_data["extra_details"]["assets_extra_details"];
                    }
                    if(isset($bynder_extra_data_video["extra_details"]["assets_extra_details"])){
                        $by_extra_details_video = $bynder_extra_data_video["extra_details"]["assets_extra_details"];
                    }
                    $both_merge_extra_detail = array_merge($by_extra_details, $by_extra_details_video);
					if(count($old_asset_detail_array) > 0) {
						$new_asset_detail = array_merge($old_asset_detail_array, $both_merge_extra_detail);
					}
					$update_latest_code = [
						"asset_list" => $array_merge,
                        "assets_extra_details" => $new_asset_detail
					];
                    $new_value_array = json_encode($update_latest_code, true);

                    $updated_values = [
                        'bynder_multi_img' => $new_value_array,
                        'bynder_isMain' => $flag,
                        'bynder_auto_replace' => 1,
						'use_bynder_cdn' => 1
                    ];
                    $this->action->updateAttributes(
                        [$product_ids],
                        $updated_values,
                        $storeId
                    );
                    /*
                    $this->action->updateAttributes(
                        [$product_ids],
                        ['bynder_isMain' => $flag],
                        $storeId
                    );
                    */
                }
            } 
        } catch (Exception $e) {
            $insert_data = [
                "sku" => $product_sku_key,
                "message" => $e->getMessage(),
                'media_id' => "",
                "data_type" => ""
            ];
            //$this->getInsertDataTable($insert_data);
        }
    }

     /**
     * Upate Item
     *
     * @return $this
     * @param string $img_json
     * @param string $product_sku_key
     * @param string $mg_img_role_option
     * @param string $img_alt_text
     * @param string $bynder_media_id
     */
    public function getUpdateDoc(
		$img_json,
		$product_sku_key,
		$mg_role,
		$image_alt_text_value,
		$bynder_media_id_value,
		$bynder_extra_data,
		$byd_hash_id,
		$byd_type,
		$byd_media_is_order
	)
    {

        $select_attribute = "image";
        //$model = $this->_byndersycData->create();
        $image_detail = [];
        $diff_image_detail = [];
        try {
            $storeId = $this->storeManagerInterface->getStore()->getId();
            /*
            $byndeimageconfig = $this->datahelper->byndeimageconfig();
            $img_roles = explode(",", $byndeimageconfig);*/
            $_product = $this->_productRepository->get($product_sku_key);
            $product_ids = $_product->getId();
            $doc_values = $_product->getBynderDocument();
            $b_id = [];
            $all_item_url = [];
			$img_type = explode(",", $byd_type);
			$img_type = array_unique($img_type);
			if (in_array("document", $img_type)) {
                if (!empty($doc_values)) {
                    $item_old_value = json_decode($doc_values, true);
                    $item_old_value = $item_old_value["asset_list"];
                    if (is_array($item_old_value)) {
						if (count($item_old_value) > 0) {
							foreach ($item_old_value as $doc) {
                                if ($doc['item_type'] == 'DOCUMENT') {
                                    $all_item_url[] = $doc['item_url'];
                                    $b_id[] = $doc['bynder_md_id'];
                                }
                            }
						}
                    }
                    $new_doc_array = explode("\n", $img_json);
					$bynder_media_id = explode("\n", $bynder_media_id_value);
					$hashId = explode("\n", $byd_hash_id);
					$isOrder = explode("\n", $byd_media_is_order);
                    $doc_detail = [];
                    foreach ($new_doc_array as $vv => $doc_value) {
                        if(!empty($doc_value)){
							$is_order = isset($isOrder[$vv]) ? $isOrder[$vv] : "";
                            $item_url = explode("?", $doc_value);
                            $doc_name = explode("@@", $doc_value);
                            $media_doc_explode = explode("/", $item_url[0]);
                            if(!in_array($bynder_media_id[$vv], $b_id)) {
                                $doc_detail[] = [
                                    "item_url" => $doc_name[0],
                                    "item_type" => 'DOCUMENT',
                                    "doc_name" => $doc_name[1],
                                    "bynder_md_id" => $bynder_media_id[$vv],
                                    "hash_id" => $hashId[$vv],
									"is_order" => empty($is_order) ? "100" : $is_order
                                ];
                                $data_doc_value = [
                                    'sku' => $product_sku_key,
                                    'message' => $doc_name[0],
                                    'data_type' => '2',
                                    'media_id' => $bynder_media_id[$vv],
                                    'remove_for_magento' => '2',
                                    'added_on_cron_compactview' => '1',
                                    'lable' => 1
                                ];
                                $this->getInsertDataTable($data_doc_value);
                            }
                            
                        }
                    }
                    $array_merg = array_merge($item_old_value, $doc_detail);
                    $by_extra_details = array();
                    if(isset($bynder_extra_data["extra_details"]["assets_extra_details"])){
                        $by_extra_details = $bynder_extra_data["extra_details"]["assets_extra_details"];
                    }
					$update_latest_code = [
						"asset_list" => $array_merg,
                        "assets_extra_details" => $by_extra_details
					];
                    $new_value_array = json_encode($update_latest_code, true);
                    $this->action->updateAttributes(
                        [$product_ids],
                        ['bynder_document' => $new_value_array, 'bynder_cron_sync' => 1],
                        $storeId
                    );
                }
            }
        } catch (Exception $e) {
            $insert_data = [
                "sku" => $product_sku_key,
                "message" => $e->getMessage(),
                "data_type" => "",
                'media_id' => "",
                'remove_for_magento' => '',
                'added_on_cron_compactview' => '',
                "lable" => "0"
            ];
            //$this->getInsertDataTable($insert_data);
        }
    }

    /**
     * Update Bynder cron sync status
     *
     * @param string $sku
     */
    public function updateBynderCronSync($sku)
    {
        $updated_values = [
            'bynder_cron_sync' => 2
        ];

        $storeId = $this->getMyStoreId();
        $_product = $this->_productRepository->get($sku);
        $product_ids = $_product->getId();

        $this->action->updateAttributes(
            [$product_ids],
            $updated_values,
            $storeId
        );
    }
}
