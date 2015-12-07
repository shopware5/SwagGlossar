<?php
/*
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 */

/**
 * Shopware Glossar Plugin - GlossarInventory Frontend Controller
 *
 * @category  Shopware
 * @package   Shopware\Plugins\SwagGlossar\Controllers\Frontend\GlossarInventory
 * @copyright Copyright (c) 2012, shopware AG (http://www.shopware.de)
 */
class Shopware_Controllers_Frontend_GlossarInventory extends Enlight_Controller_Action
{
    /**
     * Adds the standard template directory 'Views' to use the new templates
     *
     * @return void
     */
    public function init()
    {
        $isEmotion = Shopware()->Shop()->getTemplate()->getVersion() < 3;
        if ($isEmotion) {
            $template = '_emotion/';
        } else {
            $template = 'responsive/';
        }

        $this->View()->addTemplateDir(dirname(__FILE__) . "/../../Views/" . $template);
    }

    /**
     * Creates an array with the whole alphabet and german umlauts
     * Sends the alphabet and the glossar-results to the view
     *
     * @return void
     */
    public function indexAction()
    {
        $this->View()->loadTemplate("frontend/index/glossarInventory.tpl");
        $alphabet = $this->createAlphabet();

        $this->View()->alphabet = $alphabet;
        $storeId = Shopware()->Shop()->getId();
        $sql = "SELECT keywords, glossar
				FROM s_plugin_glossar
				WHERE storeID = ?
				ORDER BY keywords ASC";
        $results = Shopware()->Db()->fetchPairs($sql, array($storeId));
        $glossarArray = array();
        foreach ($alphabet as $letter) {
            foreach ($results as $keysString => $value) {
                $keys = explode("|", $keysString);

                foreach ($keys as $key) {
                    $key = strip_tags($key);

                    if (mb_strtolower($letter) == mb_strtolower(mb_substr($key, 0, 1))) {
                        if (strlen($key) > 15) {
                            $key = substr($key, 0, 17);
                            $key = $key . ' ...';
                        }

                        $glossarArray[$letter][] = array("key" => ($key), "value" => wordwrap($value, 25, "\n", true));
                    }
                }
            }
        }

        $this->View()->results = $glossarArray;
    }

    /**
     * helper method to generate the alphabet based on the ascii table
     *
     * @return array
     */
    public function createAlphabet()
    {
        for ($i = 65; $i <= 90; $i++) {
            $alphabet[chr($i)] = chr($i);
        }
        $alphabet["&Auml;"] = "Ä";
        $alphabet["&Ouml;"] = "Ö";
        $alphabet["&Uuml;"] = "Ü";

        return $alphabet;
    }
}
