/* global wp, jQuery */
(function ($, wp) {
  if (! wp || ! wp.media || ! wp.media.view) { return; }

  // Custom filter dropdown for the media grid view
  var GcpFolderFilter = wp.media.view.AttachmentFilters.extend({
    id: 'gcp-folder-filter',
    createFilters: function () {
      this.filters = {
        all: {
          text: gcpMediaFilter.labels.all,
          props: { gcp_media_folder: '' },
          priority: 10
        },
        produkte: {
          text: gcpMediaFilter.labels.produkte,
          props: { gcp_media_folder: 'produkte' },
          priority: 20
        },
        other: {
          text: gcpMediaFilter.labels.other,
          props: { gcp_media_folder: 'other' },
          priority: 30
        }
      };
    }
  });

  // Extend the attachments browser to inject our filter into the toolbar
  var OrigBrowser = wp.media.view.AttachmentsBrowser;
  wp.media.view.AttachmentsBrowser = OrigBrowser.extend({
    createToolbar: function () {
      OrigBrowser.prototype.createToolbar.apply(this, arguments);
      this.toolbar.set('gcpFolderFilter', new GcpFolderFilter({
        controller: this.controller,
        model:      this.collection.props,
        priority:   -75
      }).render());
    }
  });
}(jQuery, wp));
