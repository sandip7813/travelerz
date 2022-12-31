<!-- Main Sidebar Container -->
<aside class="main-sidebar sidebar-dark-primary elevation-4">
    <!-- Brand Logo -->
    <a href="{{ route('admin.dashboard') }}" class="brand-link">
      <img src="{{ asset('images/twhite.jpeg') }}" alt="AdminLTE Logo" class="brand-image img-circle elevation-3" style="opacity: .8">
      <span class="brand-text font-weight-light">{{ config('app.name') }} Admin</span>
    </a>

    <!-- Sidebar -->
    <div class="sidebar">
      <!-- Sidebar user panel (optional) -->
      <div class="user-panel mt-3 pb-3 mb-3 d-flex">
        <div class="image">
          <img src="{{ asset('admin/dist/img/user2-160x160.jpg') }}" class="img-circle elevation-2" alt="User Image">
        </div>
        <div class="info">
          <a href="#" class="d-block">Hi {{ Auth::user()->name ?? 'Admin' }},</a>
        </div>
      </div>

      <!-- Sidebar Menu -->
      <nav class="mt-2">
        <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">
          <!-- Add icons to the links using the .nav-icon class
               with font-awesome or any other icon font library -->
              {{-- +++++++++++++++++++++++++ USERS :: Start +++++++++++++++++++++++++ --}}
              <li class="nav-item @if (Request::is('admin/users') || Request::is('admin/users/*')) menu-open @endif">
                <a href="{{ route('admin.users.index') }}" class="nav-link @if (Request::is('admin/users') || Request::is('admin/users/*')) active @endif">
                  <i class="nav-icon fas fa-users"></i>
                  <p>Users </p>
                </a>
              </li>
              {{-- +++++++++++++++++++++++++ USERS :: End +++++++++++++++++++++++++ --}}

              {{-- +++++++++++++++++++++++++ CATEGORIES :: Start +++++++++++++++++++++++++ --}}
              <li class="nav-item @if (Request::is('admin/categories') || Request::is('admin/categories/*')) menu-open @endif">
                <a href="javascript: void(0);" class="nav-link @if (Request::is('admin/categories') || Request::is('admin/categories/*')) active @endif">
                  <i class="nav-icon fas fa-th"></i>
                  <p>Categories <i class="right fas fa-angle-left"></i></p>
                </a>

                <ul class="nav nav-treeview">
                  <li class="nav-item">
                    <a href="{{ route('admin.category.index') }}" class="nav-link @if (Request::is('admin/categories')) active @endif">
                      <i class="far fa-circle nav-icon"></i>
                      <p>Manage Categories</p>
                    </a>
                  </li>
                  <li class="nav-item">
                    <a href="{{ route('admin.category.create') }}" class="nav-link @if (Request::is('admin/categories/create')) active @endif">
                      <i class="far fa-circle nav-icon"></i>
                      <p>Add Category</p>
                    </a>
                  </li>
                </ul>
              </li>
              {{-- +++++++++++++++++++++++++ CATEGORIES :: End +++++++++++++++++++++++++ --}}

              {{-- +++++++++++++++++++++++++ MY ACCOUNT :: Start +++++++++++++++++++++++++ --}}
              <li class="nav-item @if (Request::is('admin/myaccount') || Request::is('admin/myaccount/*')) menu-open @endif">
                <a href="javascript: void(0);" class="nav-link @if (Request::is('admin/myaccount') || Request::is('admin/myaccount/*')) active @endif">
                  <i class="nav-icon fas fa-user-cog"></i>
                  <p>My Account <i class="right fas fa-angle-left"></i></p>
                </a>

                <ul class="nav nav-treeview">
                  <li class="nav-item">
                    <a href="{{ route('admin.myaccount.change-password') }}" class="nav-link @if (Request::is('admin/myaccount/change-password')) active @endif">
                      <i class="far fa-circle nav-icon"></i>
                      <p>Change Password</p>
                    </a>
                  </li>
                </ul>
              </li>
              {{-- +++++++++++++++++++++++++ MY ACCOUNT :: End +++++++++++++++++++++++++ --}}

              {{-- LOGOUT :: Start --}}
              <li class="nav-item">
                <a href="{{ route('logout') }}" class="nav-link" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                  <i class="fas fa-sign-out-alt"></i>
                  <p>Logout</p>
                </a>
              </li>

              <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                  {{ csrf_field() }}
              </form>
              {{-- LOGOUT :: End --}}
        </ul>
      </nav>
      <!-- /.sidebar-menu -->
    </div>
    <!-- /.sidebar -->
  </aside>