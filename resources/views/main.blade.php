@extends('layouts.app')

@section('head')
<title> Picture </title>
<link type="text/css" rel='stylesheet' href="/css/main.css" />

<link href="/css/elusive-icons.min.css" rel="stylesheet">
@endsection

@section('content')
    <div id="show_websites"></div>

    <div id="page_container">
      <div id="index_header">
        <div>
          <br />
          <div class="container">
            <div class="row">
              <div class="col-md-8 col-md-push-4 center">
                <div class="separator hidden-xs hidden-sm"></div>
                <div>
                  <p class="header-white wallpaper-header">PICTURE</p>
                  <p class="header-white">COLLECTION</p>
                </div>
                <div class="separator hidden-xs hidden-sm"></div>
                <h1 class="header-text">
                  Home To <span class="header-blue">{{$about['totalpic']}}</span> Pictures
                </h1>
                <br />
                <form action="search" method="GET">
                  <div class="input-group" id="search_zone_index">
                    <input
                      name="keyword"
                      class="search-bar form-control input-lg"
                      placeholder="Picture Search"
                      type="text"
                    />
                    <span class="input-group-btn">
                      <button
                        type="submit"
                        class="btn-search-bar btn btn-default btn-lg"
                      >
                        <i class="el el-search"></i>
                      </button>
                    </span>
                  </div>
                </form>
                <h4 class="center">
                  Search for picture! Video Game Images, Car Pictures, etc
                </h4>
              </div>
              <div class="col-md-4 col-md-pull-8">
                <img
                  width="360"
                  height="471"
                  class="img-responsive alpha-coders-logo"
                  src="/img/donald.png"
                />
              </div>
            </div>
          </div>
        </div>
        <br />

      </div>
      <div class="white-separator"></div>

      <div class="center">
        <br />

        <h1>Below Are Some of Newest Pictures</h1>
        <div class="white-separator"></div>

        <div class="center">
            @foreach ($picturelist as $picture)
    <div class="thumb-container-big " id="{{'thumb_'.$picture['id']}}">
        <div class='thumb-container'>
            <div class='boxgrid'>
                <a href="{{asset('picture/show/'.$picture['id'])}}" title="{{$picture['subcategory']['category'].' '.$picture['subcategory']['name'].' '.$picture['tagstring']}} HD Picture | Image">
                    <img class="lazy-load" data-src="{{asset($picture['thumb'])}}" alt="HD Picture | Image ID:{{$picture['id']}}">
                </a>
            </div>
            <div class="boxcaption">
                <span class='thumb-info-big'>
                    <span>{{$picture['resolution']}}</span>&nbsp;&nbsp;
                        <a href="{{ asset('by_category/'.$picture['subcategory']['category_id']) }}" title="{{$picture['subcategory']['category']}} HD Pictures | Images">{{$picture['subcategory']['category']}}</a>&nbsp;&nbsp;
                        <a href="{{ asset('by_subcategory/'.$picture['subcategory']['id']) }}" title="{{$picture['subcategory']['name']}} HD Pictures | Images">{{$picture['subcategory']['name']}}</a>
                    </span>
                    <br/>
                    <div class="overlay">
                            <div>
                            <span title="Download Picture" class="btn btn-primary btn-block download-button" data-id="{{$picture['id']}}">
                                <i class="el el-download-alt"></i>
                            </span>
                            </div>
                            <div>
                            <span class='btn btn-user' onClick="linkToProfile({{$picture['uploader']['id']}}); return false;" title="{{$picture['uploader']['username']}}&#039;s Picture Submissions">
                                <img class="lazy-load user-avatar" data-src="{{asset($picture['uploader']['avatar'])}}" alt="">&nbsp;<span class='align-middle'>{{$picture['uploader']['username']}}</span>
                            </span>
                            </div>
                        </div>
                        <div class='thumb-stats' >
                            <span title="Views">
                            <i class="el el-eye-open"></i> {{$picture['view']}}
                        </span>
                            &nbsp;&nbsp;&nbsp;
                            <span title="Download">
                            <i class="el el-download-alt"></i> {{$picture['download']}}
                        </div>
                    </div>
                </div>
                <div class='tags-info'>
                    @foreach ($picture["tag"] as $tags)
                    <a href="{{asset('/by_tag/'.$tags['tag_id'])}}" title="{{$tags['name']}} HD Pictures | Images">{{$tags['name']}}</a>&nbsp; &nbsp;
                    @endforeach
                </div>
            </div>
            @endforeach

        </div>

      </div>

      <br />
      <a id="more_nav"></a>
      <div class="center">
        <h1>Finding Pictures &amp; More!</h1>
      </div>
      <div class="white-separator"></div>

      <div id="container_more_nav">
        <div class="container-more">
          <div class="finding-compact-container">
            <ul class="nav nav-tabs bold center">
              <li class="active">
                <a class="light-blue" href="#categories" data-toggle="tab"
                  >Categories</a
                >
              </li>
              <li>
                <a class="light-blue" href="#resolutions" data-toggle="tab"
                  >Resolutions</a
                >
              </li>
            </ul>

            <div class="tab-content">

              <div class="tab-pane active" id="categories">
                  
                @foreach ($categorylist as $category)
                  <a
              href="/by_category/{{$category['id']}}/1"
                  class="list-group-item"
                  title="{{$category['name']}} HD Picture | Background Images"
                >
                  <span class="badge">{{$category['numofpicture']}}</span>
                  <strong>{{$category['name']}}</strong>
                </a>
                @endforeach

              </div>

              <div class="tab-pane" id="resolutions">
                <span id="visitor_resolution"></span>
                @foreach ($resolutionlist as $resolution)
                <a
              href="by_resolution/{{$resolution['resolution']}}/1"
                  class="list-group-item"
                  title="{{$resolution['resolution']}} HD Picture | Background Images"
                >
                  <span class="badge">{{$resolution['numofpicture']}}</span>
                  <strong>{{$resolution['resolution']}}</strong>
                </a>
                @endforeach
              </div>
            </div>
          </div>
          <div class="popular-content">
            <div class="popular-content-container">
              <div>
                <h3 class="title-popular">Popular Subcategories</h3>
                <div class="popular-container">
                    
                    @foreach ($subcategorylist as $subcategory)
                    <a
                    class="btn btn-default"
                    title="{{$subcategory['name']}} HD Pictures | Background Images"
                    href="/by_subcategory/{{$subcategory['id']}}/1"
                  >
                    <span class="popular-name">{{$subcategory['name']}}</span>
                    <span class="badge">{{$subcategory['numofpicture']}}</span>
                  </a>
                  @endforeach
                </div>
                <div class="popular-link-all">
                  <a
                    class="btn btn-info btn-custom"
                    href="/subcategories"
                  >
                    <i class="el el-list"></i> View All Subcategories
                  </a>
                </div>
              </div>
              <div>
                <h3 class="title-popular">Popular Tags</h3>
                <div class="popular-container">
                    @foreach ($taglist as $tag)
                    <a
                    class="btn btn-default"
                    title="{{$tag['name']}} HD Pictures | Background Images"
                    href="/by_tag/{{$tag['id']}}/1"
                  >
                    <span class="popular-name">{{$tag['name']}}</span>
                    <span class="badge">{{$tag['numofpicture']}}</span>
                  </a>
                    @endforeach
                </div>
                <div class="popular-link-all">
                  <a
                    class="btn btn-info btn-custom"
                    href="/tags"
                  >
                    <i class="el el-list"></i> View All Tags
                  </a>
                </div>
              </div>
              <div>
                <h3 class="title-popular">Most Uploader</h3>
                <div class="popular-container">
                    @foreach ($userlist as $user)
                    <a
                    class="btn btn-default"
                    title="{{$user['username']}}'s Profile"
                    href="/users/profile/{{$user['id']}}"
                  >
                  <img class="lazy-load user-avatar" data-src="{{asset($user['avatar'])}}" alt="">
                    <span class="popular-name">&nbsp;{{$user['username']}}</span>
                    <span class="badge">{{$user['numofpicture']}}</span>
                  </a>
                  @endforeach
                </div>
                <br/>
              </div>
            </div>
          </div>
        </div>
      </div>

      <br />
      <div class="center">
        <h1>About Us</h1>
        <div class="white-separator"></div>
      </div>

      <div class="container-community">
        <div class="list-group flex-list-group">
          <div class="list-group-item list-group-item-info bold">
            Our Community
          </div>
          <div class="list-group-item">
          Registered Users<span class="floatright">{{$about['user']}}</span>
          </div>
          <div class="list-group-item">
            Picture<span class="floatright">{{$about['totalpic']}}</span>
          </div>
          <div class="list-group-item">
            Views<span class="floatright">{{$about['view']}}</span>
          </div>
          <div class="list-group-item">
            Downloads<span class="floatright">{{$about['download']}}</span>
          </div>
        </div>
      </div>
    </div>
@endsection

@section('script')
    <script src="/js/bootstrap-3.4.1.min.js"></script>
    <script src="{{ asset('/js/intersection-observer.min.js') }}"></script>
<script type="text/javascript">

    function handleIntersection(entries, observer) {
        entries.forEach(function(entry) {
            if(entry.intersectionRatio > 0) {
                loadImage(entry.target);
                observer.unobserve(entry.target);
            }
        });
    }

    function loadImage(image) {
        const src = image.dataset.src;

        image.src = src;
        image.classList.remove("lazy-load");
    }

    var io = new IntersectionObserver(handleIntersection,
        {
            rootMargin: "400px 0px",
            threshold: [0.01]
        }
    );

    let lazy_load_images = document.querySelectorAll(".lazy-load");

    for(var i = 0; i < lazy_load_images.length; ++i) {
        io.observe(lazy_load_images[i]);
    }
</script>

        <script type="text/javascript">
        var text = "Your Resolution";
        var ratio = window.devicePixelRatio || 1;

        if(ratio == 1) {
            var width = window.screen.width;
            var height = window.screen.height;
        }
        else {
            var width = Math.floor(window.screen.width * ratio);
            var height = Math.floor(window.screen.height * ratio);
        }

        if(parseInt(width.toString().split('').pop()) == 1 && parseInt(height.toString().split('').pop()) == 1) {
            width -= 1;
            height -= 1;
        }
        else if(width == 1242 && height == 2208 && ratio == 3) {
            width = 1080;
            height = 1920;
        }

        $('#visitor_resolution').replaceWith('<a class="list-group-item" title="' + text + ' (' + width + 'x' + height + ')" href="by_resolution/' + width + 'x' + height + '/1"><strong>' + text + ' (' + width + 'x' + height + ')</strong></a>');
    </script>

    <script>
    var the_top_string = "143px"; // 219 - 76
    var the_top_hidden_string = "219px";

    //Show/hide additional info for picture
    $( "body" ).on("mouseenter", ".thumb-container-big", function() {
        $(".boxcaption", this).stop().animate({top: the_top_string },{queue:false,duration:160});
        $('.tags-info', this).show();
    }).on("mouseleave", ".thumb-container-big", function() {
        $(".boxcaption", this).stop().animate({top: the_top_hidden_string },{queue:false,duration:160});
        $('.tags-info', this).hide();
    });
    
    function linkToProfile(user_id) {
        window.location.href = "/users/profile/" + user_id + "";
    }
</script>
<script>
    $('body').on('click', '.download-button',  function() {
      var element = $(this);
      var id = element.attr("data-id");
      window.location = "/picture/get-download-link/" + id;
    });
</script>
@endsection
