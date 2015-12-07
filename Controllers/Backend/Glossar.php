<?php
/*
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 */

/**
 * Shopware Glossar Plugin - Glossar Backend Controller
 *
 * @category  Shopware
 * @package   Shopware\Plugins\SwagGlossar\Controllers\Backend\Glossar
 * @copyright Copyright (c) 2012, shopware AG (http://www.shopware.de)
 */
class Shopware_Controllers_Backend_Glossar extends Shopware_Controllers_Backend_ExtJs
{
    /**
     * Adding local template directory to smarty template scope
     */
    public function init()
    {
        $this->View()->addTemplateDir(dirname(__FILE__) . "/../../Views/");
        parent::init();
    }

    /**
     * Add or edit new glossar items
     */
    public function insertEditGlossarAction()
    {
        try {
            $id = $this->Request()->id;
            $valueKeyword = $this->Request()->keywords;
            $valueGlossar = $this->Request()->glossar;
            $storeId = $this->Request()->shop;

            if (empty($storeId)) {
                throw new Exception("Please select a store");
            }

            if (!empty($id)) {
                $sql = "UPDATE s_plugin_glossar
						SET keywords = ?, glossar = ?
						WHERE id = ?";
                Shopware()->Db()->query($sql, array($valueKeyword, $valueGlossar, $id));
            } else {
                $sql = "INSERT INTO s_plugin_glossar (keywords,glossar,storeID)
                    	VALUES (?, ?, ?)";
                Shopware()->Db()->query($sql, array($valueKeyword, $valueGlossar, $storeId));
                $id = Shopware()->Db()->lastInsertId();
            }
            $this->View()->assign(array("success" => true, "formID" => $id));
        } catch (Exception $e) {
            $this->View()->assign(array("success" => false, "errorMsg" => $e->getMessage()));
        }
    }

    /**
     * Delete items from the grid / database
     */
    public function deleteGlossarAction()
    {
        $params = $this->Request()->getParams();

        try {
            if ($params[0]) {
                foreach ($params as $data) {
                    $sql = "DELETE FROM s_plugin_glossar
							WHERE id = ?";
                    Shopware()->Db()->query($sql, array($data['id']));
                }
            } else {
                $id = $this->Request()->id;
                $sql = "DELETE FROM s_plugin_glossar
						WHERE id = ?";
                Shopware()->Db()->query($sql, array($id));
            }
            $this->View()->assign(array("success" => true));
        } catch (Exception $e) {
            $this->View()->assign(array("success" => false, "errorMsg" => $e->getMessage()));
        }
    }

    /**
     * Get all glossar terms from the database
     */
    public function getGlossarAction()
    {
        try {
            $params = $this->Request()->getParams();
            $limit = (int) $params['limit'];
            $start = (int) $params['start'];

            $filter = $this->Request()->get('filter');
            $sort = $this->Request()->get('sort');

            if ($sort) {
                $sort = $sort[count($sort) - 1];
                $sortValue = "ORDER BY " . $sort["property"] . " " . $sort["direction"];
            } else {
                $sortValue = "ORDER BY keywords ASC";
            }

            if ($filter) {
                $filter = $filter[count($filter) - 1];
                $filterValue = "%" . $filter['value'] . "%";
                $sql = "SELECT SQL_CALC_FOUND_ROWS pg.id, keywords, glossar, cs.name as shop
						FROM s_plugin_glossar pg
                        INNER JOIN s_core_shops cs ON cs.id = pg.storeID
                        WHERE keywords LIKE :value OR glossar LIKE :value
                        {$sortValue}
                        LIMIT :start, :limit";
                $prepared = Shopware()->Db()->prepare($sql);
                $prepared->bindParam(':value', $filterValue);
            } else {
                $sql = "SELECT SQL_CALC_FOUND_ROWS pg.id, keywords, glossar, cs.name as shop
						FROM s_plugin_glossar pg
                        INNER JOIN s_core_shops cs ON cs.id = pg.storeID
                        {$sortValue}
                        LIMIT :start, :limit";
                $prepared = Shopware()->Db()->prepare($sql);
            }

            $prepared->bindParam(':start', $start, PDO::PARAM_INT);
            $prepared->bindParam(':limit', $limit, PDO::PARAM_INT);
            $prepared->execute();
            $getGlossar = $prepared->fetchAll();

            $total = Shopware()->Db()->fetchOne("SELECT FOUND_ROWS()");
            $this->View()->assign(array("success" => true, "total" => $total, "data" => $getGlossar));
        } catch (Exception $e) {
            $this->View()->assign(array("success" => false, "errorMsg" => $e->getMessage()));
        }
    }
}
