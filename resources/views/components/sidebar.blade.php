<nav class="page-sidebar" id="sidebar">
    <div id="sidebar-collapse">
        <div class="admin-block d-flex">
            <div>
                <img src="{{asset('assets/img/admin-avatar.png')}}" width="45px" />
            </div>
            <div class="admin-info">
                <div class="font-strong">{{$user['user']['name']}}</div><small class="text-capitalize">{{$user['user']['role']}}</small></div>
        </div>
        <ul class="side-menu metismenu">
            <li>
                <a class="{{url()->current() == route('dashboard') ? 'active' : ''}}" href="{{route('dashboard')}}"><i class="sidebar-item-icon fa fa-th-large"></i>
                    <span class="nav-label">Dashboard</span>
                </a>
            </li>
            <li class="heading">FEATURES</li>
            @canany(['create category', 'read category'])
                <li class="{{$activeNav == 'categories' ? 'active' : ''}}">
                    <a href="javascript:;"><i class="sidebar-item-icon fa fa-tags"></i>
                        <span class="nav-label">Categories</span><i class="fa fa-angle-left arrow"></i></a>
                    <ul class="nav-2-level collapse">
                        @can('create category')
                            <li>
                                <a class="{{url()->current() == route('categories.create') ? 'active' : ''}}" href="{{route('categories.create')}}">Create New</a>
                            </li>
                        @endcan
                        @can('read category')
                            <li>
                                <a class="{{url()->current() == route('categories.index') ? 'active' : ''}}" href="{{route('categories.index')}}">Manage Categories</a>
                            </li>
                        @endcan
                    </ul>
                </li>
            @endcanany
            @canany(['create product', 'read product'])
                <li class="{{$activeNav == 'products' ? 'active' : ''}}">
                    <a href="javascript:;"><i class="sidebar-item-icon fa fa-cubes"></i>
                        <span class="nav-label">Products</span><i class="fa fa-angle-left arrow"></i></a>
                    <ul class="nav-2-level collapse">
                        <li>
                            <a class="{{url()->current() == route('products.create') ? 'active' : ''}}" href="{{route('products.create')}}">Create New</a>
                        </li>
                        <li>
                            <a class="{{url()->current() == route('products.index') ? 'active' : ''}}" href="{{route('products.index')}}">Manage Products</a>
                        </li>
                    </ul>
                </li>
            @endcanany
            @canany(['create purchase', 'read purchase'])
                <li class="{{$activeNav == 'purchases' ? 'active' : ''}}">
                    <a href="javascript:;"><i class="sidebar-item-icon fa fa-briefcase"></i>
                        <span class="nav-label">Purchases</span><i class="fa fa-angle-left arrow"></i></a>
                    <ul class="nav-2-level collapse">
                        @can('create purchase')
                            <li>
                                <a class="{{url()->current() == route('purchases.create') ? 'active' : ''}}" href="{{route('purchases.create')}}">Create New</a>
                            </li>
                        @endcan
                        @can('read purchase')
                            <li>
                                <a class="{{url()->current() == route('purchases.index') ? 'active' : ''}}" href="{{route('purchases.index')}}">Manage Purchases</a>
                            </li>
                        @endcan
                    </ul>
                </li>
            @endcanany
            <li>
                <a href="javascript:;"><i class="sidebar-item-icon fa fa-users"></i>
                    <span class="nav-label">Users</span><i class="fa fa-angle-left arrow"></i></a>
                <ul class="nav-2-level collapse">
                    <li>
                        <a href="#">Add User</a>
                    </li>
                    <li>
                        <a href="#">Manage Users</a>
                    </li>
                    <li>
                        <a href="#">Manage Roles</a>
                    </li>
                    <li>
                        <a href="#">Manage Permissions</a>
                    </li>
                </ul>
            </li>
            <li>
                <a href="javascript:;"><i class="sidebar-item-icon fa fa-truck"></i>
                    <span class="nav-label">Suppliers</span><i class="fa fa-angle-left arrow"></i></a>
                <ul class="nav-2-level collapse">
                    <li>
                        <a href="#">Add Supplier</a>
                    </li>
                    <li>
                        <a href="#">Manage Suppliers</a>
                    </li>
                </ul>
            </li>
        </ul>
    </div>
</nav>