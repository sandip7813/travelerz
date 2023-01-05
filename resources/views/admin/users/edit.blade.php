@extends('admin.layouts.app')

@php
$date_of_birth = ( !is_null($user->date_of_birth) ) ? \Carbon\Carbon::createFromFormat('Y-m-d', $user->date_of_birth)->format('d/m/Y') : null;
@endphp

@section('styles')
<!-- iCheck for checkboxes and radio inputs -->
<link rel="stylesheet" href="{{ asset('admin/plugins/icheck-bootstrap/icheck-bootstrap.min.css') }}">
<!-- daterange picker -->
<link rel="stylesheet" href="{{ asset('admin/plugins/daterangepicker/daterangepicker.css') }}">
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
            <h1 class="m-0">Edit User Details</h1>
          </div><!-- /.col -->
          <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
              <li class="breadcrumb-item"><a href="{{ route('admin.users.index') }}">Users</a></li>
              <li class="breadcrumb-item active title_wrap">{{ $user->name }}</li>
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
                    <h3 class="card-title title_wrap">{{ $user->name }} ({{ $user->email }})</h3>
                </div>
                <form id="edit-user-form" action="{{ route('admin.users.update', $user->uuid) }}">
                    <div class="card-body">
                        <label>User Name</label>
                        <div class="input-group mb-3 title_row">
                            <input type="text" name="user_name" class="form-control mr-2" value="{{ $user->name }}" placeholder="User Name">
                        </div>

                        <label>Gender</label>
                        <div class="input-group mb-3 title_row">
                            <div class="icheck-primary d-inline mr-5">
                                <input type="radio" name="gender" id="gender_male" value="male" @if($user->gender == 'male')checked @endif>
                                <label for="gender_male">Male</label>
                            </div>
                            <div class="icheck-primary d-inline mr-5">
                                <input type="radio" name="gender" id="gender_female" value="female" @if($user->gender == 'female')checked @endif>
                                <label for="gender_female">Female</label>
                            </div>
                            <div class="icheck-primary d-inline">
                                <input type="radio" name="gender" id="gender_other" value="other" @if($user->gender == 'other')checked @endif>
                                <label for="gender_other">Other</label>
                            </div>
                        </div>

                        <label>About User</label>
                        <div class="input-group mb-3 title_row">
                            <textarea class="form-control" rows="3" name="about_user" placeholder="About User">{{ $user->about_me }}</textarea>
                        </div>

                        <label>Date of Birth</label>
                        <div class="input-group mb-3 title_row">
                            <div class="input-group-prepend">
                              <span class="input-group-text">
                                <i class="far fa-calendar-alt"></i>
                              </span>
                            </div>
                            <input type="text" class="form-control float-right" name="date_of_birth" id="date_of_birth" value="{{ $date_of_birth }}">
                        </div>
                        <!-- /.input group -->

                        <label>Location</label>
                        <div class="input-group mb-3 title_row">
                          
                          <div class="col-md-4">
                            <select name="country" id="country" class="form-control select2">
                              <option value=""> - Select Country - </option>
                              @foreach( country_list() as $country )
                                <option value="{{ $country->id }}" @if( $user->country_id == $country->id ) selected @endif>{{ $country->name }}</option>
                              @endforeach
                            </select>
                          </div>

                          <div class="col-md-4">
                            <select name="state" id="state" class="form-control select2">
                              <option value=""> - Select State / Province - </option>
                            </select>
                          </div>

                          <div class="col-md-4">
                            <input type="text" name="city" class="form-control" placeholder="City" value="{{ $user->city }}">
                          </div>
                        </div>

                        <label>Status</label>
                        <div class="input-group mb-3 title_row">
                            <div class="icheck-success d-inline mr-5">
                                <input type="radio" name="user_status" id="status_active" value="1" @if($user->status == '1')checked @endif>
                                <label for="status_active">Active</label>
                            </div>
                            <div class="icheck-danger d-inline">
                                <input type="radio" name="user_status" id="status_inactive" value="2" @if($user->status == '2')checked @endif>
                                <label for="status_inactive">Inactive</label>
                            </div>
                        </div>
                    </div>

                    <!-- /.card-body -->
                    <div class="card-footer">
                        <button type="submit" class="btn btn-success float-right" id="update-user-submit">Update</button>
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
<script src="{{ asset('admin/plugins/moment/moment.min.js') }}"></script>
<!-- date-picker -->
<script src="{{ asset('admin/plugins/inputmask/jquery.inputmask.min.js') }}"></script>
<!-- date-range-picker -->
<script src="{{ asset('admin/plugins/daterangepicker/daterangepicker.js') }}"></script>
<!-- Select2 -->
<script src="{{ asset('admin/plugins/select2/js/select2.full.min.js') }}"></script>

<script>
  $(function () {
    $('.select2').select2({
      theme: 'bootstrap4'
    });
    
    $('#date_of_birth').daterangepicker({
        singleDatePicker: true,
        showDropdowns: true,
        minYear: 1901,
        maxYear: parseInt(moment().format('YYYY'),10),
        locale: {
          format: 'DD/MM/YYYY'
        }
    }, function(start, end, label) {
        var years = moment().diff(start, 'years');
        alert("You are " + years + " years old!");
    });

    $.ajaxSetup({
      headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
      }
    });

    //++++++++++++++++++++ GENERATE STATE LIST :: Start ++++++++++++++++++++//
    $('#country').on('change', function(){
      selected_country = $(this).val();

      $.ajax({
            dataType: 'json',
            data: {
                country: selected_country,
                state: '{{ $user->state_id }}'
            },
            url: "{{ route('admin.generate-state-list-dropdown') }}",
            success: function (data) {
                //console.log(data);
                if (data.status == 'success') {
                  $('#state').html(data.dropdown_html);

                  if( data.total_states == 0 ){
                    $('input[name="city"]').val('');
                  }
                }
            }
      });
    }).trigger('change');
    //++++++++++++++++++++ GENERATE STATE LIST :: End ++++++++++++++++++++//

    $('#state').on('change', function(){
      $('input[name="city"]').val('');
    });

    //++++++++++++++++++++ SUBMIT FORM :: Start ++++++++++++++++++++//
    $('#edit-user-form').submit(function(e){
      update_user_btn = $('#update-user-submit');

      user_name = $('input[name="user_name"]').val().trim();
      
      form_val = $(this).serialize();
      form_action = $(this).attr('action');

      e.preventDefault();

      update_user_btn.html('<i class="fa fa-spinner" aria-hidden="true"></i> Updating...').attr('disabled', true);

      //
      $.ajax({
        dataType: 'json',
        type: 'PATCH',
        data: form_val,
        url: form_action,
        success:function(data) {
            update_user_btn.html('Update').attr('disabled', false);

          if( data.status == 'failed' ){
            swal_fire_error(data.error.message);
            return false;
          }
          else if( data.status == 'success' ){
            swal_fire_success('User info updated successfully!');

            $('.title_wrap').html(user_name);
          }

          $('.btn').attr('disabled', false);
          update_user_btn.html('Submit');
        }
      });
      //
    });
    //++++++++++++++++++++ SUBMIT FORM :: End ++++++++++++++++++++//
    
  });


</script>
@endsection