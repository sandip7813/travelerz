@php
    $profile_pic_url = ( isset($user->profile_picture->name) ) ? url('images/' . config('filesystems.image_folder.200x160') . '/' . $user->profile_picture->name) : url('images/no-image.jpg');
    $user_location = user_location($user->uuid);
@endphp
<!-- Profile Image -->
<div class="card card-primary card-outline">
    <div class="card-body box-profile">
      <div class="text-center">
        <img class="profile-user-img img-fluid img-circle"
             src="{{ $profile_pic_url }}"
             alt="{{ $user->name }}">
      </div>

      <h3 class="profile-username text-center">{{ $user->name }}</h3>

      @if( $user_location != '' )
      <p class="text-muted text-center">
        <i class="fas fa-map-marker-alt mr-1"></i> {{ $user_location }}
      </p>
      @endif

      <ul class="list-group list-group-unbordered mb-3">
        <li class="list-group-item">
          <b>Followers</b> <a class="float-right">{{ $user->followers->count() }}</a>
        </li>
        <li class="list-group-item">
          <b>Following</b> <a class="float-right">{{ $user->followings->count() }}</a>
        </li>
        <li class="list-group-item">
          <b>Friends</b> <a class="float-right">{{ $user->friends->count() }}</a>
        </li>
      </ul>

    </div>
    <!-- /.card-body -->
  </div>
  <!-- /.card -->

  <!-- Status Box -->
  <div class="card card-primary">
    <div class="card-header">
      <h3 class="card-title">Status</h3>
    </div>
    <!-- /.card-header -->
    <div class="card-body">
      <strong><i class="fas fa-info-circle"></i> {{ $statusArray[$user->status] }}</strong>
      <hr>

      <strong><i class="fas fa-envelope"></i> {{ $user->email }}</strong>
      <p class="text-muted">
        @if( is_null($user->email_verified_at) )
          Not Verified
        @else
          Verified on {{ $user->email_verified_at }}
        @endif
      </p>

      <hr>

      <strong><i class="fas fa-phone-square"></i> {{ $user->phone }}</strong>
      <p class="text-muted">
        @if( is_null($user->phone_verified_at) )
          Not Verified
        @else
          Verified on {{ $user->phone_verified_at }}
        @endif
      </p>

    </div>
    <!-- /.card-body -->
  </div>
  <!-- /.card -->

  <div class="card card-primary">
    <div class="card-header">
      <h3 class="card-title">Other Info</h3>
    </div>
    <!-- /.card-header -->
    <div class="card-body">
      @if($user->gender)
      <strong><i class="fas fa-venus-mars"></i> {{ $user->gender }}</strong>
      <hr>
      @endif

      <strong><i class="fas fa-calendar-alt"></i> {{ $user->date_of_birth }}</strong>

    </div>
    <!-- /.card-body -->
  </div>
  <!-- /.card -->
