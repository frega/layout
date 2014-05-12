/**
 * @file
 * Theme functions for the layout js-app.
 */
(function ($, _, drupalSettings) {
  Drupal.theme.layoutModalLink = function(title, path, options) {
    var d = _.defaults(options || {}, {
      dialogOptions: {"width": 700},
      ariaLabel: title,
      role: 'button',
      class: 'icon',
      attributes: {}
    });
    return '<a data-dialog-options=\'' + JSON.stringify(d.dialogOptions) + '\' data-accepts="application/vnd.drupal-modal" ' +
      'href="' + Drupal.url(path) + '" role="button" aria-label="' + d.ariaLabel + '" class="' + d.class + ' ajax">' + title + '</a>';
  };

  /**
   * Theme function for a region.
   * @param id
   * @param label
   * @return {String}
   */
  Drupal.theme.layoutContainer = function (id, label, attributes) {
    var html =
      '<div id="layout-container-' + id + '" class="layout-container">' +
        '<div class="lining">' +
          '<header class="clearfix">' +
            '<div role="form" class="operations">' +
              Drupal.theme.layoutModalLink(Drupal.t('Add component'), 'admin/structure/layout/components/' + drupalSettings.layout.id + '/' + id) +
              // @note: disable for the time being.
              // Drupal.theme.layoutModalLink(Drupal.t('Configure container'), 'admin/structure/layout/containers/' + drupalSettings.layout.id + '/' + id) +
            '</div>' +
            '<div class="info"><span class="label">' + label + '</span></div>' +
          '</header>' +
          '<div class="components">' +
            '<div class="row"></div>' +
          '</div>' +
        '</div>' +
      '</div>';
    return html;
  }

  /**
   * Theme function to get the html for a component instance.
   * @param id
   * @param label
   * @return {String}
   */
  Drupal.theme.layoutComponent = function (id, label, attributes) {
    if (!label) {
      label = id;
    }
    return '<div class="component" id="component-' + id + '">' +
      '<div class="lining">' +
        '<div class="info">' +
          '<span class="label mb-text">' + label + '</span>' +
        '</div>' +
        '<div class="operations mb-component-operations">' +
          Drupal.theme.layoutModalLink(Drupal.t('Configure component'), attributes.configurePath) +
          Drupal.theme.layoutModalLink(Drupal.t('Delete component'), attributes.deletePath) +
        '</div>' +
      '</div>' +
     '</div>';
  };

})(jQuery, _, drupalSettings);
