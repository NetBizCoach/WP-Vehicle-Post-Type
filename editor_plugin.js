/**
* @author Orange Room Software
* @copyright Copyright © 2010, Orange Room Software, All rights reserved.
*/

(function(){
  tinymce.create('tinymce.plugins.vehicleTemplatePlugin', {
    init: function(ed, url) {
      ed.addCommand('mcevehicleTemplate',
      function() {
        ed.windowManager.open({
          file: url + '/editor_popup.php',
          width: 450, // + parseInt(ed.getLang('vehicle_template.delta_width', 0)),
          height: 400, // + parseInt(ed.getLang('vehicle_template.delta_height', 0)),
          inline: 1
        },
        {
          plugin_url: url
        });
      });

      // Register buttons
      ed.addButton('vehicle_template', {
        title: 'Vehicle Template',
        cmd: 'mcevehicleTemplate',
        image: url + '/car.png'
      });
    },
    getInfo: function() {
      return {
        longname: 'Vehicle Template',
        author: 'Orange Room Software',
        authorurl: 'http://www.orangeroomsoftware.com',
        infourl: 'http://www.orangeroomsoftware.com/wordpress',
        version: '1.0'
      };
    }
  });
  tinymce.create('tinymce.plugins.Clear', {
    init: function(ed, url) {
      ed.addCommand('mceClearMe',
      function() {
        // Add short code to editor
        if (window.tinyMCE) {
          window.tinyMCE.execInstanceCommand('content', 'mceInsertContent', false, '<hr class="clear" />');
        }
      });

      // Register buttons
      ed.addButton('clear', {
        title: 'Clear',
        cmd: 'mceClearMe',
        image: url + '/clear.png'
      });
    }
  });

  // Register plugin
  tinymce.PluginManager.add('vehicle_template', tinymce.plugins.vehicleTemplatePlugin);
  tinymce.PluginManager.add('clear', tinymce.plugins.Clear);
})();