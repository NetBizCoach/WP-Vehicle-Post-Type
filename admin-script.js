(function() {
  jQuery(function() {
    String.prototype.toProperCase = function () {
      return this.replace(/\w\S*/g, function(txt){return txt.charAt(0).toUpperCase() + txt.substr(1).toLowerCase();});
    };

    // Features
    jQuery('#add-feature-button').click(function(){
      var new_item = jQuery('#add-feature-text').val().toProperCase();
      var features = jQuery("#features-data").val();
      jQuery('#features').append('<li><input type="checkbox" value="' + new_item + '" checked> ' + new_item + '</li>');
      jQuery("#features-data").val(features += '|' + new_item);
      jQuery('#add-feature-text').val('');
    });

    jQuery("#features input").live("change", function() {
      var item = jQuery(this).val();
      var features = jQuery("#features-data").val();
      if (jQuery(this).is(':checked')) {
        jQuery("#features-data").val(features += '|' + item);
      } else {
        var re = new RegExp("\\|?" + item);
        jQuery("#features-data").val(features.replace(re, ''));
      }
    });

    // Options
    jQuery('#add-option-button').click(function(){
      var new_item = jQuery('#add-option-text').val().toProperCase();
      var features = jQuery("#options-data").val();
      jQuery('#options').append('<li><input type="checkbox" value="' + new_item + '" checked> ' + new_item + '</li>');
      jQuery("#options-data").val(features += '|' + new_item);
      jQuery('#add-option-text').val('');
    });

    jQuery("#options input").live("change", function() {
      var item = jQuery(this).val();
      var features = jQuery("#options-data").val();
      if (jQuery(this).is(':checked')) {
        jQuery("#options-data").val(features += '|' + item);
      } else {
        var re = new RegExp("\\|?" + item);
        jQuery("#options-data").val(features.replace(re, ''));
      }
    });
  });
}).call(this);
