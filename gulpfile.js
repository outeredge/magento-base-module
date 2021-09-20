const gulp = require('gulp')
const browsersync = require('browser-sync').create();

exports.browserSync = function(done) {
    var runtimeUrl = process.env.RUNTIME_URL.replace('https://', '').replace(/\/$/, '');

    browsersync.init({
    watchOptions: {
        ignored: '*.map.css'
    },
    files: ['app/design/frontend/**/*.css'],
    host: runtimeUrl.replace('8080-', '3000-'),
    open: false,
    ui: false,
    proxy: {
        target: 'localhost:8080',
        proxyOptions: {
            changeOrigin: false
        },
        reqHeaders: function (config) {
            return {
                'host': runtimeUrl,
            };
        }
    },
    rewriteRules: [
        {
            match: /8080/g,
            replace: '3000'
        }
    ],
    port: 3000,
    socket: {
        port: 443
    }
    });
    done();
}
