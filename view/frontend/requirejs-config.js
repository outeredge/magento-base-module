var config = {
    shim: {
       'jquery/jquery-migrate': {
            init: function () {
                jQuery.migrateMute = true;
                jQuery.migrateTrace = false;
            }
        }
    }
}
