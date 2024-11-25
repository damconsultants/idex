<?php

namespace DamConsultants\Idex\Model\ResourceModel;

class CustomerVisibilityOption extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * Bynder
     *
     * @return $this
     */
    protected function _construct()
    {
        $this->_init('bynder_customer_visibility_option', 'id');
    }
	public function truncate()
    {
        $this->getConnection()->truncateTable($this->getMainTable());
    }
}
