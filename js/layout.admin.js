(function ($, window, Drupal, drupalSettings) {

"use strict";

var appView;

Drupal.AjaxCommands.prototype.layoutBlockReload = function (ajax, response, status) {
  // Find the appropriate model by its id.
  var m = Drupal.layout.getComponentInstanceModelById(response.data.id);
  if (m) {
    // The views will take care of all necessary updates (unbinding, rebinding).
    m.set(response.data);
  } else {
    // Retrieve the container model
    var c = Drupal.layout.getContainerModelById(response.data.container);
    if (c) {
      // Instantiate a new component model, the container will repaint etc.
      // @todo: fix weight/order will probably be *off*
      c.get('components').add(new Drupal.layout.ComponentModel(response.data));
    }
  }
};

/**
 * Attach display editor functionality.
 */
Drupal.behaviors.displayEditor = {
  attach: function (context, settings) {
    /**
     * Bind links to open in a dialog using Drupal.ajax.
     * @param el
     */

    Drupal.layout.ajaxify = function(el) {
      // Bind Ajax behaviors to all items showing the class.
      $(el).find('.ajax').once('ajax', function () {
        var element_settings = {};
        element_settings.progress = { 'type': null };

        if ($(this).attr('href')) {
          element_settings.url = $(this).attr('href');
          element_settings.event = 'click.layout';
        }
        element_settings.accepts = $(this).data('accepts');
        element_settings.dialog = $(this).data('dialog-options');

        var base = $(this).attr('id');
        Drupal.ajax[base] = new Drupal.ajax(base, this, element_settings);
      });
    }

    /**
     * Unbind links bound to Drupal.ajax.
     * @param el
     */
    Drupal.layout.deajaxify = function(el) {
      $(el).find('.ajax').each(function() {
        var base = $(this).attr('id');
        if (Drupal.ajax[base]) {
          $(this).off('click.layout');
          delete Drupal.ajax[base]
        }
      });
    }

    /**
     * Retrieve the ComponentModel
     * @param id
     * @return {*}
     */
    Drupal.layout.getComponentInstanceModelById = function(id) {
      var m;
      Drupal.layout.appModel.get('containers').each(function(region) {
        m = m || region.get('components').get(id);
      }, this);
      return m;
    };

    Drupal.layout.getContainerModelById = function(id) {
      return Drupal.layout.appModel.get('containers').get(id);
    };

    /**
     * Generates the required Backbone Collections and Models.
     * @param layoutData
     * @return {Drupal.layout.ContainerCollection}
     */
    function generateRegionCollections(layoutData) {
      var containers = new Drupal.layout.ContainerCollection();
      _(layoutData.containers).each(function(region) {
        var components = new Drupal.layout.ComponentCollection();
        components.reset(region.components);
        containers.add(new Drupal.layout.ContainerModel({
          id: region.id,
          label: region.label,
          components: components
        }));
      });
      return containers;
    };

    // Initial attaching.
    if (!appView) {
      Drupal.layout.appModel = new Drupal.layout.AppModel({
        id: drupalSettings.layout.id,
        layout: drupalSettings.layout.layoutData.layout,
        containers: generateRegionCollections(drupalSettings.layout.layoutData)
      });
      appView = new Drupal.layout.AppView({
        model: Drupal.layout.appModel,
        el: $('#content .form-item-components'),
        locked: drupalSettings.layout.locked
      });

      // @todo: we need to do this in order to circumvent the merge-behavior of
      // Drupal.ajax on drupalSettings (which makes sense, just not here).
      drupalSettings.layout.layoutData = {};
      appView.render();
    } else {
      // Drupal.ajax has (good) reasons to call the attach function three times
      // per response (triggered by layout select menu). But we
      // need this only once and we need to make sure that the layout data is
      // replaced not merged, that's why we do this stunt. There needs to be
      // some form of making this less awkward.
      if (drupalSettings.layout.layoutData.id) {
        // Updating the model will trigger an rendering as appropriate.
        Drupal.layout.appModel.set({
          id: drupalSettings.layout.id,
          layout: drupalSettings.layout.layoutData.layout,
          containers: generateRegionCollections(drupalSettings.layout.layoutData)
        });
        // @todo: we need to do this in order to circumvent the merge-behavior of
        // Drupal.ajax on drupalSettings (which makes sense, just not here).
        drupalSettings.layout.layoutData = {};
      }
    }

  }
};

})(jQuery, window, Drupal, drupalSettings);
