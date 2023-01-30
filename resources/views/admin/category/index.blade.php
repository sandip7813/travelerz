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
            <h1 class="m-0">Manage Categories</h1>
          </div><!-- /.col -->
          <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
              <li class="breadcrumb-item active">Manage Categories</li>
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
                    <form action="{{ route('admin.category.index') }}" method="get">
                      <div class="row">
                        <div class="col-md-5">
                          <input type="text" name="cat_title" class="form-control mr-2" placeholder="Category Name" value="{{ request('cat_title') }}">
                        </div>

                        <div class="col-md-2">
                          <select name="cat_status" class="form-control select2bs4 select2_dropdown" style="width: 100%;">
                            <option value="">Select Status</option>
                            @foreach($statusArray as $status_key => $status_val)
                              <option value="{{ $status_key }}" @if( request('cat_status', '') == $status_key ) selected @endif>{{ $status_val }}</option>
                            @endforeach
                          </select>
                        </div>

                        <div class="col-md-2">
                          <button type="submit" class="btn btn-primary">Search</button> &nbsp;
                          <a href="{{ route('admin.category.index') }}" class="btn btn-default">Clear</a>
                        </div>

                        <div class="col-md-3"></div>
                      </div>
                    </form>
                  </div>
                </div>
                {{-- +++++++++++++++++++ SEARCH RECORDS :: End +++++++++++++++++++ --}}

                @if( $categories->count() > 0 )
                  <table class="table table-bordered table-hover table-striped projects">
                    <thead>
                      <tr>
                        <th>Icon</th>
                        <th>Title</th>
                        <th>Slug</th>
                        <th class="no-sort">Status</th>
                        <th class="no-sort">Action</th>
                      </tr>
                    </thead>
                    <tbody>
                      @foreach($categories as $cat)
                        @php
                          $icon_url = ( isset($cat->icon_image->name) ) ? url('images/' . config('filesystems.image_folder.icon-files') . '/' . $cat->icon_image->name) : url('images/no-image.jpg');
                        @endphp
                        <tr>
                        <td>
                            <ul class="list-inline">
                              <li class="list-inline-item">
                                <img src="{{ $icon_url }}" class="table-avatar" width="80" height="80" style="width:80px; height:80px;">
                              </li>
                            </ul>
                          </td>
                          <td>{{ $cat->name }}</td>
                          <td>{{ $cat->slug }}</td>
                          <td>
                            <input type="checkbox" name="cat_status" class="status_toggle" data-onstyle="success" data-offstyle="danger" data-on="Active" data-off="Inactive" data-size="mini" data-width="80" data-uuid="{{ $cat->uuid }}" @if($cat->status == 1) checked @endif data-toggle="toggle">
                          </td>
                          <td>
                            <a href="{{ route('admin.category.edit', $cat->uuid) }}" data-toggle="tooltip" data-placement="top" title="Edit this Category info"><i class="fas fa-edit"></i></a>
                            &nbsp;&nbsp;&nbsp;
                            <a href="javascript: void(0);" data-toggle="tooltip" data-placement="top" title="Delete this Category" class="delete_category" data-route="{{ route('admin.category.destroy', $cat->uuid) }}"><i class="fas fa-trash-alt"></i></a>
                          </td>
                        </tr>
                      @endforeach
                    </tbody>
                    <tfoot>
                      <tr>
                        <th>Icon</th>
                        <th>Title</th>
                        <th>Slug</th>
                        <th>Status</th>
                        <th>Action</th>
                      </tr>
                    </tfoot>
                  </table>
                @else
                  <p>No Category found!</p>
                @endif
              </div>
              <!-- /.card-body -->

              <div class="card-footer clearfix">
                <div class="pagination-sm m-0 float-right">
                  {{ $categories->withQueryString()->links() }}
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
    //+++++++++++++++++++ UPDATE CATEGORY STATUS :: Start +++++++++++++++++++//
    $(".status_toggle").on('change', function(){
      this_obj = $(this);
      uuid = this_obj.data('uuid');
      this_obj.bootstrapToggle('disable');

      $.ajax({
        dataType: 'json',
        type: 'POST',
              data:{
                uuid: uuid
              },
              url: "{{ route('admin.category.change-status') }}",
              success:function(data) {
                this_obj.bootstrapToggle('enable');
                if( data.status == 'failed' ){
                  swal_fire_error(data.error.message);
                  return false;
                }
                else if( data.status == 'success' ){
                  swal_fire_success('Category status updated successfully!');
                }
              }
      });
    });
    //+++++++++++++++++++ UPDATE CATEGORY STATUS :: End +++++++++++++++++++//

    //+++++++++++++++++++ DELETE CATEGORY :: Start +++++++++++++++++++//
    $('.delete_category').on('click', function(){
      this_obj = $(this);
      data_route = this_obj.data('route');

      Swal.fire({
        title: 'Do you want to delete this category permanently?',
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
                swal_fire_success('Category deleted successfully!');
                $(this_obj).parents('tr').fadeOut('slow');
            }
            }
          });
        }
      });
    });
    //+++++++++++++++++++ DELETE CATEGORY :: End +++++++++++++++++++//
  });
</script>
@endsection