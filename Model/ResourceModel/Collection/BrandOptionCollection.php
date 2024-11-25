<?php

namespace DamConsultants\Idex\Model\ResourceModel\Collection;

class BrandOptionCollection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    /**
     * Collection
     *
     * @return $this
     */
    protected function _construct()
    {
        $this->_init(
            \DamConsultants\Idex\Model\BrandOption::class,
            \DamConsultants\Idex\Model\ResourceModel\BrandOption::class
        );
    }
}
