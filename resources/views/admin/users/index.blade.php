@extends('admin.layouts.app')

@section('styles')
<link href="{{ asset('css/bootstrap-toggle.min.css') }}" rel="stylesheet">
<!-- Select2 -->
<link rel="stylesheet" href="{{ asset('admin/plugins/select2/css/select2.min.css') }}">
<link rel="stylesheet" href="{{ asset('admin/plugins/select2-bootstrap4-theme/select2-bootstrap4.min.css') }}">
@endsection

@section('content')
  <!-- Content Wrapper. Contains page content -->
  <div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <div class="content-header">
      <div class="container-fluid">
        <div class="row mb-2">
          <div class="col-sm-6">
            <h1 class="m-0">Manage Users</h1>
          </div><!-- /.col -->
          <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
              <li class="breadcrumb-item active">Manage Users</li>
            </ol>
          </div><!-- /.col -->
        </div><!-- /.row -->
      </div><!-- /.container-fluid -->
    </div>
    <!-- /.content-header -->

    <!-- Main content -->
    <!-- Main content -->
    <section class="content">
      <div class="container-fluid">
        <div class="row">
          <div class="col-12">
            <div class="card">
              <div class="card-body">

                {{-- +++++++++++++++++++ SEARCH RECORDS :: Start +++++++++++++++++++ --}}
                <div class="card card-info">
                  <div class="card-header">
                    <h3 class="card-title">Search Box</h3>
                  </div>

                  <div class="card-body">
                    <form action="{{ route('admin.users.index') }}" method="get">
                      <div class="row">
                        <div class="col-md-3">
                          <input type="text" name="uname" class="form-control mr-2" placeholder="User Name" value="{{ request('uname') }}">
                        </div>

                        <div class="col-md-3">
                          <input type="text" name="uemail" class="form-control mr-2" placeholder="User Email" value="{{ request('uemail') }}">
                        </div>

                        <div class="col-md-2">
                          <input type="text" name="uphone" class="form-control mr-2" placeholder="User Phone Number" value="{{ request('uphone') }}">
                        </div>

                        <div class="col-md-2">
                          <select name="ustatus" class="form-control select2bs4 select2_dropdown" style="width: 100%;">
                            <option value="">Select Status</option>
                            @foreach($statusArray as $status_key => $status_val)
                              <option value="{{ $status_key }}" @if( request('ustatus', '') == $status_key ) selected @endif>{{ $status_val }}</option>
                            @endforeach
                          </select>
                        </div>

                        <div class="col-md-2">
                          <button type="submit" class="btn btn-primary">Search</button> &nbsp;
                          <a href="{{ route('admin.users.index') }}" class="btn btn-default">Clear</a>
                        </div>

                      </div>
                    </form>
                  </div>
                </div>
                {{-- +++++++++++++++++++ SEARCH RECORDS :: End +++++++++++++++++++ --}}

                @if( $users->count() > 0 )
                  <table class="table table-bordered table-hover table-striped projects">
                    <thead>
                      <tr>
                        <th>Profile Picture</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>DoB (d/m/Y)</th>
                        <th>Status</th>
                        <th>Action</th>
                      </tr>
                    </thead>
                    <tbody>
                      @foreach($users as $user)
                        @php
                            //$profile_pic_url = ( isset($user->profile_picture->name) ) ? url('images/' . config('filesystems.image_folder.200x160') . '/' . $user->profile_picture->name) : url('images/no-image.jpg');
                            $profile_pic_url = ( isset($user->profile_picture->name) ) ? generate_image_url($user->profile_picture->name) : no_image_url();

                            if(isset($user->profile_picture->name)){
                                $profile_pic_url_array = generate_image_url($user->profile_picture->name);
                                $profile_pic_url = $profile_pic_url_array['file_url_200x160'];
                            }
                            else{
                                $profile_pic_url = no_image_url();
                            }
                        @endphp
                        <tr>
                          <td>
                            <ul class="list-inline">
                              <li class="list-inline-item">
                                <img src="{{ $profile_pic_url }}" class="table-avatar" width="80" height="80" style="width:80px; height:80px;">
                              </li>
                            </ul>
                          </td>
                          <td>{{ $user->name }}</td>
                          <td>{{ $user->email }}
                            @if( !is_null($user->email_verified_at) )
                              <a class="ml-3" data-toggle="tooltip" data-placement="top" title="Email verified"><i class="fas fa-check"></i></a>
                            @else
                              <a dat class="ml-3" data-toggle="tooltip" data-placement="top" title="Email not verified"><i class="fas fa-times"></i></a>
                            @endif
                          </td>
                          <td>{{ $user->phone }}
                            @if( !is_null($user->phone_verified_at) )
                              <a data-t class="ml-3" data-toggle="tooltip" data-placement="top" title="Phone number verified"><i class="fas fa-check"></i></a>
                            @else
                              <a data-toggl class="ml-3" data-toggle="tooltip" data-placement="top" title="Phone number not verified"><i class="fas fa-times"></i></a>
                            @endif
                          </td>
                          <td>{{ \Carbon\Carbon::parse($user->date_of_birth)->format('d/m/Y') }}</td>
                          <td>{{ $statusArray[$user->status] }}</td>
                          <td>
                            <a href="{{ route('admin.users.show', $user->uuid) }}" data-toggle="tooltip" data-placement="top" title="View user details"><i class="fas fa-eye"></i></a>
                            &nbsp;&nbsp;&nbsp;
                            <a href="{{ route('admin.users.edit', $user->uuid) }}" data-toggle="tooltip" data-placement="top" title="Edit this User info"><i class="fas fa-edit"></i></a>
                            &nbsp;&nbsp;&nbsp;
                            <a href="javascript: void(0);" data-toggle="tooltip" data-placement="top" title="Delete this user" class="delete_user" data-route="{{ route('admin.users.destroy', $user->uuid) }}"><i class="fas fa-trash-alt"></i></a>
                          </td>
                        </tr>
                      @endforeach
                    </tbody>
                    <tfoot>
                      <tr>
                        <th>Profile Picture</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>DoB (d/m/Y)</th>
                        <th>Status</th>
                        <th>Action</th>
                      </tr>
                    </tfoot>
                  </table>
                @else
                  <p>No user found!</p>
                @endif
              </div>
              <!-- /.card-body -->

              <div class="card-footer clearfix">
                <div class="pagination-sm m-0 float-right">
                  {{ $users->withQueryString()->links() }}
                </div>
              </div>

            </div>
            <!-- /.card -->

          </div>
          <!-- /.col -->
        </div>
        <!-- /.row -->
      </div>
      <!-- /.container-fluid -->
    </section>
    <!-- /.content -->

  </div>
  <!-- /.content-wrapper -->
@endsection

@section('scripts')
<script src="{{ asset('js/bootstrap-toggle.min.js') }}"></script>
<!-- Select2 -->
<script src="{{ asset('admin/plugins/select2/js/select2.full.min.js') }}"></script>

<script>
  $(function () {
    select2_dropdown = $('.select2_dropdown');

    select2_dropdown.select2({
      theme: 'bootstrap4'
    });

    $.ajaxSetup({
      headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
      }
    });

    //+++++++++++++++++++ DELETE USER :: Start +++++++++++++++++++//
    $('.delete_user').on('click', function(){
      this_obj = $(this);
      data_route = this_obj.data('route');

      Swal.fire({
        title: 'Do you want to delete this user permanently?',
        showCancelButton: true,
        confirmButtonText: 'Yes',
        cancelButtonText: 'No',
      }).then((result) => {
        if (result.isConfirmed) {
          $.ajax({
            dataType: 'json',
            type: 'DELETE',
            url: data_route,
            success:function(data) {
            this_obj.bootstrapToggle('enable');
            if( data.status == 'failed' ){
                swal_fire_error(data.error.message);
                return false;
            }
            else if( data.status == 'success' ){
                swal_fire_success('User deleted successfully!');
                $(this_obj).parents('tr').fadeOut('slow');
            }
            }
          });
        }
      });
    });
    //+++++++++++++++++++ DELETE USER :: End +++++++++++++++++++//
  });
</script>
@endsection
