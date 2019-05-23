const Encore = require('@symfony/webpack-encore');

Encore
    // the project directory where compiled assets will be stored
    .setOutputPath('public/build/')

    // the public path used by the web server to access the previous directory
    .setPublicPath('/build')

    // .enableSourceMaps(!Encore.isProduction())
    // uncomment to create hashed filenames (e.g. app.abc123.css)
    // .enableVersioning(Encore.isProduction())

    // uncomment to define the assets of the project
    .addEntry('js/app', './assets/js/app.js')
    .addEntry('js/form.basic', './assets/js/views/form.basic.js')
    .addEntry('js/form.fields', './assets/js/views/form.fields.js')
    .addEntry('js/form.selector', './assets/js/views/form.selector.js')
    .addEntry('js/catalogs/elements/form', './assets/js/catalogs/elements/form.js')
    .addEntry('js/storage/storage/index', './assets/js/storage/storage/index.js')
    .addEntry('js/training/tests/form', './assets/js/training/tests/form.js')

    .addStyleEntry('css/app', './assets/css/app.scss')
    .addStyleEntry('css/storage/storage/index', './assets/css/storage/storage/index.scss')
    .addStyleEntry('css/training/tests/form', './assets/css/training/tests/form.scss')
    .addStyleEntry('css/catalogs/elements/index', './assets/css/catalogs/elements/index.scss')

    // empty the outputPath dir before each build
    .cleanupOutputBeforeBuild()

    // show OS notifications when builds finish/fail
    // .enableBuildNotifications()

    // uncomment if you use Sass/SCSS files
    .enableSassLoader()

    .enableSingleRuntimeChunk()

    // uncomment for legacy applications that require $/jQuery as a global variable
    .autoProvidejQuery()

    // this creates a 'vendor.js' file with jquery and the bootstrap JS module
    // these modules will *not* be included in page1.js or page2.js anymore
    .createSharedEntry('vendor', './assets/js/shared.js')
;

module.exports = Encore.getWebpackConfig();
