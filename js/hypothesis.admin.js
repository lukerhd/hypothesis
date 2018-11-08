(function ($) {
  'use strict';

  /**
   * Provide the summary information for the Hypothesis settings vertical tabs.
   */
  Drupal.behaviors.hypothesis = {
    attach: function (context, settings) {

      // The drupalSetSummary method required for this behavior is not available
      // on the Hypothesis settings page, so we need to make sure this
      // behavior is processed only if drupalSetSummary is defined.
      if (typeof jQuery.fn.drupalSetSummary == 'undefined') {
        return;
      }
  
      $('details#edit-path', context).drupalSetSummary(function (context) {
        if (!$('textarea#edit-path-hypothesis-pages', context).val()) {
          return Drupal.t('Not restricted');
        }
        else {
          return Drupal.t('Restricted to certain pages');
        }
      });
  
      $('details#edit-content-types', context).drupalSetSummary(function (context) {
        var vals = [];
        $('input[type="checkbox"]:checked', context).each(function () {
          vals.push($.trim($(this).next('label').html()));
        });
        if (!vals.length) {
          vals.push(Drupal.t('Not restricted'));
        }
        return vals.join(', ');
      });
  
      $('details#edit-user-roles', context).drupalSetSummary(function (context) {
        var vals = [];
        $('input[type="checkbox"]:checked', context).each(function () {
          vals.push($.trim($(this).next('label').html()));
        });
        if (!vals.length) {
          vals.push(Drupal.t('Not restricted'));
        }
        return vals.join(', ');
      });

    }
  };
  
  
})(jQuery);