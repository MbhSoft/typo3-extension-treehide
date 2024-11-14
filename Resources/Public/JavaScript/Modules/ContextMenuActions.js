/**
 * Module: TYPO3/CMS/Treehide/ContextMenuActions
 *
 * JavaScript to handle
 * @exports TYPO3/CMS/Treehide/ContextMenuActions
 */

import $ from 'jquery';
/**
 * @todo Is necessary here to import the modules with .js, should not be necessary?
 */
import AjaxRequest from '@typo3/core/ajax/ajax-request.js';
import Notification from '@typo3/backend/notification.js';
import Viewport from '@typo3/backend/viewport.js';

class ContextMenuActions {

  constructor() {
    /**
     * This binding maintains the value of this
     * inside these methods during future calls.
     * source: https://stackoverflow.com/a/43026607/2444812
     *
     * @todo should be possible to access "this." in the methods without this?
     */
    this.hidePagesRecursive = this.hidePagesRecursive.bind(this);
    this.nodesRemovePlaceholder = this.nodesRemovePlaceholder.bind(this);
    this.nodesAddPlaceholder = this.nodesAddPlaceholder.bind(this);

  }


  hidePagesRecursive(table, uid, data) {
    //var mode = this.data("mode");
    var mode = data.mode;
    this.nodesAddPlaceholder();
    new AjaxRequest(top.TYPO3.settings.ajaxUrls.treehide_hidepagesrecursive).withQueryArguments({id: uid, mode: mode}).get({cache: "no-cache"}).then(
      async e => {
        const t = await e.resolve();
        if (!0 === t.success) {
          Notification.success(t.title, t.message, 2);
          document.dispatchEvent(new CustomEvent("typo3:pagetree:refresh"));
          Viewport.ContentContainer.setUrl(top.list_frame.document.location.pathname + top.list_frame.document.location.search);
        } else {
          Notification.error(t.title, t.message, 2)
          this.nodesRemovePlaceholder();
        }
      },() => {
        Notification.error("Went wrong on the server side.")
        this.nodesRemovePlaceholder();
      }
    )
  }

  nodesRemovePlaceholder() {
    $('.svg-tree').find('.svg-tree-loader').hide();
  }

  nodesAddPlaceholder() {
    $('.svg-tree').find('.svg-tree-loader').show();
  }

}

export default new ContextMenuActions;
