<?php

namespace DamConsultants\Idex\Model\ResourceModel\Collection;

class DefaultMetaPropertyCollection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    /**
     * MetaPropertyCollection
     *
     * @return $this
     */
    protected function _construct()
    {
        $this->_init(
            \DamConsultants\Idex\Model\DefaultMetaProperty::class,
            \DamConsultants\Idex\Model\ResourceModel\DefaultMetaProperty::class
        );
    }
}
