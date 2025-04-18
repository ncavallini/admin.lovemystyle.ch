/*! @preserve
 * bootbox.locales.js
 * version: 6.0.0
 * author: Nick Payne <nick@kurai.co.uk>
 * license: MIT
 * http://bootboxjs.com/
 */
(function(global, factory) {
    'use strict';
    if (typeof define === 'function' && define.amd) {
      define(['bootbox'], factory);
    } else if (typeof module === 'object' && module.exports) {
      factory(require('./bootbox'));
    } else {
      factory(global.bootbox);
    }
  }(this, function(bootbox) {
    'use strict';
    // locale : Italian
    // author : Mauro
    bootbox.addLocale('it', {
      OK: 'OK',
      CANCEL: 'Annulla',
      CONFIRM: 'Conferma'
    });
  
  }));
  