var elixir = require('laravel-elixir');

/*
 |--------------------------------------------------------------------------
 | Elixir Asset Management
 |--------------------------------------------------------------------------
 |
 | Elixir provides a clean, fluent API for defining some basic Gulp tasks
 | for your Laravel application. By default, we are compiling the Sass
 | file for our application, as well as publishing vendor resources.
 |
 */

elixir(function (mix) {
    mix.sass(["variables.scss",
        "auth.scss",
        "base.scss",
        "breadnav.scss",
        "buttons.scss",
        "components.scss",
        "contenttabs.scss",
        "fonts.scss",
        "header.scss",
        "inputs.scss",
        "leftnav.scss",
        "mail.scss",
        "main.scss",
        "modals.scss",
        "notes.scss",
        "notifications.scss",
        "overlay.scss"],'public/assets/css', 'public/css');
});