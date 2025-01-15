<?php
namespace DamConsultants\Idex\Setup\Patch\Data;

use Magento\Eav\Setup\EavSetup;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface;

class UpdateAttributesScope implements DataPatchInterface
{
    private $moduleDataSetup;
    private $eavSetupFactory;

    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup,
        EavSetupFactory $eavSetupFactory
    ) {
        $this->moduleDataSetup = $moduleDataSetup;
        $this->eavSetupFactory = $eavSetupFactory;
    }

    public function apply()
    {
        $this->moduleDataSetup->getConnection()->startSetup();
        
        /** @var EavSetup $eavSetup */
        $eavSetup = $this->eavSetupFactory->create(['setup' => $this->moduleDataSetup]);

        // List of attribute codes to update
        $attributes = [
            'bynder_multi_img',
            'bynder_document',
            'use_bynder_both_image',
            'use_bynder_cdn',
            'bynder_isMain',
            'bynder_cron_sync',
            'bynder_auto_replace',
            'bynder_delete_cron'
        ];

        // Update each attribute's scope to store
        foreach ($attributes as $attributeCode) {
            $eavSetup->updateAttribute(
                \Magento\Catalog\Model\Product::ENTITY,
                $attributeCode,
                'is_global',
                ScopedAttributeInterface::SCOPE_STORE
            );
        }

        $this->moduleDataSetup->getConnection()->endSetup();
    }

    public static function getDependencies()
    {
        return [];
    }

    public function getAliases()
    {
        return [];
    }
}
