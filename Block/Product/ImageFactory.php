<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace DamConsultants\Idex\Block\Product;

use Magento\Catalog\Block\Product\Image as ImageBlock;
use Magento\Catalog\Model\View\Asset\ImageFactory as AssetImageFactory;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Image\ParamsBuilder;
use Magento\Catalog\Model\View\Asset\PlaceholderFactory;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\View\ConfigInterface;
use Magento\Catalog\Helper\Image as ImageHelper;
use Magento\Catalog\Api\ProductRepositoryInterface;

/**
 * Create imageBlock from product and view.xml
 *
 * @api
 */
class ImageFactory extends \Magento\Catalog\Block\Product\ImageFactory
{
    /**
     * @var ConfigInterface
     */
    private $presentationConfig;

    /**
     * @var AssetImageFactory
     */
    private $viewAssetImageFactory;

    /**
     * @var ParamsBuilder
     */
    private $imageParamsBuilder;

    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var viewAssetPlaceholderFactory
     */
    private $viewAssetPlaceholderFactory;
    /**
     * @var productRepository
     */
    protected $productRepository;

    /**
     * @param ObjectManagerInterface $objectManager
     * @param ConfigInterface $presentationConfig
     * @param AssetImageFactory $viewAssetImageFactory
     * @param PlaceholderFactory $viewAssetPlaceholderFactory
     * @param ProductRepositoryInterface $productRepository
     * @param ParamsBuilder $imageParamsBuilder
     */
    public function __construct(
        ObjectManagerInterface $objectManager,
        ConfigInterface $presentationConfig,
        AssetImageFactory $viewAssetImageFactory,
        PlaceholderFactory $viewAssetPlaceholderFactory,
        ProductRepositoryInterface $productRepository,
        ParamsBuilder $imageParamsBuilder
    ) {
        $this->objectManager = $objectManager;
        $this->presentationConfig = $presentationConfig;
        $this->viewAssetPlaceholderFactory = $viewAssetPlaceholderFactory;
        $this->viewAssetImageFactory = $viewAssetImageFactory;
        $this->imageParamsBuilder = $imageParamsBuilder;
        $this->productRepository = $productRepository;
    }

    /**
     * Remove class from custom attributes
     *
     * @param array $attributes
     * @return array
     */
    private function filterCustomAttributes(array $attributes): array
    {
        if (isset($attributes['class'])) {
            unset($attributes['class']);
        }
        return $attributes;
    }

    /**
     * Retrieve image class for HTML element
     *
     * @param array $attributes
     * @return string
     */
    private function getClass(array $attributes): string
    {
        return $attributes['class'] ?? 'product-image-photo';
    }

    /**
     * Calculate image ratio
     *
     * @param int $width
     * @param int $height
     * @return float
     */
    private function getRatio(int $width, int $height): float
    {
        if ($width && $height) {
            return $height / $width;
        }
        return 1.0;
    }

    /**
     * Get image label
     *
     * @param Product $product
     * @param string $imageType
     * @return string
     */
    private function getLabel(Product $product, string $imageType): string
    {
        $product_details = $this->productRepository->getById($product->getId());
        $bynderImage = $product_details->getBynderMultiImg();
        $use_bynder_cdn = $product_details->getUseBynderCdn();
        $label = "";
        $use_bynder_both_image = $product_details->getUseBynderBothImage();
        if ($use_bynder_cdn == 1 || $use_bynder_both_image == 1) {
            if ($bynderImage != "") {
                $json_value = json_decode($bynderImage, true);
                $json_value = $json_value["asset_list"];
                $small_image = 'Small';
                if (!empty($json_value)) {
                    foreach ($json_value as $values) {
                        if ($values['item_type'] == 'IMAGE') {
                            if (!empty($values['alt_text'])) {
                                $altText = trim($values['alt_text']);
                                $label = $altText;
                            }
                        }
                    }
                } else {
                    $label = $product->getData($imageType . '_' . 'label');
                    if (empty($label)) {
                        $label = $product->getName();
                    }
                }
            }
        } else {
            $label = $product->getData($imageType . '_' . 'label');
            if (empty($label)) {
                $label = $product->getName();
            }
        }
        return (string) $label;
    }
    /**
     * Create image block from product
     *
     * @param Product $product
     * @param string $imageId
     * @param array|null $attributes
     * @return ImageBlock
     */
    public function create(Product $product, string $imageId, array $attributes = null): ImageBlock
    {
        $viewImageConfig = $this->presentationConfig->getViewConfig()->getMediaAttributes(
            'Magento_Catalog',
            ImageHelper::MEDIA_TYPE_CONFIG_NODE,
            $imageId
        );
        $imageMiscParams = $this->imageParamsBuilder->build($viewImageConfig);
        $originalFilePath = $product->getData($imageMiscParams['image_type']);

        if ($originalFilePath === null || $originalFilePath === 'no_selection') {
            $imageAsset = $this->viewAssetPlaceholderFactory->create(
                [
                    'type' => $imageMiscParams['image_type']
                ]
            );
        } else {
            $imageAsset = $this->viewAssetImageFactory->create(
                [
                    'miscParams' => $imageMiscParams,
                    'filePath' => $originalFilePath,
                ]
            );
        }
        $attributes = $attributes === null ? [] : $attributes;
        $image_url = "";
        $product_details = $this->productRepository->getById($product->getId());
        $bynderImage = $product_details->getBynderMultiImg();
		$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
		$datahelper = $objectManager->get("\DamConsultants\Idex\Helper\Data");
		$default_image = $datahelper->getSmallPlaceHolder();
        //$default_image = "https://media.idexcorp.com/m/11a5506c07907565/Magento_Base-IDEXFS_Logo_Color_Transparent-200x200.png";
        $use_bynder_cdn = $product_details->getUseBynderCdn();
        $use_bynder_both_image = $product_details->getUseBynderBothImage();
        if ($use_bynder_cdn == 1 || $use_bynder_both_image == 1) {
            if ($bynderImage != "") {
                $json_value = json_decode($bynderImage, true);
                $json_value = $json_value["asset_list"];
                $item_old_asset_value = json_decode($bynderImage, true);
                $old_asset_detail_array = $item_old_asset_value['assets_extra_details'];
                foreach($old_asset_detail_array as $extra_detail) {
                    if(isset($extra_detail['brand_default_image'])) {
                        $default_image = $default_image;//$extra_detail['brand_default_image'];
                    }
                }
                $small_image = 'Small';
                if (!empty($json_value)) {
                    foreach ($json_value as $values) {
                        if ($values['item_type'] == 'IMAGE') {
                            if (isset($values['image_role'])) {
                                foreach ($values['image_role'] as $image_role) {
                                    if ($image_role == $small_image) {
                                        $image_values = trim($values['thum_url']);
                                        $image_url = $image_values;
                                    }
                                }
                            }
                        }
                    }
                }
                if (empty($image_url)) {
                    $image_url = $default_image;
                }
            } else {
                $image_url = $default_image;
            }
        } else {
            $image_url = $imageAsset->getUrl();
        }
        $data = [
            'data' => [
                'template' => 'Magento_Catalog::product/image_with_borders.phtml',
                'image_url' => $image_url,
                'width' => $imageMiscParams['image_width'],
                'height' => $imageMiscParams['image_height'],
                'label' => $this->getLabel($product, $imageMiscParams['image_type'] ?? ''),
                'ratio' => $this->getRatio($imageMiscParams['image_width'] ?? 0, $imageMiscParams['image_height'] ?? 0),
                'custom_attributes' => $this->filterCustomAttributes($attributes),
                'class' => $this->getClass($attributes),
                'product_id' => $product->getId()
            ],
        ];
       
        return $this->objectManager->create(ImageBlock::class, $data);
    }
}
