var googletag = googletag || {};
googletag.cmd = googletag.cmd || [];
(function() {
    var gads = document.createElement("script");
    gads.async = true;
    gads.type = "text/javascript";
    var useSSL = "https:" == document.location.protocol;
    gads.src = (useSSL ? "https:" : "http:") + "//www.googletagservices.com/tag/js/gpt.js";
    var node =document.getElementsByTagName("script")[0];
    node.parentNode.insertBefore(gads, node);
})();

var WPAdUtility = function() {
    this.network_code = '';
    this.targeting = [];
    this.divs_initialized = [];
    this.log_level = 0;

    this.init = function() {
        if ( window.wp_adutility_page_options == 'none' ) {
            window.WPAdUtility.log('init(): Post/page has ads disabled (window.wp_adutility_page_options = none).');

            return true;
        }

        googletag.cmd.push(function() {
            googletag.pubads().enableSingleRequest();
            //googletag.pubads().collapseEmptyDivs(true);
            googletag.enableServices();
        });

        googletag.cmd.push(function() {    
            var arr_targeting = window.WPAdUtility.targeting;
            for ( var i = 0; i < arr_targeting.length; i++ ) {
                var key = Object.keys(arr_targeting[i]);
                var val = arr_targeting[i][key];

                googletag.pubads().setTargeting(key[0], [val]);

                window.WPAdUtility.log('init(): Key-value key = ' + key[0] + ' set to a value = ' + [val]);
            }
        });
    }

    this.displayAd = function( ad_unit, div_id, creative_size, slot_targets ) {
        var str_adunit = "/" + this.network_code + "/" + ad_unit;

        if ( window.wp_adutility_page_options == 'none' ) {
            window.WPAdUtility.log('displayAd(): Post/page has ads disabled (window.wp_adutility_page_options = none).');

            return true;
        }

        if ( window.WPAdUtility.inArray(div_id, window.WPAdUtility.divs_initialized) == true ) {
            window.WPAdUtility.log('displayAd(): Aborting, already called googletag.display() on #' + div_id);

            return true;
        }
        window.WPAdUtility.divs_initialized.push(div_id);

        var arr_creative_sizes_allowed = window.WPAdUtility.filterOversizeAdSizes(creative_size);

        googletag.cmd.push(function() {
            window.WPAdUtility.log('displayAd(): About to call googletag.defineSlot() for ad unit ' + str_adunit + ' at size [' + arr_creative_sizes_allowed + '] on ID #' + div_id);

            var ad_slot = googletag.defineSlot(str_adunit, arr_creative_sizes_allowed, div_id).addService(googletag.pubads());

            if ( typeof slot_targets != 'undefined' ) {
                for ( var i = 0; i < slot_targets.length; i++ ) {
                    ad_slot.setTargeting(slot_targets[i][0], slot_targets[i][1]);

                    window.WPAdUtility.log('displayAd(): Set a slot target for ID #' + div_id + ' with a key of ' + slot_targets[i][0] + ' and a value of ' + slot_targets[i][1]);
                }
            }

            googletag.display(div_id);

            window.WPAdUtility.log('displayAd(): Called googletag.display() on #' + div_id);
        });
    }

    this.setNetworkCode = function() {        
        window.WPAdUtility.log('setNetworkCode(): Using a Google Ad Manager network code of: ' + window.wp_adutility_network_code);

        return window.wp_adutility_network_code;
    }

    this.filterOversizeAdSizes = function( arr_creative_sizes_all ) {
        var arr_creative_sizes_allowed = new Array();
        var int_width = window.innerWidth || document.documentElement.clientWidth;

        for ( var i = 0; i < arr_creative_sizes_all.length; i++ ) {
            if ( arr_creative_sizes_all[i][0] <= int_width ) {
                arr_creative_sizes_allowed.push(arr_creative_sizes_all[i]);

                window.WPAdUtility.log('displayAd(): Allowing ad size of [' + arr_creative_sizes_all[i] + ']; current browser width is ' + int_width);
            }
            else {
                window.WPAdUtility.log('displayAd(): Filtered out ad size of [' + arr_creative_sizes_all[i] + ']; current browser width is ' + int_width);
            }
        }

        return arr_creative_sizes_allowed;
    }

    this.setDefaultKeyValues = function() {
        var arr_default_key_values = new Array();

        var str_domain = window.location.host;
        str_domain = str_domain.toLowerCase();

        arr_default_key_values.push({ 'domain': str_domain });

        var str_pathname = window.location.pathname;
        str_pathname = str_pathname.toLowerCase();
        
        arr_default_key_values.push({ 'pathname': str_pathname });

        return arr_default_key_values;
    }

    this.inArray = function( needle, haystack ) {
        var length = haystack.length;

        for ( var i = 0; i < length; i++ ) {
            if (haystack[i] == needle) return true;
        }

        return false;
    }

    this.getLogLevelFromURL = function() {
        if ( window.location.search.indexOf('ad_debug=1') > -1 ) {
            return 1;
        } 
        
        return 0;
    }

    this.log = function( log_text ) {
        if ( this.log_level >= 1 ) {
            console.log('[WP Ad Utility][' + new Date().toUTCString() + '] ' + log_text);
        }
    }
};

window.WPAdUtility = new WPAdUtility();
window.WPAdUtility.log_level = window.WPAdUtility.getLogLevelFromURL();
window.WPAdUtility.network_code = window.WPAdUtility.setNetworkCode();
window.WPAdUtility.targeting = window.WPAdUtility.setDefaultKeyValues();
window.WPAdUtility.init();