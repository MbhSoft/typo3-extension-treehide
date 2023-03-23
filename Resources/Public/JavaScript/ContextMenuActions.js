/**
 * Module: TYPO3/CMS/Treehide/ContextMenuActions
 *
 * JavaScript to handle
 * @exports TYPO3/CMS/Treehide/ContextMenuActions
 */
define(["require", "exports", "jquery", "TYPO3/CMS/Core/Ajax/AjaxRequest","TYPO3/CMS/Backend/Notification","TYPO3/CMS/Backend/Viewport"], (function (e,t,$,AjaxRequest,Notification,Viewport) {
  "use strict";

  /**
   * @exports TYPO3/CMS/Treehide/ContextMenuActions
   */
  var ContextMenuActions = {};

  ContextMenuActions.hidePagesRecursive = function(table, uid) {
    var mode = this.data("mode");
    ContextMenuActions.nodesAddPlaceholder();
    new AjaxRequest(top.TYPO3.settings.ajaxUrls.treehide_hidepagesrecursive).withQueryArguments({id: uid, mode: mode}).get({cache: "no-cache"}).then(
      async e => {
        const t = await e.resolve();
        if (!0 === t.success) {
          Notification.success(t.title, t.message, 2);
          document.dispatchEvent(new CustomEvent("typo3:pagetree:refresh"));
          Viewport.ContentContainer.setUrl(top.list_frame.document.location.pathname + top.list_frame.document.location.search);
        } else {
          Notification.error(t.title, t.message, 2)
          ContextMenuActions.nodesRemovePlaceholder();
        }
      },() => {
        Notification.error("Went wrong on the server side.")
        ContextMenuActions.nodesRemovePlaceholder();
      }
    )
  }

  ContextMenuActions.nodesRemovePlaceholder = function() {
    $('.svg-tree').find('.svg-tree-loader').hide();
  }

  ContextMenuActions.nodesAddPlaceholder = function() {
    $('.svg-tree').find('.svg-tree-loader').show();
  }

  return ContextMenuActions;
}));
