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
 *
 * @category   Shopware
 * @package    Glossar
 * @subpackage Detail
 * @copyright  Copyright (c) 2012, shopware AG (http://www.shopware.de)
 * @version    $Id$
 * @author shopware AG
 */

/**
 * Glossar View - Detail
 *
 * This window is opened, when the user wants to edit or create a glossar-term.
 */
//{namespace name="backend/plugins/glossar/main"}
Ext.define('Shopware.apps.Glossar.view.glossar.Detail', {
    extend: 'Enlight.app.Window',
    alias: 'widget.glossar-main-detail',
    cls: 'createWindow',
    modal: true,

    layout: 'border',
    autoShow: true,
    title: '{s name=detail/glossaryDetailTitle}Glossary Detail{/s}',
    border: 0,
    width: 600,
    height: 270,
    stateful: true,
    stateId: 'shopware-glossar-detail',
    footerButton: false,

    initComponent: function () {
        var me = this;

        me.glossarForm = me.createFormPanel();
        me.dockedItems = [
            {
                xtype: 'toolbar',
                ui: 'shopware-ui',
                dock: 'bottom',
                cls: 'shopware-toolbar',
                items: me.createButtons()
            }
        ];

        me.items = [me.glossarForm];
        me.callParent(arguments);
    },

    createFormPanel: function () {
        var me = this;
        var glossarForm = Ext.create('Ext.form.Panel', {
            collapsible: false,
            split: false,
            region: 'center',
            defaults: {
                labelStyle: 'font-weight: 700; text-align: right;',
                labelWidth: 130,
                anchor: '100%'
            },
            bodyPadding: 10,
            items: [
                {
                    xtype: 'textfield',
                    name: 'keywords',
                    fieldLabel: '{s name=detail/keywordFieldLabel}Keyword{/s}',
                    supportText: '{s name=detail/keywordSupportText}You may list more than one keyword by separating the keywords with a |{/s}',
                    allowBlank: false,
                    required: true
                },
                {
                    xtype: 'textarea',
                    name: 'glossar',
                    fieldLabel: '{s name=detail/glossaryFieldLabel}Glossary{/s}',
                    allowBlank: false,
                    required: true
                },
                {
                    xtype: 'combo',
                    name: 'shop',
                    fieldLabel: '{s name=detail/shopFieldLabel}Shop{/s}',
                    store: Ext.create('Shopware.apps.Glossar.store.Shop').load(),
                    displayField: 'name',
                    valueField: 'id',
                    emptyText: 'Please select',
                    allowBlank: false,
                    required: true,
                    editable: false

                },
                {
                    xtype: 'hidden',
                    name: 'id'
                }
            ]
        });

        if (me.record) {
            glossarForm.loadRecord(me.record);
        }

        return glossarForm;
    },

    createButtons: function () {
        var me = this;
        var buttons = ['->',
            {
                text: '{s name=detail/cancelButton}Cancel{/s}',
                cls: 'secondary',
                scope: me,
                handler: me.destroy
            },
            {
                text: '{s name=detail/saveButton}Save{/s}',
                action: 'saveGlossar',
                cls: 'primary'
            }
        ];

        return buttons;
    }
});