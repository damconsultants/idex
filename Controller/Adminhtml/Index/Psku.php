<?php

namespace DamConsultants\Idex\Controller\Adminhtml\Index;

use DamConsultants\Idex\Model\ResourceModel\Collection\MetaPropertyCollectionFactory;
use DamConsultants\Idex\Model\ResourceModel\Collection\BynderMediaTableCollectionFactory;

class Psku extends \Magento\Backend\App\Action
{
    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $resultPageFactory = false;
    /**
     * @var $resultJsonFactory
     */
    protected $resultJsonFactory;
    /**
     * @var $productAction
     */
    protected $productAction;
    /**
     * @var $storeManagerInterface
     */
    protected $storeManagerInterface;
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
     * @var $datahelper
     */
    protected $datahelper;
    /**
     * @var $_byndersycData
     */
    protected $_byndersycData;
    /**
     * @var $_productRepository
     */
    protected $_productRepository;
    /**
     * @var $product
     */
    protected $product;

    /**
     * Product Sku.
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Catalog\Model\Product\Action $action
     * @param \Magento\Store\Model\StoreManagerInterface $storeManagerInterface
     * @param \DamConsultants\Idex\Model\BynderConfigSyncDataFactory $byndersycData
     * @param \DamConsultants\Idex\Model\BynderMediaTableFactory $bynderMediaTable
     * @param BynderMediaTableCollectionFactory $bynderMediaTableCollectionFactory
     * @param \Magento\Catalog\Model\Product $product
     * @param \Magento\Catalog\Model\ProductRepository $productRepository
     * @param MetaPropertyCollectionFactory $metaPropertyCollectionFactory
     * @param \DamConsultants\Idex\Helper\Data $DataHelper
     * @param \Magento\Framework\Controller\Result\JsonFactory $jsonFactory
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Catalog\Model\Product\Action $action,
        \Magento\Store\Model\StoreManagerInterface $storeManagerInterface,
        \DamConsultants\Idex\Model\BynderConfigSyncDataFactory $byndersycData,
        \DamConsultants\Idex\Model\BynderMediaTableFactory $bynderMediaTable,
        BynderMediaTableCollectionFactory $bynderMediaTableCollectionFactory,
        \Magento\Catalog\Model\Product $product,
        \Magento\Catalog\Model\ProductRepository $productRepository,
        MetaPropertyCollectionFactory $metaPropertyCollectionFactory,
        \DamConsultants\Idex\Helper\Data $DataHelper,
        \Magento\Framework\Controller\Result\JsonFactory $jsonFactory
    ) {
        parent::__construct($context);
        $this->resultJsonFactory = $jsonFactory;
        $this->productAction = $action;
        $this->storeManagerInterface = $storeManagerInterface;
        $this->metaPropertyCollectionFactory = $metaPropertyCollectionFactory;
        $this->bynderMediaTable = $bynderMediaTable;
        $this->bynderMediaTableCollectionFactory = $bynderMediaTableCollectionFactory;
        $this->datahelper = $DataHelper;
        $this->_byndersycData = $byndersycData;
        $this->_productRepository = $productRepository;
        $this->product = $product;
    }
    /**
     * Execute
     *
     * @return $this
     */
    public function execute()
    {
        if (!$this->getRequest()->isAjax()) {
            $this->_forward('noroute');
            return '';
        }

        $property_id = null;
        $product_sku = $this->getRequest()->getParam('product_sku');
        $select_attribute = $this->getRequest()->getParam('select_attribute');
        $result = $this->resultJsonFactory->create();
       
        $collection = $this->metaPropertyCollectionFactory->create()->getData();
        $meta_properties = $this->getMetaPropertiesCollection($collection);

        $collection_value = $meta_properties['collection_data_value'];
        $collection_slug_val = $meta_properties['collection_data_slug_val'];

        if (strlen($product_sku) > 0) {
            $productSku = explode(",", trim($product_sku));
            if (count($productSku) > 0) {
                foreach ($productSku as $sku) {
                    if ($sku != "") {
                        $bd_sku = trim(preg_replace('/[^A-Za-z0-9]-/', '_', $sku));
                        $get_data = $this->datahelper->getImageSyncWithProperties(
                            $bd_sku,
                            $property_id,
                            $collection_value
                        );
                        $getIsJson = $this->getIsJSON($get_data);
                        if (!empty($get_data) && $getIsJson) {
                            $respon_array = json_decode($get_data, true);
                            if ($respon_array['status'] == 1) {
                                $convert_array = json_decode($respon_array['data'], true);
								
                                if ($convert_array['status'] == 1) {
                                    $current_sku = $sku;
                                    try {
                                        $this->getDataItem(
                                            $select_attribute,
                                            $convert_array,
                                            $collection_slug_val,
                                            $current_sku
                                        );
                                    } catch (Exception $e) {
                                        $insert_data = [
                                            "sku" => $sku,
                                            "message" => $e->getMessage(),
                                            "data_type" => "",
                                            "lable" => "0"
                                        ];
                                        $this->getInsertDataTable($insert_data);
                                    }
                                    
                                } else {
                                    $insert_data = [
                                        "sku" => $sku,
                                        "message" => $convert_array['data'],
                                        "data_type" => "",
                                        "lable" => "0"
                                    ];
                                    $this->getInsertDataTable($insert_data);
                                }
                            } else {
                                $insert_data = [
                                "sku" => $sku,
                                "message" => 'Please Select The Metaproperty First.....',
                                "data_type" => "",
                                "lable" => "0"
                                ];
                                $this->getInsertDataTable($insert_data);
                                $result_data = $result->setData(
                                    ['status' => 0, 'message' => 'Please check Bynder Synchronization. Action Log.....']
                                );
                                return $result_data;
                            }
                        } else {
                            $result_data = $result->setData(
                                [
                                'status' => 0,
                                'message' => 'Something went wrong from API side, Please contact to support team!'
                                              ]
                            );
                            return $result_data;
                        }
                    }
                }
            }
            $result_data = $result->setData([
                'status' => 1,
                'message' => 'Data Sync Successfully.Please check Bynder Synchronization Log.!'
            ]);
            return $result_data;
        } else {
            $result_data = $result->setData(['status' => 0, 'message' => 'Please enter atleast one SKU.']);
            return $result_data;
        }
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
            'bynder_sync_data' => $insert_data['message'],
            'bynder_data_type' => $insert_data['data_type'],
            'lable' => $insert_data['lable']
        ];
        $model->setData($data_image_data);
        $model->save();
    }
    /**
     * Is Json
     *
     * @param string $sku
     * @param string $m_id
     * @param string $product_ids
     * @param string $storeId
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
            $new_m_id = trim($new_data);
            $data_image_data = [
                'sku' => $sku,
                'media_id' => $new_m_id,
                'status' => "1",
            ];
            $model->setData($data_image_data);
            $model->save();
        }
        $updated_values = [
            'bynder_delete_cron' => 1
        ];
        $this->productAction->updateAttributes(
            [$product_ids],
            $updated_values,
            $storeId
        );
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
        $model = $this->bynderMediaTableCollectionFactory->create();
        $model->addFieldToFilter('sku', ['eq' => [$sku]])->load();
        foreach ($model as $mdata) {
            if ($mdata['media_id'] != $media_id) {
                $this->bynderMediaTable->create()->load($mdata['id'])->delete();

            }
        }
    }
    /**
     * Get Data Item
     *
     * @param string $select_attribute
     * @param array $convert_array
     * @param array $collection_data_slug_val
     * @param array $current_sku
     * @return this
     */
    public function getDataItem($select_attribute, $convert_array, $collection_data_slug_val, $current_sku)
    {
        $data_arr = [];
        $data_val_arr = [];
        $result = $this->resultJsonFactory->create();
        if ($convert_array['status'] == 1) {
            foreach ($convert_array['data'] as $data_value) {
                if ($select_attribute == $data_value['type']) {
                    $bynder_media_id = $data_value['id'];
                    $image_data = $data_value['thumbnails'];
                    $bynder_image_role = $image_data['magento_role_options'];
                    $bynder_alt_text = $image_data['img_alt_text'];
                    $sku_slug_name = "property_" . $collection_data_slug_val['sku']['bynder_property_slug'];
                    /*$data_sku = $data_value[$sku_slug_name];*/
                    $data_sku[0] = $current_sku;
                    /*Below code for multiple derivative according to image roll */
                    $images_urls_list = [];
                    $new_magento_role_list = [];
                    $new_bynder_alt_text =[];
                    $is_order = [];
                    $new_bynder_mediaid_text = [];
                    if (count($bynder_image_role) > 0) {
                        foreach ($bynder_image_role as $m_bynder_role) {
                            $lower_m_bynder_role = strtolower($m_bynder_role);
                            if ($m_bynder_role == "Catalog_Tile") {
                                $original_m_bynder_role = "Base";
                            }
                            if ($m_bynder_role == "Featured") {
                                $original_m_bynder_role = "Small";
                            }
                            if ($m_bynder_role == "Product_Page") {
                                $original_m_bynder_role = "Swatch";
                            }
                            if ($m_bynder_role == "Thumbnail") {
                                $original_m_bynder_role = "Thumbnail";
                            }
                            //$original_m_bynder_role = $m_bynder_role;
                            if (isset($data_value["thumbnails"][$original_m_bynder_role])) {
                                $images_urls_list[]= $data_value["thumbnails"][$original_m_bynder_role]."\n";
                                $new_magento_role_list[] = $original_m_bynder_role."\n";

                                $alt_text_vl = $data_value["thumbnails"]["img_alt_text"];
                                if (is_array($data_value["thumbnails"]["img_alt_text"])) {
                                    $alt_text_vl = implode(" ", $data_value["thumbnails"]["img_alt_text"]);
                                }
                                $new_bynder_alt_text[] = (strlen($alt_text_vl) > 0)?$alt_text_vl."\n":"###\n";
                                $magento_order_slug = "property_" . $collection_data_slug_val['image_order']['bynder_property_slug'];
                                if(isset($data_value[$magento_order_slug])) {
                                    foreach ($data_value[$magento_order_slug]  as $property_Magento_Media_Order) {
                                        $is_order[] = $property_Magento_Media_Order . "\n";
                                    }
                                }
                            } else {
                                if(isset($data_value["thumbnails"]["JPG"])){
                                    $images_urls_list[]= $data_value["thumbnails"]["JPG"]."\n";
                                }else{
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
                            $magento_order_slug = "property_" . $collection_data_slug_val['image_order']['bynder_property_slug'];
                            if(isset($data_value[$magento_order_slug])) {
                                foreach ($data_value[$magento_order_slug]  as $property_Magento_Media_Order) {
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
                    }
                    if (count($images_urls_list) == 0) {
                        if (isset($image_data["JPG"])) {
                            $images_urls_list[] = $image_data["JPG"]."\n";
                        } elseif (isset($data_value["thumbnails"]["webimage"])) {
                            $images_urls_list[] = $data_value["thumbnails"]["webimage"] . "\n";
                        } else {
                            $images_urls_list[] = "no image" . "\n";
                        }
                    }
                    if ($data_value['type'] == "image") {
                        array_push($data_arr, $data_sku[0]);
                        $data_p = [
                            "sku" => $data_sku[0],
                            "url" => $images_urls_list, /* chagne by kuldip ladola for testing perpose */
                            'magento_image_role' => $new_magento_role_list,
                            'image_alt_text' => $new_bynder_alt_text,
                            'bynder_media_id_new' => $new_bynder_mediaid_text,
							'is_order' => $is_order
                        ];
                        array_push($data_val_arr, $data_p);
                    } else {
                        if ($select_attribute == 'video') {
                            $video_link[] = $data_value["videoPreviewURLs"][0] . '@@' . $image_data["webimage"];
                            array_push($data_arr, $data_sku[0]);
                            //$data_p = ["sku" => $data_sku[0], "url" => $video_link];
							$data_p = [
								"sku" => $data_sku[0],
								"url" => $video_link, /* chagne by kuldip ladola for testing perpose */
								'magento_image_role' => $new_magento_role_list,
								'image_alt_text' => $new_bynder_alt_text,
								'bynder_media_id_new' => $new_bynder_mediaid_text,
								'is_order' => $is_order
							];
                            array_push($data_val_arr, $data_p);

                        } else {
                            $doc_name = $data_value["name"];
                            $doc_name_with_space = preg_replace("/[^a-zA-Z]+/", "-", $doc_name);
                            $doc_link = $image_data["image_link"] . '@@' . $doc_name_with_space . "\n";
                            array_push($data_arr, $data_sku[0]);
                            //$data_p = ["sku" => $data_sku[0], "url" => $doc_link];
                            $data_p = [
                                "sku" => $data_sku[0],
                                "url" => [$doc_link], /* chagne by kuldip ladola for testing perpose */
                                'magento_image_role' => $new_magento_role_list,
                                'image_alt_text' => $new_bynder_alt_text,
                                'bynder_media_id_new' => $new_bynder_mediaid_text,
                                'is_order' => $is_order
                            ];
                            array_push($data_val_arr, $data_p);
                        }

                    }
                }
            }
        }
        if (count($data_arr) > 0) {
            $this->getProcessItem($data_arr, $data_val_arr);
        } else {
            $result_data = $result->setData(['status' => 0, 'message' => 'No Data Found...']);
            return $result_data;
        }
    }
    /**
     * Get Process Item
     *
     * @param array $data_arr
     * @param array $data_val_arr
     * @return $this
     */
    public function getProcessItem($data_arr, $data_val_arr)
    {
        $result = $this->resultJsonFactory->create();
        $image_value_details_role = [];
        $temp_arr = [];
        $byn_is_order = [];
        //echo "<pre>"; print_r($data_val_arr); exit;
        foreach ($data_arr as $key => $skus) {
            $temp_arr[$skus][] = implode("", $data_val_arr[$key]["url"]);
            $image_value_details_role[$skus][] = implode("", $data_val_arr[$key]["magento_image_role"]);
            $image_alt_text[$skus][] = implode("", $data_val_arr[$key]["image_alt_text"]);
            $byn_md_id_new[$skus][] = implode("", $data_val_arr[$key]["bynder_media_id_new"]);
			$byn_is_order[$skus][] = implode("", $data_val_arr[$key]["is_order"]);
        }
        foreach ($temp_arr as $product_sku_key => $image_value) {
            
            $img_json = implode("", $image_value);
            $mg_role = implode("", $image_value_details_role[$product_sku_key]);
            $image_alt_text_value = implode("", $image_alt_text[$product_sku_key]);
            $byd_media_id_value = implode("", $byn_md_id_new[$product_sku_key]);
            $byd_media_is_order = implode("", $byn_is_order[$product_sku_key]);
            $this->getUpdateImage(
                $img_json,
                $product_sku_key,
                $mg_role,
                $image_alt_text_value,
                $byd_media_id_value,
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
	 * @param string $byd_media_is_order
     */
    public function getUpdateImage($img_json, $product_sku_key, $mg_img_role_option, $img_alt_text, $bynder_media_ids, $byd_media_is_order)
    {
        $result = $this->resultJsonFactory->create();
        $select_attribute = $this->getRequest()->getParam('select_attribute');
        $image_detail = [];
        $diff_image_detail = [];
        try {
            
            $storeId = $this->storeManagerInterface->getStore()->getId();
            
            /*
            $byndeimageconfig = $this->datahelper->byndeimageconfig();
            $img_roles = explode(",", $byndeimageconfig);*/

            $_product = $this->_productRepository->get($product_sku_key);
            
            $product_ids = $_product->getId();
            
            $image_value = $_product->getBynderMultiImg();
            
            $doc_value = $_product->getBynderDocument();
            $bynder_media_id = explode("\n", $bynder_media_ids);
            $isOrder = explode("\n", $byd_media_is_order);
            if ($select_attribute == "image") {
                if (!empty($image_value)) {
                    $new_image_array = explode("\n", $img_json);
                    //$bynder_media_id = explode("\n", $bynder_media_ids);
                    $new_alttext_array = explode("\n", $img_alt_text);
                    $new_magento_role_option_array = explode("\n", $mg_img_role_option);
                    $all_item_url = [];
                    $all_video_url = [];
                    $item_old_value = json_decode($image_value, true);
					if (is_array($item_old_value)) {
						if (count($item_old_value) > 0) {
							foreach ($item_old_value as $img) {
                                if ($img['item_type'] == 'IMAGE') {
                                    $all_item_url[] = $img['thum_url'];
                                } else {
                                    $all_video_url[] = $img['item_url'];
                                }
                            }
						}
                    }
                    foreach ($new_image_array as $vv => $new_image_value) {
                        if (trim($new_image_value) != "" && $new_image_value != "no image") {
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
                                "is_import" => 0,
								"is_order" => [$is_order]
                            ];
                            if (!in_array($item_url[0], $all_item_url)) {
                                $is_order = isset($isOrder[$vv]) ? $isOrder[$vv] : "";
                                $diff_image_detail[] = [
                                    "item_url" => $new_image_value,
                                    "alt_text" => $img_altText_val,
                                    "image_role" => $curt_img_role,
                                    "item_type" => 'IMAGE',
                                    "thum_url" => $item_url[0],
                                    "bynder_md_id" => $bynder_media_id[$vv],
                                    "is_import" => 0,
									"is_order" => [$is_order]
                                ];
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
                        }
                    }
                    $d_img_roll = "";
                    $d_media_id = [];
                    if (count($diff_image_detail) > 0) {
                        foreach ($diff_image_detail as $d_img) {
                            $d_img_roll = $d_img['image_role'];
                            $d_media_id[] =  $d_img['bynder_md_id'];
                        }
                        $this->getInsertMedaiDataTable($product_sku_key, $d_media_id, $product_ids, $storeId);
                    }
                    $new_image_detail = [];
                    $new_image_detail_image = [];
                    $new_image_detail_video = [];
                    if (count($image_detail) > 0) {
                        foreach ($image_detail as $img) {
                            $image[] = $img['item_url'];
                        }
						if (is_array($item_old_value)) {
                            if (is_array($all_video_url) && count($all_video_url) == 0 && count($all_item_url) > 0) {
                                foreach ($item_old_value as $img) {
                                    if ($img['item_type'] == 'IMAGE') {
                                        $item_img_url = $img['item_url'];
                                    }
                                    if (in_array($item_img_url, $image)) {
                                        $item_key = array_search($img['item_url'], array_column($image_detail, "item_url"));
                                        $new_image_detail[] = [
                                            "item_url" => $item_img_url,
                                            "alt_text" => $image_detail[$item_key]['alt_text'],
                                            "image_role" => $image_detail[$item_key]['image_role'],
                                            "item_type" => $img['item_type'],
                                            "thum_url" => $img['thum_url'],
                                            "bynder_md_id" => $img['bynder_md_id'],
                                            "is_import" => $img['is_import'],
                                            "is_order" => $image_detail[$item_key]['is_order'],
                                        ];
                                    }
                                }
                            } elseif (count($all_video_url) > 0 && count($all_item_url) == 0) {
                                $new_image_detail = array_merge($image_detail, $item_old_value);
                            } elseif (count($all_video_url) > 0 && count($all_item_url) > 0) {
                                foreach ($item_old_value as $img) {
                                    $item_img_url = $img['item_url'];
                                    if (in_array($item_img_url, $image)) {
                                        $item_key = array_search($img['item_url'], array_column($image_detail, "item_url"));
                                        $new_image_detail_image[] = [
                                            "item_url" => $item_img_url,
                                            "alt_text" => $image_detail[$item_key]['alt_text'],
                                            "image_role" => $image_detail[$item_key]['image_role'],
                                            "item_type" => $img['item_type'],
                                            "thum_url" => $img['thum_url'],
                                            "bynder_md_id" => $img['bynder_md_id'],
                                            "is_import" => $img['is_import'],
                                            "is_order" => $image_detail[$item_key]['is_order'],
                                        ];
                                    }
                                    if ($img['item_type'] == 'VIDEO') {
                                        $new_image_detail_video[] = [
                                            "item_url" => $img['item_url'],
                                            "alt_text" => $img['alt_text'],
                                            "image_role" => $img['image_role'],
                                            "item_type" => $img['item_type'],
                                            "thum_url" => $img['thum_url'],
                                            "bynder_md_id" => $img['bynder_md_id'],
                                            "is_import" => $img['is_import'],
                                            "is_order" => $img['is_order'],
                                        ];
                                    }
                                }
                                $new_image_detail = array_merge($new_image_detail_image, $new_image_detail_video);
                            }
						}
                    }
                    $array_merge = array_merge($new_image_detail, $diff_image_detail);
                    $media_id = [];
                    foreach ($array_merge as $img) {
                        $type[] = $img['item_type'];
                        $image[] = $img['item_url'];
                        $media_id[] = $img['bynder_md_id'];
                        $this->getDeleteMedaiDataTable($product_sku_key, $img['bynder_md_id']);
                    }
                    $this->getInsertMedaiDataTable($product_sku_key, $media_id, $product_ids, $storeId);
                    $image_value_array = implode(',', $image);
                    $flag = 0;
                    if (in_array("IMAGE", $type) && in_array("VIDEO", $type)) {
                        $flag = 1;
                    } elseif (in_array("IMAGE", $type)) {
                        $flag = 2;
                    } elseif (in_array("VIDEO", $type)) {
                        $flag = 3;
                    }
                    $new_value_array = json_encode($array_merge, true);
                    $data_image_data = [
                        'sku' => $product_sku_key,
                        'message' => $image_value_array,
                        'data_type' => '1',
                        "lable" => "1"
                    ];
                    $this->getInsertDataTable($data_image_data);
                    $updated_values = [
                        'bynder_multi_img' => $new_value_array,
                        'bynder_isMain' => $flag
                    ];
                    $this->productAction->updateAttributes(
                        [$product_ids],
                        $updated_values,
                        $storeId
                    );
                    /*
                    $this->productAction->updateAttributes(
                        [$product_ids],
                        ['bynder_isMain' => $flag],
                        $storeId
                    );
                    */
                } else {
                    try {
                        $new_image_array = explode("\n", $img_json);
                        $new_alttext_array = explode("\n", $img_alt_text);
                        $new_magento_role_option_array = explode("\n", $mg_img_role_option);
                        foreach ($new_image_array as $vv => $image_value) {
                            if (!empty($image_value)) {
                                if (trim($image_value) != "" && $image_value != "no image") {
                                    $item_url = explode("?", $image_value);
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
                                        "item_url" => $image_value,
                                        "alt_text" => $img_altText_val,
                                        "image_role" => $curt_img_role,
                                        "item_type" => 'IMAGE',
                                        "thum_url" => $item_url[0],
                                        "bynder_md_id" => $bynder_media_id[$vv],
                                        "is_import" => 0,
                                        "is_order" => [$is_order]
                                    ];
                                    
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
                            }
                        }
                        $media_id = [];
                        foreach ($image_detail as $img) {
                            $type[] = $img['item_type'];
                            $image[] = $img['item_url'];
                            $m_id[] = $img['bynder_md_id'];
                            $this->getDeleteMedaiDataTable($product_sku_key, $img['bynder_md_id']);
                        }
                        $this->getInsertMedaiDataTable($product_sku_key, $m_id, $product_ids, $storeId);
                        $image_value_array = implode(',', $image);
                        
                        $flag = 0;
                        if (in_array("IMAGE", $type) && in_array("VIDEO", $type)) {
                            $flag = 1;
                        } elseif (in_array("IMAGE", $type)) {
                            $flag = 2;
                        } elseif (in_array("VIDEO", $type)) {
                            $flag = 3;
                        }
                        $data_image_data = [
                            'sku' => $product_sku_key,
                            'message' => $image_value_array,
                            'data_type' => '1',
                            "lable" => "1"
                        ];
                        $this->getInsertDataTable($data_image_data);
                        $new_value_array = json_encode($image_detail, true);
                        
                        $updated_values = [
                            'bynder_multi_img' => $new_value_array,
                            'bynder_isMain' => $flag
                        ];
                        $this->productAction->updateAttributes(
                            [$product_ids],
                            $updated_values,
                            $storeId
                        );
                    } catch (\Throwable $th) {
                       echo "<pre>"; print_r($th->getMessage());
                    }
                    
                    /*
                    $this->productAction->updateAttributes(
                        [$product_ids],
                        ['bynder_isMain' => $flag],
                        $storeId
                    );
                    */
                   
                }
            } elseif ($select_attribute == "video") {
                if (!empty($image_value)) {
                    $new_video_array = explode(" \n", $img_json);
                    $old_value_array = json_decode($image_value, true);
                    $old_item_url = [];
                    $all_video_url = [];
                    $video_detail = [];
                    $video_detail_diff = [];
                    if (!empty($old_value_array)) {
                        foreach ($old_value_array as $value) {
                            if ($value['item_type'] == 'IMAGE') {
                                $old_item_url[] = $value['thum_url'];
                            } else {
                                $all_video_url[] = $value['item_url'];
                            }
                        }
                    }
                    foreach ($new_video_array as $vv => $video_value) {
                        $item_url = explode("@@", $video_value);
                        $thum_url = explode("@@", $video_value);
                        $media_video_explode = explode("/", $item_url[0]);
                        $is_order = isset($isOrder[$vv]) ? $isOrder[$vv] : "";
                        if (!empty($video_value)) {
                            $video_detail[] = [
                                "item_url" => $item_url[0],
                                "image_role" => null,
                                "item_type" => 'VIDEO',
                                "thum_url" => $thum_url[1],
                                "bynder_md_id" => $bynder_media_id[$vv],
                                "is_order" => [$is_order]
                            ];
                            if (!in_array($item_url[0], $all_video_url) && (count($all_video_url) > 0)) {
                                $is_order = isset($isOrder[$vv]) ? $isOrder[$vv] : "";
                                $video_detail_diff[] = [
                                    "item_url" => $item_url[0],
                                    "image_role" => null,
                                    "item_type" => 'VIDEO',
                                    "thum_url" => $thum_url[1],
                                    "bynder_md_id" => $bynder_media_id[$vv],
                                    "is_order" => [$is_order]
                                ];
                            }
                        }
                    }
                    $new_video_array = array_merge($video_detail, $video_detail_diff);
                    if (!empty($old_value_array)) {
                        $array_merge = array_merge($old_value_array, $new_video_array);
                        foreach ($array_merge as $img) {

                            $type[] = $img['item_type'];
                        }
                        $flag = 0;
                        if (in_array("IMAGE", $type) && in_array("VIDEO", $type)) {
                            $flag = 1;
                        } elseif (in_array("IMAGE", $type)) {
                            $flag = 2;
                        } elseif (in_array("VIDEO", $type)) {
                            $flag = 3;
                        }
                    }
                    $new_value_array = json_encode($array_merge, true);
                    $data_video_data = [
                        'sku' => $product_sku_key,
                        'message' => $new_value_array,
                        'data_type' => '3',
                        "lable" => "1"
                    ];
                    $this->getInsertDataTable($data_video_data);
                    $updated_values = [
                        'bynder_multi_img' => $new_value_array,
                        'bynder_isMain' => $flag
                    ];
                    $this->productAction->updateAttributes(
                        [$product_ids],
                        $updated_values,
                        $storeId
                    );
                    /*
                    $this->productAction->updateAttributes(
                        [$product_ids],
                        ['bynder_isMain' => $flag],
                        $storeId
                    );
                    */
                } else {
                    $new_video_array = explode(" \n", $img_json);
                    $video_detail = [];
                    foreach ($new_video_array as $vv => $video_value) {
                        if (!empty($video_value)) {
                            $item_url = explode("@@", $video_value);
                            $thum_url = explode("@@", $video_value);
                            $media_video_explode = explode("/", $item_url[0]);
                            $is_order = isset($isOrder[$vv]) ? $isOrder[$vv] : "";
                            $video_detail[] = [
                                "item_url" => $item_url[0],
                                "image_role" => null,
                                "item_type" => 'VIDEO',
                                "thum_url" => $thum_url[1],
                                "bynder_md_id" => $bynder_media_id[$vv],
                                "is_order" => [$is_order]
                            ];
                        }
                    }
                    foreach ($video_detail as $img) {
                        $type[] = $img['item_type'];
                    }
                    $flag = 0;
                    if (in_array("IMAGE", $type) && in_array("VIDEO", $type)) {
                        $flag = 1;
                    } elseif (in_array("IMAGE", $type)) {
                        $flag = 2;
                    } elseif (in_array("VIDEO", $type)) {
                        $flag = 3;
                    }
                    $new_value_array = json_encode($video_detail, true);
                    $data_video_data = [
                        'sku' => $product_sku_key,
                        'message' => $new_value_array,
                        'data_type' => '3',
                        "lable" => "1"
                    ];
                    $this->getInsertDataTable($data_video_data);
                    $updated_values = [
                        'bynder_multi_img' => $new_value_array,
                        'bynder_isMain' => $flag
                    ];
                    $this->productAction->updateAttributes(
                        [$product_ids],
                        $updated_values,
                        $storeId
                    );
                    /*
                    $this->productAction->updateAttributes(
                        [$product_ids],
                        ['bynder_isMain' => $flag],
                        $storeId
                    );
                    */
                }
            } else {
                if (empty($doc_value)) {
                    $new_doc_array = explode("\n", $img_json);
                    //$isOrder = implode('', $byd_media_is_order);
                    $doc_detail = [];
                    foreach ($new_doc_array as $vv => $doc_value) {
                        if (!empty($doc_value)) {
                            $item_url = explode("?", $doc_value);
                            $media_doc_explode = explode("/", $item_url[0]);
                            $doc_detail[] = [
                                "item_url" => $item_url[0],
                                "item_type" => 'DOCUMENT',
                                "bynder_md_id" => $bynder_media_id[$vv],
                                "is_order" => $isOrder
                            ];
                        }
                    }
                    $new_value_array = json_encode($doc_detail, true);
                    $data_doc_value = [
                        'sku' => $product_sku_key,
                        'message' => $new_value_array,
                        'data_type' => '2',
                        "lable" => "1"
                    ];
                    $this->getInsertDataTable($data_doc_value);
                    $this->productAction->updateAttributes(
                        [$product_ids],
                        ['bynder_document' => $new_value_array],
                        $storeId
                    );
                }
            }
        } catch (\Exception $e) {
            return $result->setData(['message' => $e->getMessage()]);
        }
    }
}
