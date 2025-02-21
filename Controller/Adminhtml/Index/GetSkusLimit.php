<?php

namespace DamConsultants\Idex\Controller\Adminhtml\Index;

use Magento\Backend\App\Action;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\App\ResourceConnection;

class GetSkusLimit extends Action
{
    protected $resultJsonFactory;
    protected $resource;

    public function __construct(
        Action\Context $context,
        JsonFactory $resultJsonFactory,
        ResourceConnection $resource
    ) {
        parent::__construct($context);
        $this->resultJsonFactory = $resultJsonFactory;
        $this->resource = $resource;
    }

    public function execute()
    {
        $result = $this->resultJsonFactory->create();
        $token = $this->getRequest()->getParam('token');

        if (!$token) {
            return $result->setData([]);
        }

        try {
            $connection = $this->resource->getConnection();
            $table = $this->resource->getTableName('bynder_update_sku');
            $sql = "SELECT id, sku, select_attribute, select_store, status FROM {$table} WHERE token = :token";
            $skuData = $connection->fetchAll($sql, ['token' => $token]);

            return $result->setData($skuData);
        } catch (\Exception $e) {
            return $result->setData(['error' => $e->getMessage()]);
        }
    }
}
