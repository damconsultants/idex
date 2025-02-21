<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace DamConsultants\Idex\Ui\Component\Listing\Column;

use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Framework\View\Element\UiComponent\ContextInterface;

class Thumbnail extends \Magento\Ui\Component\Listing\Columns\Column
{
    /**
     * @var $name
     */
    public const NAME = 'thumbnail';
    /**
     * @var $alt
     */
    public const ALT_FIELD = 'name';

    /**
     * @var \Magento\Catalog\Helper\Image
     */
    private $imageHelper;

    /**
     * @var \Magento\Framework\UrlInterface
     */
    private $urlBuilder;
    /**
     * @var $_productRepository
     */
    protected $_productRepository;

    /**
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param \Magento\Catalog\Helper\Image $imageHelper
     * @param \Magento\Framework\UrlInterface $urlBuilder
     * @param \Magento\Catalog\Model\ProductRepository $ProductRepository
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        \Magento\Catalog\Helper\Image $imageHelper,
        \Magento\Framework\UrlInterface $urlBuilder,
        \Magento\Catalog\Model\ProductRepository $ProductRepository,
        array $components = [],
        array $data = []
    ) {
        parent::__construct($context, $uiComponentFactory, $components, $data);
        $this->imageHelper = $imageHelper;
        $this->urlBuilder = $urlBuilder;
        $this->_productRepository = $ProductRepository;
    }
    /**
     * Prepare Data Source
     *
     * @param array $dataSource
     * @return array
     */
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            $fieldName = $this->getData('name');
			$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
			$datahelper = $objectManager->get("\DamConsultants\Idex\Helper\Data");
			$default_image = $datahelper->getSmallPlaceHolder();
            //$defaultImage = "https://media.idexcorp.com/m/11a5506c07907565/Magento_Base-IDEXFS_Logo_Color_Transparent-200x200.png";
            foreach ($dataSource['data']['items'] as &$item) {
                $_product = $this->_productRepository->getById($item['entity_id']);
                $image_value = $_product->getBynderMultiImg();
                if (!empty($image_value)) {
                    $item_old_value = json_decode($image_value, true);
                    if(isset($item_old_value["asset_list"])) {
                        $item_old_value = $item_old_value["asset_list"];
                    }
                    if (null == $item_old_value || empty($item_old_value)) {
                        continue;
                    }
                    $thumbnail = $this->getThumbnailUrl($item_old_value, $default_image);
                    $product = new \Magento\Framework\DataObject($item);
                    $item[$fieldName . '_src'] = $thumbnail;
                    $item[$fieldName . '_orig_src'] = $thumbnail;
                    $item[$fieldName . '_link'] = $this->urlBuilder->getUrl(
                        'catalog/product/edit',
                        ['id' => $product->getEntityId(), 'store' => $this->context->getRequestParam('store')]
                    );

                }
            }
        }
        return $dataSource;
    }

    private function getThumbnailUrl($imageData, $default_image)
    {
        if (!empty($imageData)) {
            foreach ($imageData as $img) {
                if (!empty($img['image_role']) && in_array('Thumbnail', $img['image_role'])) {
                    return $img['thum_url'];
                }
            }
        }
        return $default_image;
    }

    /**
     * Get Alt
     *
     * @param array $row
     *
     * @return null|string
     */
    protected function getAlt($row)
    {
        $altField = $this->getData('config/altField') ?: self::ALT_FIELD;
        return $row[$altField] ?? null;
    }
}
