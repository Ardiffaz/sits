var Encore = require('@symfony/webpack-encore');

Encore
    .setOutputPath('public/build/')
    .setPublicPath('/build')
    .addEntry('main', './frontend/main.js')
    .splitEntryChunks()

    .enableSingleRuntimeChunk()
    //.disableSingleRuntimeChunk()

    .cleanupOutputBeforeBuild()
    .enableBuildNotifications()
    .enableSourceMaps(!Encore.isProduction())
    .enableVersioning(Encore.isProduction())

    .configureBabel(() => {}, {
        useBuiltIns: 'usage',
        corejs: 3
    })
    .enablePostCssLoader( (options) => {
        options.config = {
            path: './'
        }
    })
    .enableLessLoader((options) => {
        options.math = ['parens-division'];
    })
    .enableVueLoader()
;

module.exports = Encore.getWebpackConfig();
