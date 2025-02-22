<?php

namespace DamConsultants\Idex\Controller\Adminhtml\Index;

class Getsku extends \Magento\Backend\App\Action
{
    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    /**
     * @var $resultPageFactory;
     */
    protected $resultPageFactory = false;
    /**
     * @var $resultPageFactory;
     */
    protected $attribute;
    /**
     * @var $resultPageFactory;
     */
    protected $collectionFactory;
    /**
     * @var $resultPageFactory;
     */
    protected $resultJsonFactory;
    /**
     * @var $resultPageFactory;
     */
    protected $productAttributeManagementInterface;
	/**
     * @var $storeManagerInterface
     */
    protected $storeManagerInterface;

    /**
     * Get Sku.
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Catalog\Model\ResourceModel\Eav\Attribute $attribute
     * @param \Magento\Framework\Controller\Result\JsonFactory $jsonFactory
	 * @param \Magento\Store\Model\StoreManagerInterface $storeManagerInterface
     * @param \Magento\Catalog\Api\ProductAttributeManagementInterface $productAttributeManagementInterface
     * @param \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $collectionFactory
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Catalog\Model\ResourceModel\Eav\Attribute $attribute,
        \Magento\Framework\Controller\Result\JsonFactory $jsonFactory,
		\Magento\Store\Model\StoreManagerInterface $storeManagerInterface,
        \Magento\Catalog\Api\ProductAttributeManagementInterface $productAttributeManagementInterface,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $collectionFactory
    ) {
        parent::__construct($context);
        $this->attribute = $attribute;
        $this->collectionFactory = $collectionFactory;
        $this->resultJsonFactory = $jsonFactory;
		$this->storeManagerInterface = $storeManagerInterface;
        $this->productAttributeManagementInterface = $productAttributeManagementInterface;
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
            return;
        }
        $attribute_value = $this->getRequest()->getParam('select_attribute');
        $sku_limit = $this->getRequest()->getParam('sku_limit');
        $select_store = $this->getRequest()->getParam('select_store');
        $product_sku = [];
        $sku = [];
        $id = [];
        $attribute = $this->collectionFactory->create();
       
		if ($select_store == 'all_store') {
			$productcollection = $this->collectionFactory->create()
				->addAttributeToSelect('*')
				->addAttributeToFilter('status', \Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED);
			/*$productcollection->getSelect()->limit(400);*/
		} else {
			$productcollection = $this->collectionFactory->create()
				->addAttributeToSelect('*')
				->addAttributeToFilter('status', \Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED)
				->addStoreFilter($select_store);
			/*$productcollection->getSelect()->limit(400);*/
		}
        if ($sku_limit != 0) {
            $productcollection->getSelect()->limit($sku_limit);
        }
        foreach ($attribute as $value) {
            $id[] = $value['attribute_set_id'];
        }
        $array = array_unique($id);
        foreach ($array as $ids) {
            $productAttributes = $this->productAttributeManagementInterface->getAttributes($ids);

            foreach ($productAttributes as $atttr) {

                if ($atttr->getAttributeCode() == "bynder_multi_img") {
                    $image_id[] = $atttr->getAttributeSetId();
                } elseif ($atttr->getAttributeCode() == "bynder_document") {

                    $doc_id[] = $atttr->getAttributeSetId();
                }
            }
        }
        /*  IMAGE & VIDEO == 1 , IMAGE == 2 , VIDEO == 3 */
        $final = array_merge($image_id, $doc_id);
        $ids = array_unique($final);
        if (!empty($attribute_value)) {
            if ($attribute_value == "image") {
                $productcollection->addAttributeToFilter('attribute_set_id', $image_id);
                
                foreach ($productcollection as $product) {
                    if (!empty($product['bynder_multi_img'])) {
                        if ($product['bynder_isMain'] != "2" && $product['bynder_isMain'] != "1") {
                            $product_sku[] = $product->getSku();
                        }
                    } else {
                        $product_sku[] = $product->getSku();
                    }
                }
            } elseif ($attribute_value == "video") {
                $productcollection->addAttributeToFilter('attribute_set_id', $image_id);
                
                foreach ($productcollection as $product) {
                    if (!empty($product['bynder_multi_img'])) {
                        if ($product['bynder_isMain'] != "3" && $product['bynder_isMain'] != "1") {
                            $product_sku[] = $product->getSku();
                        }
                    } else {
                        $product_sku[] = $product->getSku();
                    }
                    
                }
            } elseif ($attribute_value == "document") {

                $productcollection->addAttributeToFilter('attribute_set_id', $doc_id)
                    ->addAttributeToFilter(
                        [
                            ['attribute' => 'bynder_document', 'null' => true]
                        ]
                    );
                foreach ($productcollection as $product) {
                    $product_sku[] = $product->getSku();
                }
            } elseif ($attribute_value == "all_attribute") {
				// Filter products by 'attribute_set_id' with $image_id
				$productcollection->addAttributeToFilter('attribute_set_id', $image_id);

				foreach ($productcollection as $product) {
					// Check for 'bynder_multi_img' and filter based on 'bynder_isMain'
					if (!empty($product['bynder_multi_img'])) {
						if ($product['bynder_isMain'] != '1') {
							$product_sku[] = $product->getSku();
						}
					} else {
						$product_sku[] = $product->getSku();
					}
				}

				// Add additional filter for 'attribute_set_id' with $doc_id and 'bynder_document' being null
				$productcollection->clear()
					->addAttributeToFilter('attribute_set_id', $doc_id)
					->addAttributeToFilter('bynder_document', ['null' => true]);

				foreach ($productcollection as $product) {
					$product_sku[] = $product->getSku();
				}
			}
			
        } else {
            $productcollection->addAttributeToFilter('attribute_set_id', $ids)
                ->addAttributeToFilter(
                    [
                        ['attribute' => 'bynder_multi_img', 'null' => true],
                        ['attribute' => 'bynder_document', 'null' => true]
                    ]
                );
            foreach ($productcollection as $product) {
                $product_sku[] = $product->getSku();
            }
        }
        $sku = array_unique($product_sku);
        if (count($sku) > 0) {
            $status = 1;
            $data_sku = implode(",", $sku);
        } else {
            $status = 0;
            $data_sku = "There is not any empty Bynder Data in product";
        }
        $result = $this->resultJsonFactory->create();
        return $result->setData(['status' => $status, 'message' => $data_sku]);
    }
	 /**
     * Is int
     *
     * @return $array
     */
    public function getMyStoreId()
    {
        $storeId = [];
        foreach ($this->storeManagerInterface->getStores() as $store) { 
            $storeId[] = $store->getId();
        }
        //$storeId = $this->storeManagerInterface->getStore()->getId();
        return $storeId;
    }
}
