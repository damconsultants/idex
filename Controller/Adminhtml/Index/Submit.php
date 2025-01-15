<?php

namespace DamConsultants\Idex\Controller\Adminhtml\Index;

use DamConsultants\Idex\Model\ResourceModel\Collection\MetaPropertyCollectionFactory;

class Submit extends \Magento\Backend\App\Action
{
    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $resultPageFactory = false;
    /**
     * @var $_helperData
     */
    protected $_helperData;
    /**
     * @var $metaProperty
     */
    protected $metaProperty;
    /**
     * @var $brandOption
     */
    protected $brandOption;
    /**
     * @var $customerVisibilityOption
     */
    protected $customerVisibilityOption;
    /**
     * @var $fileCategoryOption
     */
    protected $fileCategoryOption;
    /**
     * @var $brandOptionResource
     */
    protected $brandOptionResource;
    /**
     * @var $customerVisibilityOptionResource
     */
    protected $customerVisibilityOptionResource;
    /**
     * @var $fileCategoryOptionResource
     */
    protected $fileCategoryOptionResource;
    /**
     * @var $metaPropertyCollectionFactory
     */
    protected $metaPropertyCollectionFactory;

    /**
     * Submit.
     * @param \Magento\Backend\App\Action\Context $context
     * @param \DamConsultants\Idex\Helper\Data $helperData
     * @param \DamConsultants\Idex\Model\MetaPropertyFactory $metaProperty
     * @param \DamConsultants\Idex\Model\BrandOptionFactory $brandOption
     * @param \DamConsultants\Idex\Model\CustomerVisibilityOptionFactory $customerVisibilityOption
     * @param \DamConsultants\Idex\Model\FileCategoryOptionFactory $fileCategoryOption
     * @param \DamConsultants\Idex\Model\ResourceModel\BrandOption $brandOptionResource
     * @param \DamConsultants\Idex\Model\ResourceModel\CustomerVisibilityOption $customerVisibilityOptionResource
     * @param \DamConsultants\Idex\Model\ResourceModel\FileCategoryOption $fileCategoryOptionResource
     * @param MetaPropertyCollectionFactory $metaPropertyCollectionFactory
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \DamConsultants\Idex\Helper\Data $helperData,
        \DamConsultants\Idex\Model\MetaPropertyFactory $metaProperty,
        \DamConsultants\Idex\Model\BrandOptionFactory $brandOption,
        \DamConsultants\Idex\Model\CustomerVisibilityOptionFactory $customerVisibilityOption,
        \DamConsultants\Idex\Model\FileCategoryOptionFactory $fileCategoryOption,
        \DamConsultants\Idex\Model\ResourceModel\BrandOption $brandOptionResource,
        \DamConsultants\Idex\Model\ResourceModel\CustomerVisibilityOption $customerVisibilityOptionResource,
        \DamConsultants\Idex\Model\ResourceModel\FileCategoryOption $fileCategoryOptionResource,
        MetaPropertyCollectionFactory $metaPropertyCollectionFactory,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory
    ) {
        parent::__construct($context);
        $this->_helperData = $helperData;
        $this->metaProperty = $metaProperty;
        $this->brandOption = $brandOption;
        $this->customerVisibilityOption = $customerVisibilityOption;
        $this->fileCategoryOption = $fileCategoryOption;
        $this->brandOptionResource = $brandOptionResource;
        $this->customerVisibilityOptionResource = $customerVisibilityOptionResource;
        $this->fileCategoryOptionResource = $fileCategoryOptionResource;
        $this->metaPropertyCollectionFactory = $metaPropertyCollectionFactory;
        $this->resultPageFactory = $resultPageFactory;
    }
    /**
     * Execute
     *
     * @return $this
     */
    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        try {
            $metadata = $this->_helperData->getBynderMetaProperites();
            $data = json_decode($metadata, true);
            $properites_system_slug = $this->getRequest()->getParam('system_slug');
            $select_meta_tag = $this->getRequest()->getParam('select_meta_tag');
            $collection = $this->metaPropertyCollectionFactory->create();
            $meta = [];
            $properties_details = [];
            $all_properties_slug = [];
            $brandmodel = $this->brandOption->create();
            $customerVisibilitymodel = $this->customerVisibilityOption->create();
            $fileCatagorymodel = $this->fileCategoryOption->create();
            
            $get_collection_data = $collection->getData();
            if (count($get_collection_data) > 0) {
                /** option table empty */
                $this->brandOptionResource->truncate();
                $this->customerVisibilityOptionResource->truncate();
                $this->fileCategoryOptionResource->truncate();

                foreach ($get_collection_data as $metacollection) {
                    $properties_details[$metacollection['system_slug']] = [
                        "id" => $metacollection['id'],
                        "property_name" => $metacollection['property_name'],
                        "property_id" => $metacollection['property_id'],
                        "magento_attribute" => $metacollection['magento_attribute'],
                        "attribute_id" => $metacollection['attribute_id'],
                        "bynder_property_slug" => $metacollection['bynder_property_slug'],
                        "system_slug" => $metacollection['system_slug'],
                        "system_name" => $metacollection['system_name'],
                    ];
                }
                $all_properties_slug = array_keys($properties_details);
                
                foreach ($properites_system_slug as $key => $form_system_slug) {
                    
                    if (in_array($form_system_slug, $all_properties_slug)) {
                        /* update data */
                        $pro_id = $properties_details[$form_system_slug]["id"];
                        $model = $this->metaProperty->create()->load($pro_id);
                    } else {
                        /* insert data */
                        $model = $this->metaProperty->create();
                    }
                    $model->setData('property_name', $data['data'][$select_meta_tag[$key]]['label']);
                    $model->setData('property_id', $data['data'][$select_meta_tag[$key]]['id']);
                    $model->setData('bynder_property_slug', $data['data'][$select_meta_tag[$key]]['name']);
                    $model->setData('system_slug', $form_system_slug);
                    $model->setData('system_name', $form_system_slug);
                    $model->save();
                    
					$options = $data['data'][$select_meta_tag[$key]]["options"];
					
                    if ($form_system_slug == "customer_visibility") {
                        if (count($options) > 0) {
                            foreach ($options as $v) {
                                $option_id = $v["id"];
                                $option_label = $v["label"];
                                $option_name = $v["name"];
                                $bynder_status = $v["active"];
                                $data_options = [
                                    'option_id' => $option_id,
                                    'option_label' => $option_label,
                                    'option_name' => $option_name,
                                    'bynder_status' => $bynder_status,
                                    'status' => 1
                                ];
                                $customerVisibilitymodel->setData($data_options)->save();
                            }
                        }
                    } elseif ($form_system_slug == "brands") {
                        if (count($options) > 0) {
                            foreach ($options as $v) {
                                $option_id = $v["id"];
                                $option_label = $v["label"];
                                $option_name = $v["name"];
                                $bynder_status = $v["active"];
                                $data_options = [
                                    'option_id' => $option_id,
                                    'option_label' => $option_label,
                                    'option_name' => $option_name,
                                    'bynder_status' => $bynder_status,
                                    'status' => 1
                                ];
                                $brandmodel->setData($data_options)->save();
                            }
                        }
                    } elseif ($form_system_slug == "file_category") {
                        if (count($options) > 0) {
                            foreach ($options as $v) {
                                $option_id = $v["id"];
                                $option_label = $v["label"];
                                $option_name = $v["name"];
                                $bynder_status = $v["active"];
                                $data_options = [
                                    'option_id' => $option_id,
                                    'option_label' => $option_label,
                                    'option_name' => $option_name,
                                    'bynder_status' => $bynder_status,
                                    'status' => 1
                                ];
                                $fileCatagorymodel->setData($data_options)->save();
                            }
                        }
                    }
                }
            } else {
                /* insert all data */
                foreach ($properites_system_slug as $key => $form_system_slug) {
                    $model = $this->metaProperty->create();
                    $model->setData('property_name', $data['data'][$select_meta_tag[$key]]['label']);
                    $model->setData('property_id', $data['data'][$select_meta_tag[$key]]['id']);
                    $model->setData('bynder_property_slug', $data['data'][$select_meta_tag[$key]]['name']);
                    $model->setData('system_slug', $form_system_slug);
                    $model->setData('system_name', $form_system_slug);
                    $model->save();

                    $options = $data['data'][$select_meta_tag[$key]]["options"];
                    
                    if ($form_system_slug == "customer_visibility") {
                        if (count($options) > 0) {
                            foreach ($options as $k => $v) {
                                $option_id = $v["id"];
                                $option_label = $v["label"];
                                $option_name = $v["name"];
                                $bynder_status = $v["active"];
                                $data_options = [
                                    'option_id' => $option_id,
                                    'option_label' => $option_label,
                                    'option_name' => $option_name,
                                    'bynder_status' => $bynder_status,
                                    'status' => "1"
                                ];
                                $customerVisibilitymodel->setData($data_options)->save();
                            }
                        }
                    } elseif ($form_system_slug == "brands") {
                        if (count($options) > 0) {
                            foreach ($options as $k => $v) {
                                $option_id = $v["id"];
                                $option_label = $v["label"];
                                $option_name = $v["name"];
                                $bynder_status = $v["active"];
                                $data_options = [
                                    'option_id' => $option_id,
                                    'option_label' => $option_label,
                                    'option_name' => $option_name,
                                    'bynder_status' => $bynder_status,
                                    'status' => "1"
                                ];
                                $brandmodel->setData($data_options)->save();
                            }
                        }
                    } elseif ($form_system_slug == "file_category") {
                        if (count($options) > 0) {
                            foreach ($options as $k => $v) {
                                $option_id = $v["id"];
                                $option_label = $v["label"];
                                $option_name = $v["name"];
                                $bynder_status = $v["active"];
                                $data_options = [
                                    'option_id' => $option_id,
                                    'option_label' => $option_label,
                                    'option_name' => $option_name,
                                    'bynder_status' => $bynder_status,
                                    'status' => "1"
                                ];
                                $fileCatagorymodel->setData($data_options)->save();
                            }
                        }
                    }
                }
            }
            $message = __('Submited MetaProperty...!');
            
            $this->messageManager->addSuccessMessage($message);
            $this->resultPageFactory->create();
            return $resultRedirect->setPath('bynder/index/metaproperty');
        } catch (\Exception $e) {
            $this->messageManager->addException($e, __('We can\'t submit your request, Please try again.'));
        }
    }
}
