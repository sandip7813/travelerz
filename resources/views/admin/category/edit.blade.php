@extends('admin.layouts.app')

@section('styles')
<!-- iCheck for checkboxes and radio inputs -->
<link rel="stylesheet" href="{{ asset('admin/plugins/icheck-bootstrap/icheck-bootstrap.min.css') }}">
@endsection

@section('content')
  <!-- Content Wrapper. Contains page content -->
  <div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <div class="content-header">
      <div class="container-fluid">
        <div class="row mb-2">
          <div class="col-sm-6">
            <h1 class="m-0">Edit Category Details</h1>
          </div><!-- /.col -->
          <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
              <li class="breadcrumb-item"><a href="{{ route('admin.category.index') }}">Category</a></li>
              <li class="breadcrumb-item active title_wrap">{{ $category->name }}</li>
            </ol>
          </div><!-- /.col -->
        </div><!-- /.row -->
      </div><!-- /.container-fluid -->
    </div>
    <!-- /.content-header -->

    <!-- Main content -->
    <section class="content">
      <div class="container-fluid">
        <div class="row">
          <div class="col-md-2"></div>

          <div class="col-md-8">

            <!-- Input addon -->
            <div class="card card-info">
                <div class="card-header">
                    <h3 class="card-title title_wrap">{{ $category->name }}</h3>
                </div>
                <form id="edit-category-form" action="{{ route('admin.category.update', $category->uuid) }}">
                    <div class="card-body">
                        <label>Category Title</label>
                        <div class="input-group mb-3 title_row">
                            <input type="text" name="category_name" class="form-control mr-2" value="{{ $category->name }}" placeholder="Category Title">
                        </div>

                        <label>Status</label>
                        <div class="input-group mb-3 title_row">
                            <div class="icheck-success d-inline mr-5">
                                <input type="radio" name="category_status" id="status_active" value="1" @if($category->status == '1')checked @endif>
                                <label for="status_active">Active</label>
                            </div>
                            <div class="icheck-danger d-inline">
                                <input type="radio" name="category_status" id="status_inactive" value="0" @if($category->status == '0')checked @endif>
                                <label for="status_inactive">Inactive</label>
                            </div>
                        </div>
                    </div>

                    <!-- /.card-body -->
                    <div class="card-footer">
                        <button type="submit" class="btn btn-success float-right" id="update-category-submit">Update</button>
                    </div>
                </form>
            </div>
            <!-- /.card -->
          </div>
          <!--/.col (left) -->

          <div class="col-md-2"></div>

        </div>
        <!-- /.row -->
      </div><!-- /.container-fluid -->
    </section>
    <!-- /.content -->
  </div>
  <!-- /.content-wrapper -->
@endsection

@section('scripts')
<script>
  $(function () {
    $.ajaxSetup({
      headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
      }
    });

    //++++++++++++++++++++ SUBMIT FORM :: Start ++++++++++++++++++++//
    $('#edit-category-form').submit(function(e){
      update_category_btn = $('#update-category-submit');

      category_name = $('input[name="category_name"]').val().trim();
      category_status = $('input[name="category_status"]:checked').val();

      form_action = $(this).attr('action');

      e.preventDefault();

      update_category_btn.html('<i class="fa fa-spinner" aria-hidden="true"></i> Updating...').attr('disabled', true);

      //
      $.ajax({
        dataType: 'json',
        type: 'PATCH',
        data: {
            'category_name': category_name,
            'category_status': category_status,
        },
        url: form_action,
        success:function(data) {
          update_category_btn.html('Update').attr('disabled', false);

          if( data.status == 'failed' ){
            swal_fire_error(data.error.message);
            return false;
          }
          else if( data.status == 'success' ){
            swal_fire_success('Category info updated successfully!');

            $('.title_wrap').html(category_name);
          }

          $('.btn').attr('disabled', false);
          update_category_btn.html('Submit');
        }
      });
      //
    });
    //++++++++++++++++++++ SUBMIT FORM :: End ++++++++++++++++++++//
    
  });


</script>
@endsection
