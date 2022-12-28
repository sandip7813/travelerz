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
                <form id="add-category-form" action="javascript: void(0);">
                    <div class="card-body" id="category_title_wrap"></div>

                    <!-- /.card-body -->
                    <div class="card-footer">
                        <button type="button" class="btn btn-info" data-toggle="tooltip" data-placement="top" title="Add a new row" onclick="add_row_html()"><i class="fas fa-plus-square"></i>&nbsp;&nbsp;Add More</button>
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
    $('#add-category-submit').on('click', function(){
      this_obj = $(this);
      var category_title = [];
      category_title_field = $("input[name='category_title[]']");
      if( category_title_field.length == 0 ){
        swal_fire_error('No category title found!');
        return false;
      }
      category_title_field.each(function() {
          var value = $(this).val();
          if (value) {
              category_title.push(value);
          }
      });
      if (category_title.length === 0) {
        swal_fire_error('No category title found!');
        return false;
      }
      this_obj.html('<i class="fa fa-spinner" aria-hidden="true"></i> Processing');
      $('.btn').attr('disabled', true);
      //
      $.ajaxSetup({
        headers: {
          'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
      });
      $.ajax({
        dataType: 'json',
        type: 'POST',
        data:{
          category_title: category_title
        },
        url: "{{ route('admin.category.store') }}",
        success:function(data) {
          if( data.status == 'failed' ){
            swal_fire_error(data.error.message);
            return false;
          }
          else if( data.status == 'success' ){
            swal_fire_success('Categories created successfully!');
            $('#category_title_wrap').html('');
            add_row_html();
          }
          $('.btn').attr('disabled', false);
          this_obj.html('Submit');
        }
      });
      //
    });
  });
  add_row_html();
  function add_row_html(){
    html_string = '<div class="input-group mb-3 title_row">';
    html_string += '<input type="text" name="category_title[]" class="form-control" placeholder="Category Title">';
    html_string += '<div class="input-group-append">';
    html_string += '<span class="input-group-text"><a href="javascript:void(0);" data-toggle="tooltip" data-placement="top" title="Remove this row" onclick="remove_row(this)"><i class="fas fa-trash-alt"></i></a></span>';
    html_string += '</div>';
    html_string += '</div>';
    $('#category_title_wrap').append(html_string);
  }
  function remove_row(this_obj){
    category_title_field = $("input[name='category_title[]']");
    if( category_title_field.length == 1 ){
      swal_fire_error('You can\'t delete all category titles!');
      return false;
    }
    Swal.fire({
      title: 'Do you want to delete this row?',
      showCancelButton: true,
      confirmButtonText: 'Yes, Delete',
    }).then((result) => {
      if (result.isConfirmed) {
        $(this_obj).parents('.title_row').remove();
      }
    });
  }
</script>
@endsection