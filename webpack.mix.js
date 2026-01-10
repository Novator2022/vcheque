const mix = require('laravel-mix');

/*
 |--------------------------------------------------------------------------
 | Mix Asset Management
 |--------------------------------------------------------------------------
 |
 | Mix provides a clean, fluent API for defining some Webpack build steps
 | for your Laravel application. By default, we are compiling the Sass
 | file for the application as well as bundling up all the JS files.
 |
 */

mix.stylus('resources/assets/style.styl','public/react/app.css');
mix.react('resources/assets/app.js', 'public/react').version();
mix.copyDirectory('resources/assets/images', 'public/img')
    .copyDirectory('node_modules/semantic-ui-css/themes', 'public/themes')
    .copy('node_modules/semantic-ui-css/semantic.min.css', 'public/semantic.min.css');

;
