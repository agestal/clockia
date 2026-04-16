<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Title
    |--------------------------------------------------------------------------
    |
    | Here you can change the default title of your admin panel.
    |
    | For detailed instructions you can look the title section here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Basic-Configuration
    |
    */

    'title' => 'Clockia',
    'title_prefix' => '',
    'title_postfix' => '',

    /*
    |--------------------------------------------------------------------------
    | Favicon
    |--------------------------------------------------------------------------
    |
    | Here you can activate the favicon.
    |
    | For detailed instructions you can look the favicon section here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Basic-Configuration
    |
    */

    'use_ico_only' => false,
    'use_full_favicon' => false,

    /*
    |--------------------------------------------------------------------------
    | Google Fonts
    |--------------------------------------------------------------------------
    |
    | Here you can allow or not the use of external google fonts. Disabling the
    | google fonts may be useful if your admin panel internet access is
    | restricted somehow.
    |
    | For detailed instructions you can look the google fonts section here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Basic-Configuration
    |
    */

    'google_fonts' => [
        'allowed' => false,
    ],

    /*
    |--------------------------------------------------------------------------
    | Admin Panel Logo
    |--------------------------------------------------------------------------
    |
    | Here you can change the logo of your admin panel.
    |
    | For detailed instructions you can look the logo section here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Basic-Configuration
    |
    */

    'logo' => 'Admin',
    'logo_img' => 'vendor/adminlte/dist/img/AdminLTELogo.png',
    'logo_img_class' => 'brand-image elevation-0',
    'logo_img_xl' => null,
    'logo_img_xl_class' => 'brand-image-xs',
    'logo_img_alt' => 'Clockia Logo',

    /*
    |--------------------------------------------------------------------------
    | Authentication Logo
    |--------------------------------------------------------------------------
    |
    | Here you can setup an alternative logo to use on your login and register
    | screens. When disabled, the admin panel logo will be used instead.
    |
    | For detailed instructions you can look the auth logo section here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Basic-Configuration
    |
    */

    'auth_logo' => [
        'enabled' => false,
        'img' => [
            'path' => 'vendor/adminlte/dist/img/AdminLTELogo.png',
            'alt' => 'Auth Logo',
            'class' => '',
            'width' => 50,
            'height' => 50,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Preloader Animation
    |--------------------------------------------------------------------------
    |
    | Here you can change the preloader animation configuration. Currently, two
    | modes are supported: 'fullscreen' for a fullscreen preloader animation
    | and 'cwrapper' to attach the preloader animation into the content-wrapper
    | element and avoid overlapping it with the sidebars and the top navbar.
    |
    | For detailed instructions you can look the preloader section here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Basic-Configuration
    |
    */

    'preloader' => [
        'enabled' => false,
        'mode' => 'fullscreen',
        'img' => [
            'path' => 'vendor/adminlte/dist/img/AdminLTELogo.png',
            'alt' => 'AdminLTE Preloader Image',
            'effect' => 'animation__shake',
            'width' => 60,
            'height' => 60,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | User Menu
    |--------------------------------------------------------------------------
    |
    | Here you can activate and change the user menu.
    |
    | For detailed instructions you can look the user menu section here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Basic-Configuration
    |
    */

    'usermenu_enabled' => true,
    'usermenu_header' => false,
    'usermenu_header_class' => 'bg-primary',
    'usermenu_image' => false,
    'usermenu_desc' => false,
    'usermenu_profile_url' => false,

    /*
    |--------------------------------------------------------------------------
    | Layout
    |--------------------------------------------------------------------------
    |
    | Here we change the layout of your admin panel.
    |
    | For detailed instructions you can look the layout section here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Layout-and-Styling-Configuration
    |
    */

    'layout_topnav' => null,
    'layout_boxed' => null,
    'layout_fixed_sidebar' => null,
    'layout_fixed_navbar' => null,
    'layout_fixed_footer' => null,
    'layout_dark_mode' => null,

    /*
    |--------------------------------------------------------------------------
    | Authentication Views Classes
    |--------------------------------------------------------------------------
    |
    | Here you can change the look and behavior of the authentication views.
    |
    | For detailed instructions you can look the auth classes section here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Layout-and-Styling-Configuration
    |
    */

    'classes_auth_card' => 'card-outline card-secondary shadow-sm',
    'classes_auth_header' => '',
    'classes_auth_body' => '',
    'classes_auth_footer' => '',
    'classes_auth_icon' => '',
    'classes_auth_btn' => 'btn-flat btn-primary',

    /*
    |--------------------------------------------------------------------------
    | Admin Panel Classes
    |--------------------------------------------------------------------------
    |
    | Here you can change the look and behavior of the admin panel.
    |
    | For detailed instructions you can look the admin panel classes here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Layout-and-Styling-Configuration
    |
    */

    'classes_body' => 'text-sm',
    'classes_brand' => 'border-0',
    'classes_brand_text' => 'font-weight-semibold',
    'classes_content_wrapper' => '',
    'classes_content_header' => '',
    'classes_content' => '',
    'classes_sidebar' => 'sidebar-dark-primary elevation-0',
    'classes_sidebar_nav' => 'nav-compact',
    'classes_topnav' => 'navbar-white navbar-light border-bottom',
    'classes_topnav_nav' => 'navbar-expand',
    'classes_topnav_container' => 'container-fluid',

    /*
    |--------------------------------------------------------------------------
    | Sidebar
    |--------------------------------------------------------------------------
    |
    | Here we can modify the sidebar of the admin panel.
    |
    | For detailed instructions you can look the sidebar section here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Layout-and-Styling-Configuration
    |
    */

    'sidebar_mini' => 'lg',
    'sidebar_collapse' => false,
    'sidebar_collapse_auto_size' => false,
    'sidebar_collapse_remember' => true,
    'sidebar_collapse_remember_no_transition' => true,
    'sidebar_scrollbar_theme' => 'os-theme-light',
    'sidebar_scrollbar_auto_hide' => 'l',
    'sidebar_nav_accordion' => false,
    'sidebar_nav_animation_speed' => 300,

    /*
    |--------------------------------------------------------------------------
    | Control Sidebar (Right Sidebar)
    |--------------------------------------------------------------------------
    |
    | Here we can modify the right sidebar aka control sidebar of the admin panel.
    |
    | For detailed instructions you can look the right sidebar section here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Layout-and-Styling-Configuration
    |
    */

    'right_sidebar' => false,
    'right_sidebar_icon' => 'fas fa-cogs',
    'right_sidebar_theme' => 'dark',
    'right_sidebar_slide' => true,
    'right_sidebar_push' => true,
    'right_sidebar_scrollbar_theme' => 'os-theme-light',
    'right_sidebar_scrollbar_auto_hide' => 'l',

    /*
    |--------------------------------------------------------------------------
    | URLs
    |--------------------------------------------------------------------------
    |
    | Here we can modify the url settings of the admin panel.
    |
    | For detailed instructions you can look the urls section here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Basic-Configuration
    |
    */

    'use_route_url' => true,
    'dashboard_url' => 'dashboard',
    'logout_url' => 'logout',
    'login_url' => 'login',
    'register_url' => 'register',
    'password_reset_url' => 'password.request',
    'password_email_url' => 'password.email',
    'profile_url' => 'profile.show',
    'disable_darkmode_routes' => false,

    /*
    |--------------------------------------------------------------------------
    | Laravel Asset Bundling
    |--------------------------------------------------------------------------
    |
    | Here we can enable the Laravel Asset Bundling option for the admin panel.
    | Currently, the next modes are supported: 'mix', 'vite' and 'vite_js_only'.
    | When using 'vite_js_only', it's expected that your CSS is imported using
    | JavaScript. Typically, in your application's 'resources/js/app.js' file.
    | If you are not using any of these, leave it as 'false'.
    |
    | For detailed instructions you can look the asset bundling section here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Other-Configuration
    |
    */

    'laravel_asset_bundling' => false,
    'laravel_css_path' => 'css/app.css',
    'laravel_js_path' => 'js/app.js',

    /*
    |--------------------------------------------------------------------------
    | Menu Items
    |--------------------------------------------------------------------------
    |
    | Here we can modify the sidebar/top navigation of the admin panel.
    |
    | For detailed instructions you can look here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Menu-Configuration
    |
    */

    'menu' => [
        [
            'type' => 'fullscreen-widget',
            'topnav_right' => true,
        ],
        [
            'text' => 'Dashboard',
            'route' => 'dashboard',
            'icon' => 'fas fa-fw fa-gauge-high',
            'active' => ['dashboard'],
        ],
        [
            'text' => 'Negocios',
            'icon' => 'fas fa-fw fa-store',
            'submenu' => [
                [
                    'text' => 'Operativa',
                    'icon' => 'fas fa-fw fa-briefcase',
                    'submenu' => [
                        [
                            'text' => 'Negocios',
                            'route' => 'admin.negocios.index',
                            'icon' => 'far fa-fw fa-circle',
                            'active' => ['admin/negocios', 'admin/negocios/*'],
                        ],
                        [
                            'text' => 'Servicios',
                            'route' => 'admin.servicios.index',
                            'icon' => 'far fa-fw fa-circle',
                            'active' => ['admin/servicios', 'admin/servicios/*'],
                        ],
                        [
                            'text' => 'Recursos',
                            'route' => 'admin.recursos.index',
                            'icon' => 'far fa-fw fa-circle',
                            'active' => ['admin/recursos', 'admin/recursos/*'],
                        ],
                        [
                            'text' => 'Disponibilidades',
                            'route' => 'admin.disponibilidades.index',
                            'icon' => 'far fa-fw fa-circle',
                            'active' => ['admin/disponibilidades', 'admin/disponibilidades/*'],
                        ],
                        [
                            'text' => 'Combinaciones de recursos',
                            'url' => 'admin/recurso-combinaciones',
                            'icon' => 'far fa-fw fa-circle',
                            'active' => ['admin/recurso-combinaciones*'],
                        ],
                    ],
                ],
                [
                    'text' => 'Widget Calendario',
                    'route' => 'admin.negocios.shortcuts.widget',
                    'icon' => 'fas fa-fw fa-window-maximize',
                    'active' => ['admin/negocios/widget-calendario*'],
                ],
                [
                    'text' => 'Canales',
                    'icon' => 'fas fa-fw fa-plug',
                    'submenu' => [
                        [
                            'text' => 'Google Calendar',
                            'route' => 'admin.negocios.shortcuts.google-calendar',
                            'icon' => 'far fa-fw fa-circle',
                            'active' => ['admin/negocios/google-calendar*', 'admin/negocios/*/google-calendar*'],
                        ],
                    ],
                ],
            ],
        ],
        [
            'text' => 'Reservas',
            'icon' => 'fas fa-fw fa-calendar-check',
            'submenu' => [
                [
                    'text' => 'Agenda',
                    'icon' => 'fas fa-fw fa-calendar-alt',
                    'submenu' => [
                        [
                            'text' => 'Calendario',
                            'route' => 'admin.calendario.index',
                            'icon' => 'far fa-fw fa-circle',
                            'active' => ['admin/calendario*'],
                        ],
                        [
                            'text' => 'Reservas',
                            'route' => 'admin.reservas.index',
                            'icon' => 'far fa-fw fa-circle',
                            'active' => ['admin/reservas', 'admin/reservas/*'],
                        ],
                        [
                            'text' => 'Bloqueos',
                            'route' => 'admin.bloqueos.index',
                            'icon' => 'far fa-fw fa-circle',
                            'active' => ['admin/bloqueos', 'admin/bloqueos/*'],
                        ],
                    ],
                ],
                [
                    'text' => 'Relacion y cobros',
                    'icon' => 'fas fa-fw fa-wallet',
                    'submenu' => [
                        [
                            'text' => 'Clientes',
                            'route' => 'admin.clientes.index',
                            'icon' => 'far fa-fw fa-circle',
                            'active' => ['admin/clientes', 'admin/clientes/*'],
                        ],
                        [
                            'text' => 'Pagos',
                            'route' => 'admin.pagos.index',
                            'icon' => 'far fa-fw fa-circle',
                            'active' => ['admin/pagos', 'admin/pagos/*'],
                        ],
                    ],
                ],
            ],
        ],
        [
            'text' => 'Configurar la app',
            'icon' => 'fas fa-fw fa-sliders',
            'submenu' => [
                [
                    'text' => 'Catalogos de negocio',
                    'icon' => 'fas fa-fw fa-store-alt',
                    'submenu' => [
                        [
                            'text' => 'Tipos de negocio',
                            'route' => 'admin.tipos-negocio.index',
                            'icon' => 'far fa-fw fa-circle',
                            'active' => ['admin/tipos-negocio', 'admin/tipos-negocio/*'],
                        ],
                        [
                            'text' => 'Tipos de precio',
                            'route' => 'admin.tipos-precio.index',
                            'icon' => 'far fa-fw fa-circle',
                            'active' => ['admin/tipos-precio', 'admin/tipos-precio/*'],
                        ],
                        [
                            'text' => 'Tipos de recurso',
                            'route' => 'admin.tipos-recurso.index',
                            'icon' => 'far fa-fw fa-circle',
                            'active' => ['admin/tipos-recurso', 'admin/tipos-recurso/*'],
                        ],
                        [
                            'text' => 'Tipos de bloqueo',
                            'route' => 'admin.tipos-bloqueo.index',
                            'icon' => 'far fa-fw fa-circle',
                            'active' => ['admin/tipos-bloqueo', 'admin/tipos-bloqueo/*'],
                        ],
                    ],
                ],
                [
                    'text' => 'Catalogos de reservas',
                    'icon' => 'fas fa-fw fa-receipt',
                    'submenu' => [
                        [
                            'text' => 'Estados de reserva',
                            'route' => 'admin.estados-reserva.index',
                            'icon' => 'far fa-fw fa-circle',
                            'active' => ['admin/estados-reserva', 'admin/estados-reserva/*'],
                        ],
                        [
                            'text' => 'Tipos de pago',
                            'route' => 'admin.tipos-pago.index',
                            'icon' => 'far fa-fw fa-circle',
                            'active' => ['admin/tipos-pago', 'admin/tipos-pago/*'],
                        ],
                        [
                            'text' => 'Estados de pago',
                            'route' => 'admin.estados-pago.index',
                            'icon' => 'far fa-fw fa-circle',
                            'active' => ['admin/estados-pago', 'admin/estados-pago/*'],
                        ],
                        [
                            'text' => 'Conceptos de pago',
                            'route' => 'admin.conceptos-pago.index',
                            'icon' => 'far fa-fw fa-circle',
                            'active' => ['admin/conceptos-pago', 'admin/conceptos-pago/*'],
                        ],
                    ],
                ],
                [
                    'text' => 'Integraciones',
                    'icon' => 'fas fa-fw fa-plug',
                    'submenu' => [
                        [
                            'text' => 'Integraciones',
                            'route' => 'admin.integraciones.index',
                            'icon' => 'far fa-fw fa-circle',
                            'active' => ['admin/integraciones', 'admin/integraciones/*'],
                        ],
                        [
                            'text' => 'Mapeos',
                            'route' => 'admin.integracion-mapeos.index',
                            'icon' => 'far fa-fw fa-circle',
                            'active' => ['admin/integracion-mapeos', 'admin/integracion-mapeos/*'],
                        ],
                        [
                            'text' => 'Ocupaciones externas',
                            'route' => 'admin.ocupaciones-externas.index',
                            'icon' => 'far fa-fw fa-circle',
                            'active' => ['admin/ocupaciones-externas', 'admin/ocupaciones-externas/*'],
                        ],
                    ],
                ],
            ],
        ],
        ['header' => 'Desarrollo'],
        [
            'text' => 'Chat Test',
            'route' => 'admin.chat-test.index',
            'icon' => 'fas fa-fw fa-robot',
            'active' => ['admin/chat/test*'],
        ],
        ['header' => 'Cuenta'],
        [
            'text' => 'Perfil',
            'route' => 'profile.show',
            'icon' => 'fas fa-fw fa-user',
            'active' => ['user/profile'],
        ],
        ['header' => 'Navegacion'],
        [
            'text' => 'Ver sitio web',
            'url' => '/',
            'icon' => 'fas fa-fw fa-arrow-up-right-from-square',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Menu Filters
    |--------------------------------------------------------------------------
    |
    | Here we can modify the menu filters of the admin panel.
    |
    | For detailed instructions you can look the menu filters section here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Menu-Configuration
    |
    */

    'filters' => [
        JeroenNoten\LaravelAdminLte\Menu\Filters\GateFilter::class,
        JeroenNoten\LaravelAdminLte\Menu\Filters\HrefFilter::class,
        JeroenNoten\LaravelAdminLte\Menu\Filters\SearchFilter::class,
        JeroenNoten\LaravelAdminLte\Menu\Filters\ActiveFilter::class,
        JeroenNoten\LaravelAdminLte\Menu\Filters\ClassesFilter::class,
        JeroenNoten\LaravelAdminLte\Menu\Filters\LangFilter::class,
        JeroenNoten\LaravelAdminLte\Menu\Filters\DataFilter::class,
    ],

    /*
    |--------------------------------------------------------------------------
    | Plugins Initialization
    |--------------------------------------------------------------------------
    |
    | Here we can modify the plugins used inside the admin panel.
    |
    | For detailed instructions you can look the plugins section here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Plugins-Configuration
    |
    */

    'plugins' => [
        'Datatables' => [
            'active' => false,
            'files' => [
                [
                    'type' => 'js',
                    'asset' => false,
                    'location' => '//cdn.datatables.net/1.10.19/js/jquery.dataTables.min.js',
                ],
                [
                    'type' => 'js',
                    'asset' => false,
                    'location' => '//cdn.datatables.net/1.10.19/js/dataTables.bootstrap4.min.js',
                ],
                [
                    'type' => 'css',
                    'asset' => false,
                    'location' => '//cdn.datatables.net/1.10.19/css/dataTables.bootstrap4.min.css',
                ],
            ],
        ],
        'Select2' => [
            'active' => false,
            'files' => [
                [
                    'type' => 'js',
                    'asset' => false,
                    'location' => '//cdnjs.cloudflare.com/ajax/libs/select2/4.0.3/js/select2.min.js',
                ],
                [
                    'type' => 'css',
                    'asset' => false,
                    'location' => '//cdnjs.cloudflare.com/ajax/libs/select2/4.0.3/css/select2.css',
                ],
            ],
        ],
        'Chartjs' => [
            'active' => false,
            'files' => [
                [
                    'type' => 'js',
                    'asset' => false,
                    'location' => '//cdnjs.cloudflare.com/ajax/libs/Chart.js/2.7.0/Chart.bundle.min.js',
                ],
            ],
        ],
        'Sweetalert2' => [
            'active' => false,
            'files' => [
                [
                    'type' => 'js',
                    'asset' => false,
                    'location' => '//cdn.jsdelivr.net/npm/sweetalert2@8',
                ],
            ],
        ],
        'Pace' => [
            'active' => false,
            'files' => [
                [
                    'type' => 'css',
                    'asset' => false,
                    'location' => '//cdnjs.cloudflare.com/ajax/libs/pace/1.0.2/themes/blue/pace-theme-center-radar.min.css',
                ],
                [
                    'type' => 'js',
                    'asset' => false,
                    'location' => '//cdnjs.cloudflare.com/ajax/libs/pace/1.0.2/pace.min.js',
                ],
            ],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | IFrame
    |--------------------------------------------------------------------------
    |
    | Here we change the IFrame mode configuration. Note these changes will
    | only apply to the view that extends and enable the IFrame mode.
    |
    | For detailed instructions you can look the iframe mode section here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/IFrame-Mode-Configuration
    |
    */

    'iframe' => [
        'default_tab' => [
            'url' => null,
            'title' => null,
        ],
        'buttons' => [
            'close' => true,
            'close_all' => true,
            'close_all_other' => true,
            'scroll_left' => true,
            'scroll_right' => true,
            'fullscreen' => true,
        ],
        'options' => [
            'loading_screen' => 1000,
            'auto_show_new_tab' => true,
            'use_navbar_items' => true,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Livewire
    |--------------------------------------------------------------------------
    |
    | Here we can enable the Livewire support.
    |
    | For detailed instructions you can look the livewire here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Other-Configuration
    |
    */

    'livewire' => true,
];
