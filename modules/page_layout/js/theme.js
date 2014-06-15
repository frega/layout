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
      'href="' + path + '" role="button" aria-label="' + Drupal.checkPlain(d.ariaLabel) + '" class="' + Drupal.checkPlain(d['class']) + ' ajax">' + Drupal.checkPlain(title) + '</a>';
  };

  /**
   * Theme function for a region.
   * @param id
   * @param label
   * @return {String}
   */
  Drupal.theme.layoutRegion = function (id, label, attributes) {
    var actions = [];
    if (_.isArray(attributes.actions) && attributes.actions.length) {
      _.forEach(attributes.actions, function(action) {
        actions.push(Drupal.theme.layoutModalLink(action.label, action.url, action.options));
      });
    }

    var classes = [];
    classes.push('layout-region');
    classes.push('layout-region-plugin-id-' + Drupal.checkPlain(attributes.plugin_id));

    if (attributes.options['class']) {
      classes.push(attributes.options['class']);
    }

    var style = '';
    // This is a obviously a *quick & dirty hack*
    if (attributes.options.width) {
      style = ' style="float: left; width: '+attributes.options.width+'"';
    } else {
      classes.push('clearfix');
    }

    var blocks = '';
    // Unless can_add_blocks is set to false, shw the section.
    if (attributes.options.can_add_blocks !== false) {
      blocks = '<div id="layout-region-blocks-' + Drupal.checkPlain(id) + '" class="blocks"><div class="row"></div></div>';
    }

    if (attributes.options.float_blocks === false) {
      classes.push('blocks-full-width')
    }

    var html =
      '<div id="layout-region-' + Drupal.checkPlain(id) + '" class="' + classes.join(' ') + '"' + style + '>' +
        '<div class="lining">' +
          '<header class="clearfix">' +
            '<div class="info"><span class="label">'  + Drupal.checkPlain(label) + '</span></div>' +
            '<div role="form" class="operations">' +
              actions.join(' ') +
            '</div>' +
          '</header>' +
          '<div class="regions">' +
            '<div class="row"></div>' +
          '</div>' +
          blocks +
        '</div>' +
      '</div>';
    return html;
  }

  /**
   * Theme function to get the html for a block.
   * @param id
   * @param label
   * @return {String}
   */
  Drupal.theme.layoutBlock = function (id, label, attributes) {
    if (!label) {
      label = id;
    }
    return '<div class="block" id="block-' + Drupal.checkPlain(id) + '" data-uuid="' + Drupal.checkPlain(id) + '">' +
      '<div class="lining">' +
        '<div class="info">' +
          '<span class="label mb-text">' + Drupal.checkPlain(label) + '</span>' +
        '</div>' +
        '<div class="operations mb-block-operations">' +
          Drupal.theme.layoutModalLink(Drupal.t('Configure'), Drupal.url(attributes.configurePath)) +
          Drupal.theme.layoutModalLink(Drupal.t('Delete'), Drupal.url(attributes.deletePath)) +
        '</div>' +
      '</div>' +
     '</div>';
  };

})(jQuery, _, drupalSettings);
