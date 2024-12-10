<?php
namespace DamConsultants\Idex\Api;

interface BynderCustomInterface
{
    /**
     * Custom GET API
     * @param string $keyword
     * @param string|null $role Optional role parameter
     * @param string|null $page Optional page parameter
     * @param string|null $limit Optional limit parameter
     * 
     * @return mixed
     */
    public function getSearchFromBynder($keyword, $role = null, $page = null, $limit = null);

   /**
     * Custom GET API with optional parameters
     * 
     * @param string|null $keyword Optional keyword parameter
     * @param string|null $role Optional role parameter
     * @param string|null $page Optional page parameter
     * @param string|null $limit Optional limit parameter
     * @param string|null $brand Optional brand parameter
     * 
     * @return mixed
     */
    public function getListUserPriceList($keyword = null, $role = null, $page = null, $limit = null, $brand = null);

}
