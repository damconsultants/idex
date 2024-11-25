<?php

namespace DamConsultants\Idex\Model\ResourceModel\Collection;

class CustomerVisibilityOptionCollection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    /**
     * Collection
     *
     * @return $this
     */
    protected function _construct()
    {
        $this->_init(
            \DamConsultants\Idex\Model\CustomerVisibilityOption::class,
            \DamConsultants\Idex\Model\ResourceModel\CustomerVisibilityOption::class
        );
    }
}
