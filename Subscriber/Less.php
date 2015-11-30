<?php

namespace Shopware\SwagGlossar\Subscriber;

use Enlight\Event\SubscriberInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Shopware\Components\Theme\LessDefinition;

class Less implements SubscriberInterface
{
    public static function getSubscribedEvents()
    {
        return array(
            'Theme_Compiler_Collect_Plugin_Less' =>  'addLessFiles'
        );
    }

    /**
     * Provide the needed less files
     *
     * @return ArrayCollection
     */
    public function addLessFiles()
    {
        $less = new LessDefinition(
            //configuration
            array(),

            //less files to compile
            array(
                    dirname(__DIR__) . '/Views/responsive/frontend/_public/src/less/all.less'
            ),

            //import directory
            dirname(__DIR__)
        );

        return new ArrayCollection(array($less));
    }
}
