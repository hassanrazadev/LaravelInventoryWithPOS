<nav class="page-sidebar" id="sidebar">
    <div id="sidebar-collapse">
        <div class="admin-block d-flex">
            <div class="ims-avatar">{{ $user['initials'] }}</div>
            <div class="admin-info">
                <div class="font-strong">{{ $user['name'] }}</div>
                <small class="text-capitalize">{{ $user['role'] }}</small>
            </div>
        </div>
        <ul class="side-menu metismenu">
            <li class="heading">OVERVIEW</li>
            <li>
                <a class="{{ url()->current() == route('dashboard') ? 'active' : '' }}" href="{{ route('dashboard') }}">
                    <i class="sidebar-item-icon fa fa-th-large"></i>
                    <span class="nav-label">Dashboard</span>
                </a>
            </li>

            @if(auth()->user()->canAny(['create category', 'read category', 'create product', 'read product']))
                <li class="heading">INVENTORY</li>
            @endif

            @canany(['create category', 'read category'])
                <li class="{{ $activeNav == 'categories' ? 'active' : '' }}">
                    <a href="javascript:;">
                        <i class="sidebar-item-icon fa fa-tags"></i>
                        <span class="nav-label">Categories</span>
                        <i class="fa fa-angle-left arrow"></i>
                    </a>
                    <ul class="nav-2-level collapse {{ $activeNav == 'categories' ? 'in' : '' }}">
                        @can('create category')
                            <li>
                                <a class="{{ url()->current() == route('categories.create') ? 'active' : '' }}" href="{{ route('categories.create') }}">Create New</a>
                            </li>
                        @endcan
                        @can('read category')
                            <li>
                                <a class="{{ url()->current() == route('categories.index') ? 'active' : '' }}" href="{{ route('categories.index') }}">Manage Categories</a>
                            </li>
                        @endcan
                    </ul>
                </li>
            @endcanany

            @canany(['create product', 'read product'])
                <li class="{{ $activeNav == 'products' ? 'active' : '' }}">
                    <a href="javascript:;">
                        <i class="sidebar-item-icon fa fa-cubes"></i>
                        <span class="nav-label">Products</span>
                        <i class="fa fa-angle-left arrow"></i>
                    </a>
                    <ul class="nav-2-level collapse {{ $activeNav == 'products' ? 'in' : '' }}">
                        @can('create product')
                            <li>
                                <a class="{{ url()->current() == route('products.create') ? 'active' : '' }}" href="{{ route('products.create') }}">Create New</a>
                            </li>
                        @endcan
                        @can('read product')
                            <li>
                                <a class="{{ url()->current() == route('products.index') ? 'active' : '' }}" href="{{ route('products.index') }}">Manage Products</a>
                            </li>
                        @endcan
                    </ul>
                </li>
            @endcanany

            @if(auth()->user()->canAny(['create purchase', 'read purchase', 'create order', 'read order']))
                <li class="heading">TRANSACTIONS</li>
            @endif

            @canany(['create purchase', 'read purchase'])
                <li class="{{ $activeNav == 'purchases' ? 'active' : '' }}">
                    <a href="javascript:;">
                        <i class="sidebar-item-icon fa fa-briefcase"></i>
                        <span class="nav-label">Purchases</span>
                        <i class="fa fa-angle-left arrow"></i>
                    </a>
                    <ul class="nav-2-level collapse {{ $activeNav == 'purchases' ? 'in' : '' }}">
                        @can('create purchase')
                            <li>
                                <a class="{{ url()->current() == route('purchases.create') ? 'active' : '' }}" href="{{ route('purchases.create') }}">Create New</a>
                            </li>
                        @endcan
                        @can('read purchase')
                            <li>
                                <a class="{{ url()->current() == route('purchases.index') ? 'active' : '' }}" href="{{ route('purchases.index') }}">Manage Purchases</a>
                            </li>
                        @endcan
                    </ul>
                </li>
            @endcanany

            @canany(['create order', 'read order'])
                <li class="{{ $activeNav == 'orders' ? 'active' : '' }}">
                    <a href="javascript:;">
                        <i class="sidebar-item-icon fa fa-shopping-basket"></i>
                        <span class="nav-label">Orders</span>
                        <i class="fa fa-angle-left arrow"></i>
                    </a>
                    <ul class="nav-2-level collapse {{ $activeNav == 'orders' ? 'in' : '' }}">
                        @can('create order')
                            <li>
                                <a class="{{ url()->current() == route('orders.create') ? 'active' : '' }}" href="{{ route('orders.create') }}">Create New / POS</a>
                            </li>
                        @endcan
                        @can('read order')
                            <li>
                                <a class="{{ url()->current() == route('orders.index') ? 'active' : '' }}" href="{{ route('orders.index') }}">Manage Orders</a>
                            </li>
                        @endcan
                    </ul>
                </li>
            @endcanany

            @if(auth()->user()->canAny(['create supplier', 'read supplier', 'create customer', 'read customer']))
                <li class="heading">CONTACTS</li>
            @endif

            @canany(['create supplier', 'read supplier'])
                <li class="{{ $activeNav == 'suppliers' ? 'active' : '' }}">
                    <a href="javascript:;">
                        <i class="sidebar-item-icon fa fa-truck"></i>
                        <span class="nav-label">Suppliers</span>
                        <i class="fa fa-angle-left arrow"></i>
                    </a>
                    <ul class="nav-2-level collapse {{ $activeNav == 'suppliers' ? 'in' : '' }}">
                        @can('create supplier')
                            <li>
                                <a class="{{ url()->current() == route('suppliers.create') ? 'active' : '' }}" href="{{ route('suppliers.create') }}">Add Supplier</a>
                            </li>
                        @endcan
                        @can('read supplier')
                            <li>
                                <a class="{{ url()->current() == route('suppliers.index') ? 'active' : '' }}" href="{{ route('suppliers.index') }}">Manage Suppliers</a>
                            </li>
                        @endcan
                    </ul>
                </li>
            @endcanany

            @canany(['create customer', 'read customer'])
                <li class="{{ $activeNav == 'customers' ? 'active' : '' }}">
                    <a href="javascript:;">
                        <i class="sidebar-item-icon fa fa-address-book"></i>
                        <span class="nav-label">Customers</span>
                        <i class="fa fa-angle-left arrow"></i>
                    </a>
                    <ul class="nav-2-level collapse {{ $activeNav == 'customers' ? 'in' : '' }}">
                        @can('create customer')
                            <li>
                                <a class="{{ url()->current() == route('customers.create') ? 'active' : '' }}" href="{{ route('customers.create') }}">Add Customer</a>
                            </li>
                        @endcan
                        @can('read customer')
                            <li>
                                <a class="{{ url()->current() == route('customers.index') ? 'active' : '' }}" href="{{ route('customers.index') }}">Manage Customers</a>
                            </li>
                        @endcan
                    </ul>
                </li>
            @endcanany

            @if(auth()->user()->canAny(['create user', 'read user', 'create role', 'read role', 'create permission', 'read permission']))
                <li class="heading">ADMINISTRATION</li>
            @endif

            @canany(['create user', 'read user'])
                <li class="{{ $activeNav == 'users' ? 'active' : '' }}">
                    <a href="javascript:;">
                        <i class="sidebar-item-icon fa fa-users"></i>
                        <span class="nav-label">Users</span>
                        <i class="fa fa-angle-left arrow"></i>
                    </a>
                    <ul class="nav-2-level collapse {{ $activeNav == 'users' ? 'in' : '' }}">
                        @can('create user')
                            <li>
                                <a class="{{ url()->current() == route('users.create') ? 'active' : '' }}" href="{{ route('users.create') }}">Add User</a>
                            </li>
                        @endcan
                        @can('read user')
                            <li>
                                <a class="{{ url()->current() == route('users.index') ? 'active' : '' }}" href="{{ route('users.index') }}">Manage Users</a>
                            </li>
                        @endcan
                    </ul>
                </li>
            @endcanany

            @canany(['create role', 'read role'])
                <li class="{{ $activeNav == 'roles' ? 'active' : '' }}">
                    <a href="javascript:;">
                        <i class="sidebar-item-icon fa fa-shield"></i>
                        <span class="nav-label">Roles</span>
                        <i class="fa fa-angle-left arrow"></i>
                    </a>
                    <ul class="nav-2-level collapse {{ $activeNav == 'roles' ? 'in' : '' }}">
                        @can('create role')
                            <li>
                                <a class="{{ url()->current() == route('roles.create') ? 'active' : '' }}" href="{{ route('roles.create') }}">Create Role</a>
                            </li>
                        @endcan
                        @can('read role')
                            <li>
                                <a class="{{ url()->current() == route('roles.index') ? 'active' : '' }}" href="{{ route('roles.index') }}">Manage Roles</a>
                            </li>
                        @endcan
                    </ul>
                </li>
            @endcanany

            @canany(['create permission', 'read permission'])
                <li class="{{ $activeNav == 'permissions' ? 'active' : '' }}">
                    <a href="javascript:;">
                        <i class="sidebar-item-icon fa fa-key"></i>
                        <span class="nav-label">Permissions</span>
                        <i class="fa fa-angle-left arrow"></i>
                    </a>
                    <ul class="nav-2-level collapse {{ $activeNav == 'permissions' ? 'in' : '' }}">
                        @can('create permission')
                            <li>
                                <a class="{{ url()->current() == route('permissions.create') ? 'active' : '' }}" href="{{ route('permissions.create') }}">Create Permission</a>
                            </li>
                        @endcan
                        @can('read permission')
                            <li>
                                <a class="{{ url()->current() == route('permissions.index') ? 'active' : '' }}" href="{{ route('permissions.index') }}">Manage Permissions</a>
                            </li>
                        @endcan
                    </ul>
                </li>
            @endcanany
        </ul>
    </div>
</nav>
