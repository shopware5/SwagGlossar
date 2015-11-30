<?php
/**
 * Shopware 4.0
 * Copyright © 2012 shopware AG
 *
 * According to our dual licensing model, this program can be used either
 * under the terms of the GNU Affero General Public License, version 3,
 * or under a proprietary license.
 *
 * The texts of the GNU Affero General Public License with an additional
 * permission and of our proprietary license can be found at and
 * in the LICENSE file you have received along with this program.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License for more details.
 *
 * "Shopware" is a registered trademark of shopware AG.
 * The licensing of the program under the AGPLv3 does not imply a
 * trademark license. Therefore any rights, title and interest in
 * our trademarks remain entirely with us.
 */

/**
 * Shopware Glossar Plugin - Bootstrap
 *
 * @category  Shopware
 * @package   Shopware\Plugins\SwagGlossar
 * @copyright Copyright (c) 2012, shopware AG (http://www.shopware.de)
 */

use Shopware\SwagGlossar\Subscriber\Javascript;
use Shopware\SwagGlossar\Subscriber\Less;

class Shopware_Plugins_Frontend_SwagGlossar_Bootstrap extends Shopware_Components_Plugin_Bootstrap
{
    /**
     * Returns the meta information about the plugin
     * as an array.
     * Keep in mind that the plugin description located
     * in the info.txt.
     *
     * @return array
     */
    public function getInfo()
    {
        return array(
            'version' => $this->getVersion(),
            'label' => $this->getLabel(),
            'link' => 'http://www.shopware.de/',
            'description' => file_get_contents($this->Path() . 'info.txt')
        );
    }

    /**
     * Returns the version of the plugin as a string
     *
     * @return string
     * @throws Exception
     */
    public function getVersion()
    {
        $info = json_decode(file_get_contents(__DIR__ . DIRECTORY_SEPARATOR . 'plugin.json'), true);

        if ($info) {
            return $info['currentVersion'];
        } else {
            throw new Exception('The plugin has an invalid version file.');
        }
    }

    /**
     * Returns the well-formatted name of the plugin
     * as a sting
     *
     * @return string
     */
    public function getLabel()
    {
        return 'Glossar';
    }

    /**
     * Install method for this plugin
     * Define new frontend module component
     * Define new backend module component
     * Set a hook on sGetArticleById to dynamically replace product description
     * Also hook into a dispatch event, to inject the used js library on product detail page
     * Creates a new database table to save terms and add new backend module into backend menu
     *
     * @return boolean true
     */
    public function install()
    {
        $this->createForm();
        $this->subscribeEvents();
        $this->createMenu();
        $this->createDatabase();

        return array('success' => true, 'invalidateCache' => array('theme'));
    }

    /**
     * Plugin update method to handle the update process
     *
     * @param string $oldVersion
     * @return bool
     */
    public function update($oldVersion)
    {
        if (version_compare($oldVersion, '1.3.2', '<')) {
            try {
                $sql = "ALTER TABLE s_plugin_glossar ADD storeID INT NOT NULL";
                Shopware()->Db()->query($sql, array());
            } catch (\Exception $e) {
                // This column might already exist - so ignore possible exceptions
            }
        }

        return true;
    }

    /**
     * Uninstalls the menu-item
     *
     * @return bool|void
     */
    public function uninstall()
    {
        $sql = "DELETE FROM s_cms_static
                WHERE link='GlossarInventory'";
        Shopware()->Db()->query($sql);

        return parent::uninstall();
    }

    /**
     * Method to always register the custom models and the namespace for the auto-loading
     */
    public function afterInit()
    {
        $this->Application()->Loader()->registerNamespace('Shopware\SwagGlossar', $this->Path());
    }

    /**
     * Main entry point for the bonus system: Registers various subscribers to hook into shopware
     */
    public function onStartDispatch()
    {
        if ($this->assertMinimumVersion('5.0.0')) {
            $subscribers = array(
                new Less(),
                new Javascript(),
            );

            foreach ($subscribers as $subscriber) {
                $this->get('events')->addSubscriber($subscriber);
            }
        }
    }

    /**
     * creates the plugin configuration form
     */
    private function createForm()
    {
        $form = $this->Form();
        $form->setElement(
            'text',
            'position',
            array(
                'label' => 'Position der Beschreibung (möglich sind top, bottom, left, right)',
                'value' => 'bottom',
                'scope' => Shopware\Models\Config\Element::SCOPE_SHOP
            )
        );
        $form->setElement(
            'text',
            'width',
            array(
                'required' => true,
                'label' => 'Weite (in px)',
                'value' => '200',
                'scope' => Shopware\Models\Config\Element::SCOPE_SHOP
            )
        );
        $form->setElement(
            'color',
            'color',
            array(
                'required' => true,
                'label' => 'Farbe der Frontend-Übersicht',
                'value' => '#999',
                'scope' => Shopware\Models\Config\Element::SCOPE_SHOP
            )
        );
        $form->setElement(
            'boolean',
            'caseSensitive',
            array(
                'required' => true,
                'label' => 'Groß- und Kleinschreibung beachten',
                'scope' => Shopware\Models\Config\Element::SCOPE_SHOP
            )
        );

        $translations = array(
            'en_GB' => array(
                'position' => array('label' => 'Position of the description (possibilities: top, bottom, left, right)'),
                'width' => array('label' => 'Width (in px)'),
                'color' => array('label' => 'Color in the frontend overview'),
                'caseSensitive' => array('label' => 'Consider upper and lowercase')
            )
        );
        // In 4.2.2 we introduced a helper function for this, so we can skip the custom logic
        if ($this->assertMinimumVersion('4.2.2')) {
            $this->addFormTranslations($translations);
        } else {
            $this->translateForm($translations);
        }
    }

    /**
     * translates the plugin configuration form with the given translations
     *
     * @param $translations
     */
    private function translateForm($translations)
    {
        // Translations
        $shopRepository = Shopware()->Models()->getRepository('\Shopware\Models\Shop\Locale');

        //iterate the languages
        foreach ($translations as $locale => $snippets) {
            $localeModel = $shopRepository->findOneBy(array('locale' => $locale));

            //not found? continue with next language
            if ($localeModel === null) {
                continue;
            }

            if ($snippets['plugin_form']) {
                // Translation for form description
                $formTranslation = null;
                /* @var \Shopware\Models\Config\FormTranslation $translation */
                foreach ($this->form->getTranslations() as $translation) {
                    if ($translation->getLocale()->getLocale() == $locale) {
                        $formTranslation = $translation;
                    }
                }

                // If none found create a new one
                if (!$formTranslation) {
                    $formTranslation = new \Shopware\Models\Config\FormTranslation();
                    $formTranslation->setLocale($localeModel);
                    //add the translation to the form
                    $this->form->addTranslation($formTranslation);
                }

                if ($snippets['plugin_form']['label']) {
                    $formTranslation->setLabel($snippets['plugin_form']['label']);
                }

                if ($snippets['plugin_form']['description']) {
                    $formTranslation->setDescription($snippets['plugin_form']['description']);
                }

                unset($snippets['plugin_form']);
            }

            //iterate all snippets of the current language
            foreach ($snippets as $element => $snippet) {
                $translationModel = null;
                //get the form element by name
                $elementModel = $this->form->getElement($element);

                //not found? continue with next snippet
                if ($elementModel === null) {
                    continue;
                }

                // Try to load existing translation
                foreach ($elementModel->getTranslations() as $translation) {
                    if ($translation->getLocale()->getLocale() == $locale) {
                        $translationModel = $translation;
                        break;
                    }
                }

                // If none found create a new one
                if (!$translationModel) {
                    $translationModel = new \Shopware\Models\Config\ElementTranslation();
                    $translationModel->setLocale($localeModel);
                    //add the translation to the form element
                    $elementModel->addTranslation($translationModel);
                }

                if ($snippet['label']) {
                    $translationModel->setLabel($snippet['label']);
                }

                if ($snippet['description']) {
                    $translationModel->setDescription($snippet['description']);
                }
            }
        }
    }

    /**
     * subscribes the plugin events
     */
    private function subscribeEvents()
    {
        $this->subscribeEvent('Enlight_Controller_Action_PostDispatch', 'onPostDispatch', 1);
        $this->subscribeEvent(
            'Enlight_Controller_Dispatcher_ControllerPath_Frontend_GlossarInventory',
            'onGetControllerPathFrontend'
        );
        $this->subscribeEvent(
            'Enlight_Controller_Dispatcher_ControllerPath_Backend_Glossar',
            'onGetControllerPathBackend'
        );
        $this->subscribeEvent('sArticles::sGetArticleById::after', 'onGetArticle');
        $this->subscribeEvent('Enlight_Controller_Action_PostDispatch_Frontend_Detail', 'onPostDispatchDetail');
        $this->subscribeEvent('Enlight_Controller_Front_StartDispatch', 'onStartDispatch');
    }

    /**
     * creates the backend menu item
     */
    private function createMenu()
    {
        $parent = $this->Menu()->findOneBy(array('label' => 'Marketing'));

        $this->createMenuItem(
            array(
                'label' => 'Glossar',
                'controller' => 'Glossar',
                'class' => 'sprite-book-open-bookmark',
                'action' => 'Index',
                'active' => 1,
                'parent' => $parent
            )
        );
    }

    /**
     * creates the database tables for the plugin
     */
    private function createDatabase()
    {
        $sql = "CREATE TABLE IF NOT EXISTS `s_plugin_glossar` (
					`id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY ,
					`keywords` VARCHAR( 255 ) NOT NULL ,
					`glossar` TEXT NOT NULL ,
					`storeID` INT(11) NOT NULL
				) ENGINE = INNODB ;";
        Shopware()->Db()->query($sql);

        //To prevent re-inserting, when this entry is already set
        $sql = "SELECT id
				FROM s_cms_static
				WHERE link='GlossarInventory'";
        $exist = Shopware()->Db()->fetchOne($sql, array());

        if (empty($exist)) {
            $sql = "INSERT INTO s_cms_static
                	SET description = 'Glossar', grouping = 'gLeft', link = 'GlossarInventory'";
            Shopware()->Db()->query($sql);
        }
    }

    /**
     * Dispatcher to inject tiptip js (common jquery tooltip plugin)
     *
     * @param Enlight_Event_EventArgs $args
     */
    public function onPostDispatchDetail(Enlight_Event_EventArgs $args)
    {
        $isEmotion = Shopware()->Shop()->getTemplate()->getVersion() < 3;
        if ($isEmotion) {
            $args->getSubject()->View()->addTemplateDir(dirname(__FILE__) . "/Views/_emotion/");
            $args->getSubject()->View()->extendsTemplate('frontend/plugins/swag_glossar/blocks_detail.tpl');
        }
    }

    /**
     * PostDispatch to load the new template, so a new option will be added to the information-menu
     * and the new frontend controller has a template
     * Loads the plugin configs
     *
     * @static
     * @param Enlight_Event_EventArgs $args
     * @return void
     */
    public function onPostDispatch(Enlight_Event_EventArgs $args)
    {
        $request = $args->getSubject()->Request();
        $response = $args->getSubject()->Response();
        if (!$request->isDispatched() || $response->isException() || $request->getModuleName() != 'frontend') {
            return;
        }

        $config = Shopware()->Plugins()->Frontend()->SwagGlossar()->Config();

        $configs = array();
        $configs['position'] = $config->position;
        $configs['width'] = $config->width;
        $configs['color'] = $config->color;

        $args->getSubject()->View()->configs = $configs;
    }

    /**
     * Event - Listener for new backend controller
     *
     * @param Enlight_Event_EventArgs $args
     * @return string Returns the path to the backend-controller
     */
    public function onGetControllerPathBackend(Enlight_Event_EventArgs $args)
    {
        $this->Application()->Template()->addTemplateDir($this->Path() . 'Views/', 'glossar');

        return $this->Path() . 'Controllers/Backend/Glossar.php';
    }

    /**
     * Event - Listener for the new frontend controller
     *
     * @param Enlight_Event_EventArgs $args
     * @return string Returns the path to the frontend-controller
     */
    public function onGetControllerPathFrontend(Enlight_Event_EventArgs $args)
    {
        return $this->Path() . 'Controllers/Frontend/GlossarInventory.php';
    }

    /**
     * Hook on sGetArticleById to get product description
     * Parse description and set a tooltip around all matching terms
     *
     * @param $args Enlight_Hook_HookArgs
     * @return array|mixed
     */
    public function onGetArticle(Enlight_Hook_HookArgs $args)
    {
        //Get return
        $article = $args->getReturn();

        $config = Shopware()->Plugins()->Frontend()->SwagGlossar()->Config();

        $description = &$article["description_long"];
        $storeId = Shopware()->Shop()->getId();

        // Read the glossar
        $getGlossar = Shopware()->Db()->fetchAll(
            "SELECT keywords, glossar
            FROM s_plugin_glossar
            WHERE storeID = ?",
            array($storeId)
        );

        foreach ($getGlossar as $glossar) {
            $keywords = explode("|", $glossar['keywords']);
            foreach ($keywords as $keyword) {
                $description = $this->addGlossaryWords($description, $keyword, $glossar, $config);
                $newKeyword = htmlentities($keyword);
                //Needed to prevent double matching of words, which don't even contain a letter, that gets converted by htmlentites
                //"Car" would get replaced twice without the if
                if ($newKeyword != $keyword) {
                    $description = $this->addGlossaryWords($description, $newKeyword, $glossar, $config);
                }
            }
        }

        return $article;
    }

    /**
     * Helper function to replace the keywords with glossary text and
     * return the edited description
     *
     * @param $description string Contains the description
     * @param $keyword string Contains the keyword to be checked
     * @param $glossar array Contains all keywords
     * @param $config array Contains all plugin configurations
     * @return string Returns the edited description
     */
    protected function addGlossaryWords($description, $keyword, $glossar, $config)
    {
        $regex = '/\b' . quotemeta($keyword) . '(?!([^<]+)?>)\b/';
        if (!$config->caseSensitive) {
            $caseSensitiveLetter = 'i';
            $regex .= $caseSensitiveLetter;
        }
        preg_match_all($regex, $description, $matches);

        if ($matches[0][0]) {
            $style = 'font-weight:bold;text-decoration:underline;cursor:pointer;';
            $title = htmlspecialchars($glossar["glossar"]);

            $regexSearch = '/<span class="tiptip".+>' . quotemeta($keyword) . '/' . $caseSensitiveLetter;
            if (!preg_match($regexSearch, $description)) {
                $description = preg_replace(
                    "/(\b($keyword)(?!([^<]+)?>)\b)/" . $caseSensitiveLetter,
                    '<span class="tiptip" style="' . $style . '" data-width="' . $config->width . '" data-position="' . $config->position . '" title="' . $title . '">' . '\\2' . "</span>",
                    $description
                );
            }
        }

        return $description;
    }
}
