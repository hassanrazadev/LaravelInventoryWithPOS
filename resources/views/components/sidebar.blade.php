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
            <li class="heading">MAIN</li>
            <li>
                <a class="{{ $isNavActive('dashboard') ? 'active' : '' }}" href="{{ route('dashboard') }}" title="Dashboard">
                    <i class="sidebar-item-icon fa fa-th-large"></i>
                    <span class="nav-label">Dashboard</span>
                </a>
            </li>
            @can('create order')
                <li>
                    <a class="{{ $isNavActive('pos') ? 'active' : '' }}" href="{{ route('orders.create') }}" title="POS Terminal">
                        <i class="sidebar-item-icon fa fa-calculator"></i>
                        <span class="nav-label">POS Terminal</span>
                        <span class="ims-nav-pill">POS</span>
                    </a>
                </li>
            @endcan

            @if(auth()->user()->canAny(['read order', 'read purchase', 'read return', 'read expense']))
                <li class="heading">SALES &amp; OPERATIONS</li>
            @endif
            @can('read order')
                <li>
                    <a class="{{ $isNavActive('orders') ? 'active' : '' }}" href="{{ route('orders.index') }}" title="Orders">
                        <i class="sidebar-item-icon fa fa-shopping-bag"></i>
                        <span class="nav-label">Orders</span>
                    </a>
                </li>
            @endcan
            @can('read purchase')
                <li>
                    <a class="{{ $isNavActive('purchases') ? 'active' : '' }}" href="{{ route('purchases.index') }}" title="Purchases">
                        <i class="sidebar-item-icon fa fa-truck"></i>
                        <span class="nav-label">Purchases</span>
                    </a>
                </li>
            @endcan
            @can('read return')
                <li>
                    <a class="{{ $isNavActive('returns') ? 'active' : '' }}" href="{{ route('returns.index') }}" title="Sales Returns">
                        <i class="sidebar-item-icon fa fa-undo"></i>
                        <span class="nav-label">Sales Returns</span>
                    </a>
                </li>
            @endcan
            @can('read expense')
                <li>
                    <a class="{{ $isNavActive('expenses') ? 'active' : '' }}" href="{{ route('expenses.index') }}" title="Expenses">
                        <i class="sidebar-item-icon fa fa-credit-card"></i>
                        <span class="nav-label">Expenses</span>
                    </a>
                </li>
            @endcan

            @if(auth()->user()->canAny(['read product', 'read category']))
                <li class="heading">INVENTORY</li>
            @endif
            @can('read product')
                <li>
                    <a class="{{ $isNavActive('products') ? 'active' : '' }}" href="{{ route('products.index') }}" title="Products">
                        <i class="sidebar-item-icon fa fa-cubes"></i>
                        <span class="nav-label">Products</span>
                    </a>
                </li>
            @endcan
            @can('read category')
                <li>
                    <a class="{{ $isNavActive('categories') ? 'active' : '' }}" href="{{ route('categories.index') }}" title="Categories">
                        <i class="sidebar-item-icon fa fa-tags"></i>
                        <span class="nav-label">Categories</span>
                    </a>
                </li>
            @endcan

            @if(auth()->user()->canAny(['read customer', 'read supplier']))
                <li class="heading">CONTACTS</li>
            @endif
            @can('read customer')
                <li>
                    <a class="{{ $isNavActive('customers') ? 'active' : '' }}" href="{{ route('customers.index') }}" title="Customers">
                        <i class="sidebar-item-icon fa fa-address-book"></i>
                        <span class="nav-label">Customers</span>
                    </a>
                </li>
            @endcan
            @can('read supplier')
                <li>
                    <a class="{{ $isNavActive('suppliers') ? 'active' : '' }}" href="{{ route('suppliers.index') }}" title="Suppliers">
                        <i class="sidebar-item-icon fa fa-building"></i>
                        <span class="nav-label">Suppliers</span>
                    </a>
                </li>
            @endcan

            @if(auth()->user()->canAny(['read user', 'read role', 'read permission']))
                <li class="heading">ADMINISTRATION</li>
            @endif
            @can('read user')
                <li>
                    <a class="{{ $isNavActive('users') ? 'active' : '' }}" href="{{ route('users.index') }}" title="Users">
                        <i class="sidebar-item-icon fa fa-users"></i>
                        <span class="nav-label">Users</span>
                    </a>
                </li>
            @endcan
            @can('read role')
                <li>
                    <a class="{{ $isNavActive('roles') ? 'active' : '' }}" href="{{ route('roles.index') }}" title="Roles">
                        <i class="sidebar-item-icon fa fa-shield"></i>
                        <span class="nav-label">Roles</span>
                    </a>
                </li>
            @endcan
            @can('read permission')
                <li>
                    <a class="{{ $isNavActive('permissions') ? 'active' : '' }}" href="{{ route('permissions.index') }}" title="Permissions">
                        <i class="sidebar-item-icon fa fa-key"></i>
                        <span class="nav-label">Permissions</span>
                    </a>
                </li>
            @endcan
        </ul>
    </div>
</nav>
