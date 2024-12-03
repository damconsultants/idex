<?php

namespace DamConsultants\Idex\Model;

use DamConsultants\Idex\Api\BynderCustomInterface;
use DamConsultants\Idex\Model\ResourceModel\Collection\MetaPropertyCollectionFactory;

class BynderCustom implements BynderCustomInterface
{
    /**
     * @var $datahelper
     */
    protected $datahelper;
    /**
     * @var $metaPropertyCollectionFactory
     */
    protected $metaPropertyCollectionFactory;
    /**
     * Product Sku.
     * @param \DamConsultants\Idex\Helper\Data $DataHelper
     * @param MetaPropertyCollectionFactory $metaPropertyCollectionFactory
     */
    public function __construct(
        \DamConsultants\Idex\Helper\Data $DataHelper,
        MetaPropertyCollectionFactory $metaPropertyCollectionFactory
    ) {
        $this->datahelper = $DataHelper;
        $this->metaPropertyCollectionFactory = $metaPropertyCollectionFactory;
    }
    public function getSearchFromBynder($keyword, $role = null, $page = null, $limit = null)
    {
        $collection = $this->metaPropertyCollectionFactory->create()->getData();
        $meta_properties = $this->getMetaPropertiesCollection($collection);

        if (count($meta_properties["collection_data_value"]) > 0) {
            foreach ($meta_properties["collection_data_value"] as $metacollection) {
                $property_collections_details[$metacollection['system_slug']] = array(
                    "id" => $metacollection['id'],
                    "property_name" => $metacollection['property_name'],
                    "property_id" => $metacollection['property_id'],
                    "magento_attribute" => $metacollection['magento_attribute'],
                    "attribute_id" => $metacollection['attribute_id'],
                    "bynder_property_slug" => $metacollection['bynder_property_slug'],
                    "system_slug" => $metacollection['system_slug'],
                    "system_name" => $metacollection['system_name'],
                );
            }
            $all_properties_slug = array_keys($property_collections_details);
        }

        $file_title_slug_val = "property_" . $property_collections_details['file_title']['bynder_property_slug'];
        $style_slug_val = "property_" . $property_collections_details['style']['bynder_property_slug'];

        $all_details = array_column($meta_properties["collection_data_value"], "system_slug");

        $find_key_for_role = array_search("customer_visibility", $all_details);
        $role_meta_slug = $meta_properties["collection_data_value"][$find_key_for_role]["bynder_property_slug"];
        $search_values = array();
        $search_by = "";
        $response_data = array(
            "data" => array(),
            "total_records" => 0,
            "search_by" => "",
            "status" => 0,
            "message" => "Please enter keyword.",
        );
        if ($keyword == "") {
            echo json_encode($response_data, true);
            exit;
        } else {
            $kew = array(
                "keyword" => $keyword,
                "type" => "document",
                "count" => 1
            );
            $search_values = $kew;
            $search_by .= "Keyword ";
        }

        if ($role != "") {
            $roleLabelCollection = $this->datahelper->getCustomerVisibilityLabel($role);
            if (count($roleLabelCollection) > 0) {
                $role = $roleLabelCollection[0]['option_name'];
                $role_val = array("property_" . $role_meta_slug => $role);
                $search_values = array_merge($search_values, $role_val);
                $search_by .= "and Role";
            }
        }

        if ($page != "") {
            $page_val = array("page" => $page);
            $search_values = array_merge($search_values, $page_val);
            $search_by .= "with page size";
        }

        if ($limit != "") {
            $limit_val = array("limit" => $limit);
            $search_values = array_merge($search_values, $limit_val);
            $search_by .= "and limit";
        }

        $search_details = array(
            "keyword" => $keyword,
            "role"  =>  $role,
            "page" => $page,
            "limit" => $limit
        );
        try {

            $basic_details = $this->datahelper->searchPriceListFromBynder($search_details, $search_values, $meta_properties);
            $basic_details = json_decode($basic_details, true);
            $res_data = array();
            if ($basic_details["count"]["total"] > 0) {
                foreach ($basic_details["media"] as $og_k => $og_v) {
                    if (count($all_properties_slug) > 0) {
                        $b = [];
                        $c = [];
                        $f = [];
                        foreach ($all_properties_slug as $bn => $all_slugs) {
                            $slug_v = "property_" . $property_collections_details[$all_slugs]['bynder_property_slug'];
                            //$res_data[$og_k]["all_slug_ids"][$bn] = $slug_v;
                            if (isset($og_v[$slug_v])) {
                                // $res_data[$og_k][$all_slugs] = $og_v[$slug_v];
                                $res_data[$og_k]["assets_extra_details"][$all_slugs] = $og_v[$slug_v];
                            } else {
                                //$res_data[$og_k][$all_slugs] = "";
                                $res_data[$og_k]["assets_extra_details"][$all_slugs] = "";
                            }
                        }
                        if (!empty($res_data[$og_k]["assets_extra_details"]['brands'])) {
                            foreach ($res_data[$og_k]["assets_extra_details"]['brands'] as $brands) {
                                $brandCollection = $this->datahelper->getBrandName($brands);
                                $b['brands_lables'][] = $brandCollection[0]['option_label'];
                            }
                            $res_data[$og_k]["assets_extra_details"]["brands_lables"] = $b['brands_lables'];
                        } else {
                            $res_data[$og_k]["assets_extra_details"]["brands_lables"] = "";
                        }



                        if (!empty($res_data[$og_k]["assets_extra_details"]['customer_visibility'])) {
                            foreach ($res_data[$og_k]["assets_extra_details"]['customer_visibility'] as $customer) {
                                $customerCollection = $this->datahelper->getCustomerVisibilityName($customer);
                                $c['customer_visibility_lables'][] = $customerCollection[0]['option_label'];
                            }
                            $res_data[$og_k]["assets_extra_details"]["customer_visibility_lables"] = $c['customer_visibility_lables'];
                        } else {
                            $res_data[$og_k]["assets_extra_details"]["customer_visibility_lables"] = "";
                        }


                        if (!empty($res_data[$og_k]["assets_extra_details"]['file_category'])) {
                            foreach ($res_data[$og_k]["assets_extra_details"]['file_category'] as $file) {
                                $fileCollection = $this->datahelper->getFileCatagoryName($file);
                                $f['file_category_lables'][] = $fileCollection[0]['option_label'];
                            }
                            $res_data[$og_k]["assets_extra_details"]["file_category_lables"] = $f['file_category_lables'];
                        } else {
                            $res_data[$og_k]["assets_extra_details"]["file_category_lables"] = "";
                        }

                        $is_public = $og_v['isPublic'];
                        $original_size = $og_v['fileSize'];
                        $formated_size = $this->formatBytes($original_size);
                        
                        $publish_date_original = $og_v["datePublished"];
                        $modified_date_original = isset($og_v["dateModified"])?$og_v["dateModified"]:"";
                        $modified_date = "";
                        $published_date = "";
                        if($modified_date_original != ""){
                            $og_modified_time = strtotime($modified_date_original);
                            $modified_date = date("Y-m-d",$og_modified_time);
                        }

                        if($publish_date_original != ""){
                            $og_publish_time = strtotime($publish_date_original);
                            $published_date = date("Y-m-d",$og_publish_time);
                        }

                        $res_data[$og_k]["assets_extra_details"]["published_date"] = $published_date;
                        $res_data[$og_k]["assets_extra_details"]["modified_date"] = $modified_date;
                        $res_data[$og_k]["assets_extra_details"]["extension_type"] = implode("",$og_v['extension']);
                        $res_data[$og_k]["assets_extra_details"]["file_size"] = $formated_size;
                        $res_data[$og_k]["assets_extra_details"]["is_public"] = $is_public;
                    }

                    $res_data[$og_k]["docs_details"]["doc_link"] = isset($og_v["original"]) ? $og_v["original"] : "";
                    $res_data[$og_k]["docs_details"]["doc_name"] = isset($og_v[$file_title_slug_val]) ? $og_v[$file_title_slug_val] : $og_v["name"];
                    $res_data[$og_k]["docs_details"]["style_number"] = isset($og_v[$style_slug_val]) ? $og_v[$style_slug_val] : "";
                    $res_data[$og_k]["docs_details"]["bynder_assets_id"] = $og_v["id"];
                    $res_data[$og_k]["docs_details"]["bynder_assets_hashid"] = $og_v["idHash"];
                }
            }

            $response_data["data"] = $res_data;
            $response_data["total_records"] = $basic_details["count"]["total"];
            $response_data["search_by"] = $search_by;
            $response_data["status"] = 1;
            $response_data["message"] = "success";
        } catch (\Exception $e) {
            $response_data["data"] = $res_data;
            $response_data["total_records"] = 0;
            $response_data["search_by"] = $search_by;
            $response_data["status"] = 0;
            $response_data["message"] = $e->getMessage();
        }
        header('Content-Type: application/json');
        echo json_encode($response_data, true);
        exit;
    }

    public function getListUserPriceList($keyword = null, $role = null, $page = null, $limit = null)
    {
        $collection = $this->metaPropertyCollectionFactory->create()->getData();
        $meta_properties = $this->getMetaPropertiesCollection($collection);

        if (count($meta_properties["collection_data_value"]) > 0) {
            foreach ($meta_properties["collection_data_value"] as $metacollection) {
                $property_collections_details[$metacollection['system_slug']] = array(
                    "id" => $metacollection['id'],
                    "property_name" => $metacollection['property_name'],
                    "property_id" => $metacollection['property_id'],
                    "magento_attribute" => $metacollection['magento_attribute'],
                    "attribute_id" => $metacollection['attribute_id'],
                    "bynder_property_slug" => $metacollection['bynder_property_slug'],
                    "system_slug" => $metacollection['system_slug'],
                    "system_name" => $metacollection['system_name'],
                );
            }
            $all_properties_slug = array_keys($property_collections_details);
        }

        $file_title_slug_val = "property_" . $property_collections_details['file_title']['bynder_property_slug'];
        $style_slug_val = "property_" . $property_collections_details['style']['bynder_property_slug'];

        $all_details = array_column($meta_properties["collection_data_value"], "system_slug");

        $find_key_for_search = array_search("asset_sub_type", $all_details);
        $pricing_meta_slug = $meta_properties["collection_data_value"][$find_key_for_search]["bynder_property_slug"];

        $find_key_for_role = array_search("customer_visibility", $all_details);
        $role_meta_slug = $meta_properties["collection_data_value"][$find_key_for_role]["bynder_property_slug"];
        $search_values = array();
        $search_by = "";
        if ($keyword == "") {
            $keyword = "Pricing";
        }

        $kew = array(
            "property_" . $pricing_meta_slug => $keyword,
            "type" => "document",
            "count" => 1
        );
        $search_values = $kew;
        $search_by .= "Keyword ";

        if ($role != "") {
            $roleLabelCollection = $this->datahelper->getCustomerVisibilityLabel($role);
            if (count($roleLabelCollection) > 0) {
                $role = $roleLabelCollection[0]['option_name'];
                $role_val = array("property_" . $role_meta_slug => $role);
                $search_values = array_merge($search_values, $role_val);
                $search_by .= "and Role";
            }
        }

        if ($page != "") {
            $page_val = array("page" => $page);
            $search_values = array_merge($search_values, $page_val);
            $search_by .= "with page size";
        }

        if ($limit != "") {
            $limit_val = array("limit" => $limit);
            $search_values = array_merge($search_values, $limit_val);
            $search_by .= "and limit";
        }

        $search_details = array(
            "keyword" => $keyword,
            "role"  =>  $role,
            "page" => $page,
            "limit" => $limit
        );
        try {
            //code...

            $basic_details = $this->datahelper->searchPriceListFromBynder($search_details, $search_values, $meta_properties);
            $basic_details = json_decode($basic_details, true);
            $res_data = array();
            if ($basic_details["count"]["total"] > 0) {
                foreach ($basic_details["media"] as $og_k => $og_v) {
                    /* echo "<pre>";
                print_r($basic_details["media"]);
                exit; */
                    if (count($all_properties_slug) > 0) {
                        $b = [];
                        $c = [];
                        $f = [];
                        foreach ($all_properties_slug as $bn => $all_slugs) {
                            $slug_v = "property_" . $property_collections_details[$all_slugs]['bynder_property_slug'];
                            //$res_data[$og_k]["all_slug_ids"][$bn] = $slug_v;
                            if (isset($og_v[$slug_v])) {
                                // $res_data[$og_k][$all_slugs] = $og_v[$slug_v];
                                $res_data[$og_k]["assets_extra_details"][$all_slugs] = $og_v[$slug_v];
                            } else {
                                //$res_data[$og_k][$all_slugs] = "";
                                $res_data[$og_k]["assets_extra_details"][$all_slugs] = "";
                            }
                        }
                        if (!empty($res_data[$og_k]["assets_extra_details"]['brands'])) {
                            foreach ($res_data[$og_k]["assets_extra_details"]['brands'] as $brands) {
                                $brandCollection = $this->datahelper->getBrandName($brands);
                                $b['brands_lables'][] = $brandCollection[0]['option_label'];
                            }
                            $res_data[$og_k]["assets_extra_details"]["brands_lables"] = $b['brands_lables'];
                        } else {
                            $res_data[$og_k]["assets_extra_details"]["brands_lables"] = "";
                        }

                        if (!empty($res_data[$og_k]["assets_extra_details"]['customer_visibility'])) {
                            foreach ($res_data[$og_k]["assets_extra_details"]['customer_visibility'] as $customer) {
                                $customerCollection = $this->datahelper->getCustomerVisibilityName($customer);
                                $c['customer_visibility_lables'][] = $customerCollection[0]['option_label'];
                            }
                            $res_data[$og_k]["assets_extra_details"]["customer_visibility_lables"] = $c['customer_visibility_lables'];
                        } else {
                            $res_data[$og_k]["assets_extra_details"]["customer_visibility_lables"] = "";
                        }

                        if (!empty($res_data[$og_k]["assets_extra_details"]['file_category'])) {
                            foreach ($res_data[$og_k]["assets_extra_details"]['file_category'] as $file) {
                                $fileCollection = $this->datahelper->getFileCatagoryName($file);
                                $f['file_category_lables'][] = $fileCollection[0]['option_label'];
                            }
                            $res_data[$og_k]["assets_extra_details"]["file_category_lables"] = $f['file_category_lables'];
                        } else {
                            $res_data[$og_k]["assets_extra_details"]["file_category_lables"] = "";
                        }

                        $is_public = $og_v['isPublic'];
                        $original_size = $og_v['fileSize'];
                        $formated_size = $this->formatBytes($original_size);
                        
                        $publish_date_original = $og_v["datePublished"];
                        $modified_date_original = isset($og_v["dateModified"])?$og_v["dateModified"]:"";

                        $modified_date = "";
                        $published_date = "";
                        if($modified_date_original != ""){
                            $og_modified_time = strtotime($modified_date_original);
                            $modified_date = date("Y-m-d",$og_modified_time);
                        }

                        if($publish_date_original != ""){
                            $og_publish_time = strtotime($publish_date_original);
                            $published_date = date("Y-m-d",$og_publish_time);
                        }

                        $res_data[$og_k]["assets_extra_details"]["published_date"] = $published_date;
                        $res_data[$og_k]["assets_extra_details"]["modified_date"] = $modified_date;
                        $res_data[$og_k]["assets_extra_details"]["extension_type"] = implode("",$og_v['extension']);
                        $res_data[$og_k]["assets_extra_details"]["file_size"] = $formated_size;
                        $res_data[$og_k]["assets_extra_details"]["is_public"] = $is_public;
                    }

                    $res_data[$og_k]["docs_details"]["doc_link"] = isset($og_v["original"]) ? $og_v["original"] : "";
                    $res_data[$og_k]["docs_details"]["doc_name"] = isset($og_v[$file_title_slug_val]) ? $og_v[$file_title_slug_val] : $og_v["name"];
                    $res_data[$og_k]["docs_details"]["style_number"] = isset($og_v[$style_slug_val]) ? $og_v[$style_slug_val] : "";
                    $res_data[$og_k]["docs_details"]["bynder_assets_id"] = $og_v["id"];
                    $res_data[$og_k]["docs_details"]["bynder_assets_hashid"] = $og_v["idHash"];
                }
            }

            $response_data["data"] = $res_data;
            $response_data["total_records"] = $basic_details["count"]["total"];
            $response_data["search_by"] = $search_by;
            $response_data["status"] = 1;
            $response_data["message"] = "success";
        } catch (\Exception $e) {
            $response_data["data"] = $res_data;
            $response_data["total_records"] = 0;
            $response_data["search_by"] = $search_by;
            $response_data["status"] = 0;
            $response_data["message"] = $e->getMessage();
        }
        header('Content-Type: application/json');
        echo json_encode($response_data, true);
        exit;
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
     * Get formatBytes
     *
     * @param int $bytes
     * @param string $force_unit
     * @param int $format
     * @return string $response_array
     */

    public function formatBytes($bytes, $force_unit = "t", $format = 0, $si = TRUE){
        // Format string
        $format = ($format === 0) ? '%01.2f %s' : (string) $format;
    
        // IEC prefixes (binary)
        if ($si == FALSE OR strpos($force_unit, 'i') !== FALSE)
        {
            $units = array('B', 'KiB', 'MiB', 'GiB', 'TiB', 'PiB');
            $mod   = 1024;
        }
        // SI prefixes (decimal)
        else
        {
            $units = array('B', 'kB', 'MB', 'GB', 'TB', 'PB');
            $mod   = 1000;
        }
    
        // Determine unit to use
        if (($power = array_search((string) $force_unit, $units)) === FALSE)
        {
            $power = ($bytes > 0) ? floor(log($bytes, $mod)) : 0;
        }
        
        $response_array = sprintf($format, $bytes / pow($mod, $power), $units[$power]);
        return $response_array;
    }
}
