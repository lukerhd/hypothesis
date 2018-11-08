/* 
 * Function for returning hypothesisConfig object based on
 * configuration of drupalSettings.
 */
(function ($, Drupal, drupalSettings) {
  'use strict';

  // See: https://h.readthedocs.io/projects/client/en/latest/publishers/config/#client-behavior
  window.hypothesisConfig = function () {
    return {
      showHighlights: drupalSettings.hypothesis.defaults.highlights,
      openSidebar: drupalSettings.hypothesis.defaults.sidebar 
    };
  };

})(jQuery, Drupal, drupalSettings)