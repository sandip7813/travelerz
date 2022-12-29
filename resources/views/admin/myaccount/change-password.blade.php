@extends('admin.layouts.app')

@section('content')
  <!-- Content Wrapper. Contains page content -->
  <div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <div class="content-header">
      <div class="container-fluid">
        <div class="row mb-2">
          <div class="col-sm-6">
            <h1 class="m-0">Change Password</h1>
          </div><!-- /.col -->
          <div class="col-sm-6">
            {{-- <ol class="breadcrumb float-sm-right">
              <li class="breadcrumb-item"><a href="{{ route('admin.category.index') }}">Category</a></li>
              <li class="breadcrumb-item active">Add New</li>
            </ol> --}}
          </div><!-- /.col -->
        </div><!-- /.row -->
      </div><!-- /.container-fluid -->
    </div>
    <!-- /.content-header -->

    <!-- Main content -->
    <section class="content">
      <div class="container-fluid">
        <div class="row">
        <div class="col-md-3"></div>

        <div class="col-md-6">

          <!-- Input addon -->
          <div class="card card-info">
              <div class="card-header">
                  <h3 class="card-title">Update Your Password</h3>
              </div>

              @if (session('error'))
                  <div class="alert alert-danger">
                      {{ session('error') }}
                  </div>
              @endif
              @if (session('success'))
                  <div class="alert alert-success">
                      {{ session('success') }}
                  </div>
              @endif
              @if($errors)
                  @foreach ($errors->all() as $error)
                      <div class="alert alert-danger">{{ $error }}</div>
                  @endforeach
              @endif
              
              <form class="form-horizontal" method="POST" action="{{ route('admin.myaccount.change-password-submit') }}">
                <div class="card-body">
                  {{ csrf_field() }}

                  <div class="form-group{{ $errors->has('current-password') ? ' has-error' : '' }}">
                      <label for="new-password" class="col-md-4 control-label">Current Password</label>

                      <div class="col-md-12">
                          <input id="current-password" type="password" class="form-control" name="current-password" required>

                          @if ($errors->has('current-password'))
                              <span class="help-block">
                                  <strong>{{ $errors->first('current-password') }}</strong>
                              </span>
                          @endif
                      </div>
                  </div>

                  <div class="form-group{{ $errors->has('new-password') ? ' has-error' : '' }}">
                      <label for="new-password" class="col-md-4 control-label">New Password</label>

                      <div class="col-md-12">
                          <input id="new-password" type="password" class="form-control" name="new-password" required>

                          @if ($errors->has('new-password'))
                              <span class="help-block">
                                  <strong>{{ $errors->first('new-password') }}</strong>
                              </span>
                          @endif
                      </div>
                  </div>

                  <div class="form-group">
                      <label for="new-password-confirm" class="col-md-4 control-label">Confirm New Password</label>

                      <div class="col-md-12">
                          <input id="new-password-confirm" type="password" class="form-control" name="new-password_confirmation" required>
                      </div>
                  </div>

                  <div class="form-group">
                      <div class="col-md-12">
                          <button type="submit" class="btn btn-primary">Change Password</button>
                      </div>
                  </div>
                </div>
              </form>
          </div>
          <!-- /.card -->
        </div>
        <!--/.col (left) -->

        <div class="col-md-3"></div>
          <!--/.col (left) -->

        </div>
        <!-- /.row -->
      </div><!-- /.container-fluid -->
    </section>
    <!-- /.content -->
  </div>
  <!-- /.content-wrapper -->
@endsection
