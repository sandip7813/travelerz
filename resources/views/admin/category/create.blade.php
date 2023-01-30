@extends('admin.layouts.app')

@section('styles')
<link rel="stylesheet" href="{{ asset('admin/plugins/sweetalert2-theme-bootstrap-4/bootstrap-4.min.css') }}">
@endsection

@section('content')
  <!-- Content Wrapper. Contains page content -->
  <div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <div class="content-header">
      <div class="container-fluid">
        <div class="row mb-2">
          <div class="col-sm-6">
            <h1 class="m-0">Add Category</h1>
          </div><!-- /.col -->
          <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
              <li class="breadcrumb-item"><a href="{{ route('admin.category.index') }}">Category</a></li>
              <li class="breadcrumb-item active">Add New</li>
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
          <div class="col-md-3"></div>

          <div class="col-md-6">

            <!-- Input addon -->
            <div class="card card-info">
                <div class="card-header">
                    <h3 class="card-title">Enter Category Title</h3>
                </div>
                <form id="add-category-form" action="javascript: void(0);" enctype="multipart/form-data">
                    <div class="card-body">
                        <label>Category Title</label>
                        <div class="input-group mb-3 title_row">
                            <input type="text" name="category_name" class="form-control mr-2" placeholder="Category Title">
                        </div>

                        <label>Category Icon</label>
                        <div class="input-group mb-3 title_row">
                          <input type="file" name="category_icon" class="form-control mr-2" placeholder="Category Icon">
                        </div>
                    </div>

                    <!-- /.card-body -->
                    <div class="card-footer">
                        <button type="submit" class="btn btn-success float-right" id="add-category-submit">Submit</button>
                    </div>
                </form>
            </div>
            <!-- /.card -->
          </div>
          <!--/.col (left) -->

          <div class="col-md-3"></div>

        </div>
        <!-- /.row -->
      </div><!-- /.container-fluid -->
    </section>
    <!-- /.content -->
  </div>
  <!-- /.content-wrapper -->
@endsection

@section('scripts')
<script src="{{ asset('js/sweetalert2@11.js') }}"></script>
<script src="{{ asset('admin/plugins/sweetalert2/sweetalert2.min.js') }}"></script>

<script>
  $(function () {
    category_form = $('#add-category-form');

    $.ajaxSetup({
      headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
      }
    });

    category_form.on('submit', function(e){
      e.preventDefault();

      submit_btn = $('#add-category-submit');

      var formData = new FormData(this);

      submit_btn.html('<i class="fa fa-spinner" aria-hidden="true"></i> Submitting...').attr('disabled', true);

      $.ajax({
        dataType: 'json',
        type: 'POST',
        data: formData ,
        url: "{{ route('admin.category.store') }}",
        cache: false,
        contentType: false,
        processData: false,
        success:function(data) {
          submit_btn.html('Submit').attr('disabled', false);

          if( data.status == 'failed' ){
            swal_fire_error(data.error.message);
            return false;
          }
          else if( data.status == 'success' ){
            swal_fire_success('Category created successfully!');
            category_form[0].reset();
          }
        }
      });
    })
  });
</script>
@endsection