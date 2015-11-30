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
 * @subpackage View
 * @copyright  Copyright (c) 2012, shopware AG (http://www.shopware.de)
 * @version    $Id$
 * @author shopware AG
 */

/**
 * Shopware UI - Glossar view list
 *
 * This grid contains all glossar-keywords and its description.
 */
//{namespace name="backend/plugins/glossar/main"}
Ext.define('Shopware.apps.Glossar.view.glossar.List', {

    /**
     * Extend from the standard ExtJS 4
     * @string
     */
    extend: 'Ext.grid.Panel',
    border: 0,

    ui: 'shopware-ui',

    /**
     * Alias name for the view. Could be used to get an instance
     * of the view through Ext.widget('glossar-main-list')
     * @string
     */
    alias: 'widget.glossar-main-list',
    /**
     * The window uses a border layout, so we need to set
     * a region for the grid panel
     * @string
     */
    region: 'center',
    /**
     * The view needs to be scrollable
     * @string
     */
    autoScroll: true,

    /**
     * Sets up the ui component
     * @return void
     */
    initComponent: function () {
        var me = this;
        me.registerEvents();

        me.dockedItems = [];
        me.store = me.glossarStore;
        me.selModel = me.getGridSelModel();
        me.columns = me.getColumns();
        me.toolbar = me.getToolbar();
        me.dockedItems.push(me.toolbar);
//
//        // Add paging toolbar to the bottom of the grid panel
        me.dockedItems.push({
            dock: 'bottom',
            xtype: 'pagingtoolbar',
            displayInfo: true,
            store: me.store
        });

        me.callParent(arguments);
    },

    /**
     * Creates the selectionModel of the grid with a listener to enable the delete-button
     */
    getGridSelModel: function () {
        var selModel = Ext.create('Ext.selection.CheckboxModel', {
            listeners: {
                selectionchange: function (sm, selections) {
                    var owner = this.view.ownerCt,
                        btn = owner.down('button[action=deleteMultiple]');

                    //If no keyword is marked
                    if (btn) {
                        btn.setDisabled(selections.length == 0);
                    }
                }
            }
        });

        return selModel;
    },
    /**
     * Defines additional events which will be
     * fired from the component
     *
     * @return void
     */
    registerEvents: function () {
        this.addEvents(

                /**
                 * Event will be fired when the user clicks the delete icon in the
                 * action column
                 *
                 * @event deleteColumn
                 * @param [object] View - Associated Ext.view.Table
                 * @param [integer] rowIndex - Row index
                 * @param [integer] colIndex - Column index
                 * @param [object] item - Associated HTML DOM node
                 */
                'deleteColumn',

                /**
                 * Event will be fired when the user clicks the edit icon in the
                 * action column
                 *
                 * @event editColumn
                 * @param [object] View - Associated Ext.view.Table
                 * @param [integer] rowIndex - Row index
                 * @param [integer] colIndex - Column index
                 * @param [object] item - Associated HTML DOM node
                 */
                'editColumn'
        );

        return true;
    },

    /**
     *  Creates the columns
     */
    getColumns: function () {
        var me = this;
        var buttons = new Array();

        buttons.push(Ext.create('Ext.button.Button', {
            iconCls: 'sprite-minus-circle',
            action: 'delete',
            cls: 'delete',
            tooltip: '{s name=list/deleteEntryButton}Delete entry{/s}',
            handler: function (view, rowIndex, colIndex, item) {
                me.fireEvent('deleteColumn', view, rowIndex, item, colIndex);
            }
        }));

        buttons.push(Ext.create('Ext.button.Button', {
            iconCls: 'sprite-pencil',
            cls: 'editBtn',
            tooltip: '{s name=list/editKeywordButton}Edit keyword{/s}',
            handler: function (view, rowIndex, colIndex, item) {
                me.fireEvent('editColumn', view, item, rowIndex, colIndex);
            }
        }));

        var columns = [
            {
                header: '{s name=list/keywordHeader}Keyword{/s}',
                dataIndex: 'keywords',
                flex: 1
            },
            {
                header: '{s name=list/glossarHeader}Glossar{/s}',
                dataIndex: 'glossar',
                flex: 2
            },
            {
                header: '{s name=list/shopHeader}Shop{/s}',
                dataIndex: 'shop',
                flex: 1
            },
            {
                xtype: 'actioncolumn',
                width: 60,
                items: buttons
            }
        ];

        return columns;
    },

    /**
     * Creates the toolbar with a save-button, a delete-button and a textfield to search for keywords
     */
    getToolbar: function () {

        var searchField = Ext.create('Ext.form.field.Text', {
            name: 'searchfield',
            cls: 'searchfield',
            action: 'searchGlossar',
            width: 170,
            enableKeyEvents: true,
            emptyText: '{s name=list/searchField}Search...{/s}',
            listeners: {
                buffer: 500,
                keyup: function () {
                    if (this.getValue().length >= 3 || this.getValue().length < 1) {
                        /**
                         * @param this Contains the searchfield
                         */
                        this.fireEvent('fieldchange', this);
                    }
                }
            }
        });
        searchField.addEvents('fieldchange');
        var items = [];
        items.push(Ext.create('Ext.button.Button', {
            iconCls: 'sprite-plus-circle',
            text: '{s name=list/addKeywordButton}Add Keyword/Glossar{/s}',
            action: 'add'
        }));
        items.push(Ext.create('Ext.button.Button', {
            iconCls: 'sprite-minus-circle',
            text: '{s name=list/deleteKeywordsButton}Delete selected keywords{/s}',
            disabled: true,
            action: 'deleteMultiple'
        }));

        items.push('->');
        items.push(searchField);
        items.push({
            xtype: 'tbspacer',
            width: 6
        });

        var toolbar = Ext.create('Ext.toolbar.Toolbar', {
            dock: 'top',
            ui: 'shopware-ui',
            items: items
        });
        return toolbar;
    }

});