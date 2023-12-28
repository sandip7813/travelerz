@php
echo '<pre>'; print_r($posts->toArray()); echo '</pre>';
@endphp

@forelse($posts as $post)
<div class="post">
    <div class="user-block">
        <img class="img-circle img-bordered-sm" src="../../dist/img/user1-128x128.jpg" alt="user image">
        <span class="username">
        <a>{{ $user->name }}</a>
        @if($post->location)
            <a class="float-right btn-tool"><i class="fa fa-map-marker" aria-hidden="true"></i> {{ $post->location }}</a>
        @endif
        </span>
        <span class="description">{{ $post->created_at }}</span>
    </div>
    <!-- /.user-block -->
    <p>
        {!! $post->content !!}
    </p>

    <div class="col-12">
        <div class="card card-primary">
          <div class="card-header">
            <h4 class="card-title">Ekko Lightbox</h4>
          </div>
          <div class="card-body">
            <div class="row">
              <div class="col-sm-2">
                <a href="https://via.placeholder.com/1200/FFFFFF.png?text=1" data-toggle="lightbox" data-title="sample 1 - white" data-gallery="gallery">
                  <img src="https://via.placeholder.com/300/FFFFFF?text=1" class="img-fluid mb-2" alt="white sample"/>
                </a>
              </div>
              <div class="col-sm-2">
                <a href="https://via.placeholder.com/1200/000000.png?text=2" data-toggle="lightbox" data-title="sample 2 - black" data-gallery="gallery">
                  <img src="https://via.placeholder.com/300/000000?text=2" class="img-fluid mb-2" alt="black sample"/>
                </a>
              </div>
              <div class="col-sm-2">
                <a href="https://via.placeholder.com/1200/FF0000/FFFFFF.png?text=3" data-toggle="lightbox" data-title="sample 3 - red" data-gallery="gallery">
                  <img src="https://via.placeholder.com/300/FF0000/FFFFFF?text=3" class="img-fluid mb-2" alt="red sample"/>
                </a>
              </div>
              <div class="col-sm-2">
                <a href="https://via.placeholder.com/1200/FF0000/FFFFFF.png?text=4" data-toggle="lightbox" data-title="sample 4 - red" data-gallery="gallery">
                  <img src="https://via.placeholder.com/300/FF0000/FFFFFF?text=4" class="img-fluid mb-2" alt="red sample"/>
                </a>
              </div>
              <div class="col-sm-2">
                <a href="https://via.placeholder.com/1200/000000.png?text=5" data-toggle="lightbox" data-title="sample 5 - black" data-gallery="gallery">
                  <img src="https://via.placeholder.com/300/000000?text=5" class="img-fluid mb-2" alt="black sample"/>
                </a>
              </div>
              <div class="col-sm-2">
                <a href="https://via.placeholder.com/1200/FFFFFF.png?text=6" data-toggle="lightbox" data-title="sample 6 - white" data-gallery="gallery">
                  <img src="https://via.placeholder.com/300/FFFFFF?text=6" class="img-fluid mb-2" alt="white sample"/>
                </a>
              </div>
              <div class="col-sm-2">
                <a href="https://via.placeholder.com/1200/FFFFFF.png?text=7" data-toggle="lightbox" data-title="sample 7 - white" data-gallery="gallery">
                  <img src="https://via.placeholder.com/300/FFFFFF?text=7" class="img-fluid mb-2" alt="white sample"/>
                </a>
              </div>
              <div class="col-sm-2">
                <a href="https://via.placeholder.com/1200/000000.png?text=8" data-toggle="lightbox" data-title="sample 8 - black" data-gallery="gallery">
                  <img src="https://via.placeholder.com/300/000000?text=8" class="img-fluid mb-2" alt="black sample"/>
                </a>
              </div>
              <div class="col-sm-2">
                <a href="https://via.placeholder.com/1200/FF0000/FFFFFF.png?text=9" data-toggle="lightbox" data-title="sample 9 - red" data-gallery="gallery">
                  <img src="https://via.placeholder.com/300/FF0000/FFFFFF?text=9" class="img-fluid mb-2" alt="red sample"/>
                </a>
              </div>
              <div class="col-sm-2">
                <a href="https://via.placeholder.com/1200/FFFFFF.png?text=10" data-toggle="lightbox" data-title="sample 10 - white" data-gallery="gallery">
                  <img src="https://via.placeholder.com/300/FFFFFF?text=10" class="img-fluid mb-2" alt="white sample"/>
                </a>
              </div>
              <div class="col-sm-2">
                <a href="https://via.placeholder.com/1200/FFFFFF.png?text=11" data-toggle="lightbox" data-title="sample 11 - white" data-gallery="gallery">
                  <img src="https://via.placeholder.com/300/FFFFFF?text=11" class="img-fluid mb-2" alt="white sample"/>
                </a>
              </div>
              <div class="col-sm-2">
                <a href="https://via.placeholder.com/1200/000000.png?text=12" data-toggle="lightbox" data-title="sample 12 - black" data-gallery="gallery">
                  <img src="https://via.placeholder.com/300/000000?text=12" class="img-fluid mb-2" alt="black sample"/>
                </a>
              </div>
            </div>
          </div>
        </div>
    </div>

    <p>
        <a class="link-black text-sm mr-2"><i class="fas fa-share mr-1"></i> Shared ({{ $post->shared_count }})</a>
        <a class="link-black text-sm"><i class="far fa-thumbs-up mr-1"></i> Liked ({{ $post->likes_count }})</a>
        <span class="float-right">
            <a class="link-black text-sm">
                <i class="far fa-comments mr-1"></i> Comments ({{ $post->comments_count }})
            </a>
        </span>
    </p>
</div>
@empty
    <p>No post to show!</p>
@endforelse


    {{-- <div class="user-block">
      <img class="img-circle img-bordered-sm" src="../../dist/img/user1-128x128.jpg" alt="user image">
      <span class="username">
        <a href="#">Jonathan Burke Jr.</a>
        <a href="#" class="float-right btn-tool"><i class="fas fa-times"></i></a>
      </span>
      <span class="description">Shared publicly - 7:30 PM today</span>
    </div>
    <!-- /.user-block -->
    <p>
      Lorem ipsum represents a long-held tradition for designers,
      typographers and the like. Some people hate it and argue for
      its demise, but others ignore the hate as they create awesome
      tools to help create filler text for everyone from bacon lovers
      to Charlie Sheen fans.
    </p>

    <p>
      <a href="#" class="link-black text-sm mr-2"><i class="fas fa-share mr-1"></i> Share</a>
      <a href="#" class="link-black text-sm"><i class="far fa-thumbs-up mr-1"></i> Like</a>
      <span class="float-right">
        <a href="#" class="link-black text-sm">
          <i class="far fa-comments mr-1"></i> Comments (5)
        </a>
      </span>
    </p>

    <input class="form-control form-control-sm" type="text" placeholder="Type a comment">
  </div>
  <!-- /.post -->

  <!-- Post -->
  <div class="post clearfix">
    <div class="user-block">
      <img class="img-circle img-bordered-sm" src="../../dist/img/user7-128x128.jpg" alt="User Image">
      <span class="username">
        <a href="#">Sarah Ross</a>
        <a href="#" class="float-right btn-tool"><i class="fas fa-times"></i></a>
      </span>
      <span class="description">Sent you a message - 3 days ago</span>
    </div>
    <!-- /.user-block -->
    <p>
      Lorem ipsum represents a long-held tradition for designers,
      typographers and the like. Some people hate it and argue for
      its demise, but others ignore the hate as they create awesome
      tools to help create filler text for everyone from bacon lovers
      to Charlie Sheen fans.
    </p>

    <form class="form-horizontal">
      <div class="input-group input-group-sm mb-0">
        <input class="form-control form-control-sm" placeholder="Response">
        <div class="input-group-append">
          <button type="submit" class="btn btn-danger">Send</button>
        </div>
      </div>
    </form>
  </div>
  <!-- /.post -->

  <!-- Post -->
  <div class="post">
    <div class="user-block">
      <img class="img-circle img-bordered-sm" src="../../dist/img/user6-128x128.jpg" alt="User Image">
      <span class="username">
        <a href="#">Adam Jones</a>
        <a href="#" class="float-right btn-tool"><i class="fas fa-times"></i></a>
      </span>
      <span class="description">Posted 5 photos - 5 days ago</span>
    </div>
    <!-- /.user-block -->
    <div class="row mb-3">
      <div class="col-sm-6">
        <img class="img-fluid" src="../../dist/img/photo1.png" alt="Photo">
      </div>
      <!-- /.col -->
      <div class="col-sm-6">
        <div class="row">
          <div class="col-sm-6">
            <img class="img-fluid mb-3" src="../../dist/img/photo2.png" alt="Photo">
            <img class="img-fluid" src="../../dist/img/photo3.jpg" alt="Photo">
          </div>
          <!-- /.col -->
          <div class="col-sm-6">
            <img class="img-fluid mb-3" src="../../dist/img/photo4.jpg" alt="Photo">
            <img class="img-fluid" src="../../dist/img/photo1.png" alt="Photo">
          </div>
          <!-- /.col -->
        </div>
        <!-- /.row -->
      </div>
      <!-- /.col -->
    </div>
    <!-- /.row -->

    <p>
      <a href="#" class="link-black text-sm mr-2"><i class="fas fa-share mr-1"></i> Share</a>
      <a href="#" class="link-black text-sm"><i class="far fa-thumbs-up mr-1"></i> Like</a>
      <span class="float-right">
        <a href="#" class="link-black text-sm">
          <i class="far fa-comments mr-1"></i> Comments (5)
        </a>
      </span>
    </p>

    <input class="form-control form-control-sm" type="text" placeholder="Type a comment"> --}}
