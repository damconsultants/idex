<?php

namespace DamConsultants\Idex\Block\Adminhtml;

use Magento\Backend\Block\Template;
use Magento\Framework\App\ResourceConnection;

class SkuList extends Template
{
    protected $_template = 'DamConsultants_Idex::sku_list.phtml';
    protected $resource;

    public function __construct(
        Template\Context $context,
        ResourceConnection $resource,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->resource = $resource;
    }

    // Fetch unique token values
    public function getTokens()
    {
        $connection = $this->resource->getConnection();
        $table = $this->resource->getTableName('bynder_update_sku');
        $sql = "SELECT DISTINCT token FROM {$table} ORDER BY token ASC";
        return $connection->fetchCol($sql);
    }

    // Fetch SKUs based on selected token
    public function getSkuData($token = null)
    {
        $connection = $this->resource->getConnection();
        $table = $this->resource->getTableName('bynder_update_sku');
        $sql = "SELECT * FROM {$table}";

        if ($token) {
            $sql .= " WHERE token = :token";
            return $connection->fetchAll($sql, ['token' => $token]);
        }

        return $connection->fetchAll($sql);
    }

    // Get Ajax URL for fetching SKUs
    public function getAjaxUrl()
    {
        return $this->getUrl('bynder/index/getskuslimit'); // Custom Ajax Controller
    }
}
