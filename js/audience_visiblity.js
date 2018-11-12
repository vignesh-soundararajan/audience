(function ($, Drupal, drupalSettings) {

Drupal.behaviors.AudienceVisibilityBehavior = {
  attach: function (context, settings) {
    // can access setting from 'drupalSettings';
    var userflag = drupalSettings.audience_visiblity.user_flag;
    if(userflag) {
        $('#block-form').find('#edit-settings-label--description').hide();
    }
  }
};
})(jQuery, Drupal, drupalSettings);
