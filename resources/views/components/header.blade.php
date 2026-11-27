<header class="header">
    <div class="page-brand">
        <a class="link" href="{{ route('dashboard') }}">
            <span class="ims-brand-badge"><i class="fa fa-cubes"></i></span>
            <span class="brand">IMS <span class="brand-tip">POS</span></span>
        </a>
    </div>
    <div class="flexbox flex-1 pr-3">
        <!-- START TOP-LEFT TOOLBAR-->
        <ul class="nav navbar-toolbar">
            <li>
                <a class="nav-link sidebar-toggler js-sidebar-toggler" title="Toggle sidebar"><i class="ti-menu"></i></a>
            </li>
        </ul>
        <!-- END TOP-LEFT TOOLBAR-->
        <!-- START TOP-RIGHT TOOLBAR-->
        <ul class="nav navbar-toolbar">
            @unless(request()->routeIs('orders.create'))
                @can('create order')
                    <li class="mr-2">
                        <a href="{{ route('orders.create') }}" class="btn btn-primary btn-sm text-white px-3">
                            <i class="fa fa-calculator mr-1"></i> POS Terminal
                        </a>
                    </li>
                @endcan
            @endunless
            <li class="dropdown dropdown-user">
                <a class="nav-link dropdown-toggle link d-flex align-items-center" data-toggle="dropdown" href="javascript:;">
                    <span class="ims-avatar ims-avatar-sm mr-2">{{ $user['initials'] }}</span>
                    <span class="d-none d-sm-inline font-weight-medium">{{ $user['name'] }}</span>
                    <i class="fa fa-angle-down ml-2 text-muted"></i>
                </a>
                <div class="dropdown-menu dropdown-menu-right">
                    <div class="px-3 py-2 border-bottom mb-1">
                        <div class="font-weight-bold text-dark" style="font-size: 13px;">{{ $user['name'] }}</div>
                        <div class="text-muted text-truncate" style="font-size: 12px;">{{ $user['email'] }}</div>
                        <span class="badge badge-soft-primary text-capitalize mt-1">{{ $user['role'] }}</span>
                    </div>
                    <a class="dropdown-item" href="{{ route('profile.edit') }}">
                        <i class="fa fa-user text-muted"></i> My Profile
                    </a>
                    <div class="dropdown-divider my-1"></div>
                    <form action="{{ route('user.logout') }}" method="POST" class="m-0">
                        @csrf
                        <button type="submit" class="dropdown-item text-danger w-100 text-left border-0 bg-transparent" style="cursor: pointer;">
                            <i class="fa fa-power-off"></i> Logout
                        </button>
                    </form>
                </div>
            </li>
        </ul>
        <!-- END TOP-RIGHT TOOLBAR-->
    </div>
</header>
