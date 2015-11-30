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
 * @subpackage Controller
 * @copyright  Copyright (c) 2012, shopware AG (http://www.shopware.de)
 * @version    $Id$
 * @author shopware AG
 */

/**
 *  Glossar Controller
 *
 *  This controller handles all actions made in the module.
 *  Therefore it is responsible for saving, deleting and editing the keyword.
 *  It also handles the search.
 */
Ext.define('Shopware.apps.Glossar.controller.Glossar', {

    /**
     * Extend from the standard ExtJS 4
     * @string
     */
    extend: 'Ext.app.Controller',

    /**
     * Creates the necessary event listener for this
     * specific controller and opens a new Ext.window.Window
     * @return void
     */
    init: function () {
        var me = this;

        me.control({
            'glossar-main-list textfield[action=searchGlossar]': {
                fieldchange: me.onSearch
            },
            //The add-button on the toolbar
            'glossar-main-list button[action=add]': {
                click: me.onOpenCreateWindow
            },
            //The delete-button on the toolbar
            'glossar-main-list button[action=deleteMultiple]': {
                click: me.onDeleteMultiple
            },
            // The save-button from the create-window
            'window button[action=saveGlossar]': {
                click: me.onCreateGlossar
            },
            'glossar-main-list': {
                deleteColumn: me.onDeleteSingle,
                editColumn: me.onOpenEditWindow
            }
        });

    },

    /**
     * Opens the detail-window
     * @event click
     * @return void
     */
    onOpenCreateWindow: function () {
        this.getView('glossar.Detail').create();
    },

    /**
     * The user wants to edit a keyword
     * @event render
     * @param [object] view Contains the view
     * @param [object] item Contains the clicked item
     * @param [int] rowIndex Contains the row-index
     * @return void
     */
    onOpenEditWindow: function (view, item, rowIndex) {
        var store = this.subApplication.glossarStore,
            record = store.getAt(rowIndex);

        //Create edit-window
        this.getView('glossar.Detail').create({ record: record, mainStore: store });
    },

    /**
     * Function to create a new glossar-term
     * @event click
     * @param [object] btn Contains the clicked button
     * @return void
     */
    onCreateGlossar: function (btn) {
        var win = btn.up('window'),
            form = win.down('form'),
            values = form.getForm().getValues(),
            store = this.subApplication.glossarStore;

        if (!form.getForm().isValid()) {
            return;
        }

        var model = Ext.create('Shopware.apps.Glossar.model.Glossar', values);

        win.close();
        model.save({
            callback: function (data, operation) {
                var records = operation.getRecords(),
                    record = records[0],
                    rawData = record.getProxy().getReader().rawData;

                if (operation.success) {
                    Shopware.Notification.createGrowlMessage('Success', "The glossar was successfully created", 'Glossar');
                } else {
                    Shopware.Notification.createGrowlMessage('Error', rawData.errorMsg, 'Glossar');
                }
                store.load();
            }
        });
    },

    /**
     * Function to delete multiple keywords
     * Every marked keyword will be deleted
     * @event click
     * @param [object] btn Contains the clicked button
     * @return [boolean|null]
     */
    onDeleteMultiple: function (btn) {
        var win = btn.up('window'),
            grid = win.down('grid'),
            selModel = grid.selModel,
            store = grid.getStore(),
            selection = selModel.getSelection(),
            me = this,
            message = Ext.String.format('You have marked [0] entries. Are you sure you want to delete them?', selection.length);

        //Create a message-box, which has to be confirmed by the user
        Ext.MessageBox.confirm('Delete entries', message, function (response) {
            //If the user doesn't want to delete the keywords
            if (response !== 'yes') {
                return false;
            }

            Ext.each(selection, function (item) {
                store.remove(item);
            });
            store.sync({
                callback: function (batch, operation) {
                    var rawData = batch.proxy.getReader().rawData;
                    if (rawData.success) {
                        me.subApplication.glossarStore.load();
                        Shopware.Notification.createGrowlMessage('Success', "The entries were successfully deleted", 'Glossar');
                    } else {
                        Shopware.Notification.createGrowlMessage('Error', rawData.errorMsg, 'Glossar');
                    }
                }
            })
        });
    },

    /**
     * Function to delete one single keyword
     * Is used, when the user clicks on the delete-button in the action-column
     * @event click
     * @param [object] view Contains the view
     * @param [int] rowIndex Contains the row-index
     * @return mixed
     */
    onDeleteSingle: function (view, rowIndex) {
        var store = this.subApplication.glossarStore,
            values = store.data.items[rowIndex].data,
            message = Ext.String.format('Are you sure you want to delete <b> [0] </b> ?', values.keywords);

        //Create a message-box, which has to be confirmed by the user
        Ext.MessageBox.confirm('Delete entry', message, function (response) {
            //If the user doesn't want to delete the keyword
            if (response != 'yes') {
                return false;
            }
            var model = Ext.create('Shopware.apps.Glossar.model.Glossar', values);
            model.destroy({
                callback: function () {
                    store.load();
                }
            });
        });

    },

    /**
     * @event fieldchange
     * Function to search for keywords by using a store-filter
     * @param [object] field Contains the searchfield
     * @return void
     */
    onSearch: function (field) {
        var me = this,
            store = me.subApplication.glossarStore;

        //If the search-value is empty, reset the filter
        if (field.getValue().length == 0) {
            store.clearFilter();
        } else {
            //This won't reload the store
            store.filters.clear();
            //Loads the store with a special filter
            store.filter('searchValue', field.getValue());
        }
    }
});