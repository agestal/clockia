@inject('layoutHelper', 'JeroenNoten\LaravelAdminLte\Helpers\LayoutHelper')

<nav class="main-header navbar
    {{ config('adminlte.classes_topnav_nav', 'navbar-expand') }}
    {{ config('adminlte.classes_topnav', 'navbar-white navbar-light') }}">

    <ul class="navbar-nav">
        @include('adminlte::partials.navbar.menu-item-left-sidebar-toggler')

        <li class="nav-item d-none d-sm-flex align-items-center">
            <span class="clockia-navbar-title">
                Clockia <span>/ Admin</span>
            </span>
        </li>

        @each('adminlte::partials.navbar.menu-item', $adminlte->menu('navbar-left'), 'item')

        @yield('content_top_nav_left')
    </ul>

    <ul class="navbar-nav ml-auto">
        @yield('content_top_nav_right')

        @each('adminlte::partials.navbar.menu-item', $adminlte->menu('navbar-right'), 'item')

        @if(Auth::user())
            @if(config('adminlte.usermenu_enabled'))
                @include('adminlte::partials.navbar.menu-item-dropdown-user-menu')
            @else
                @include('adminlte::partials.navbar.menu-item-logout-link')
            @endif
        @endif

        @if($layoutHelper->isRightSidebarEnabled())
            @include('adminlte::partials.navbar.menu-item-right-sidebar-toggler')
        @endif
    </ul>

</nav>
