@php
//echo '<pre>'; print_r($posts->toArray()); echo '</pre>';
@endphp

@forelse($posts as $post)
@php
$user_dp = $post->created_by->profile_picture->name ?? null;

if(!is_null($user_dp)){
    $dp_url_array = generate_image_url($user_dp);
    $dp_url = $dp_url_array['file_url_200x160'];
}
else{
    $dp_url = no_image_url();
}
@endphp
<div class="post">{{ $post->id }}
    <div class="user-block">
        <img class="img-circle img-bordered-sm" src="{{ $dp_url }}" alt="{{ $post->created_by->name }}">
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

    @php
    //echo '<pre>'; print_r($posts->toArray()); echo '</pre>';
    $post_pictures = $post->pictures ?? [];
    @endphp

    @if( count($post_pictures) > 0 )
    <div class="col-12">
        <div class="card card-primary">
          <div class="card-header">
            <h4 class="card-title">Gallery</h4>
          </div>
          <div class="card-body">
            <div class="row">
                @foreach($post_pictures as $pic)
                    @php
                        $image_url = generate_image_url($pic->name);
                    @endphp
                    <div class="col-sm-2">
                        <a href="{{ $image_url['file_url_main'] }}" data-toggle="lightbox" data-gallery="gallery">
                            <img src="{{ $image_url['file_url_200x160'] }}" class="img-fluid mb-2"/>
                        </a>
                    </div>
                @endforeach
            </div>
          </div>
        </div>
    </div>
    @endif

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
