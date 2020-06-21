@extends('layouts.app')

@section('head')
<title> Picture </title>
<link href="/css/bootstrap-3.3.7-custom.min.css" rel="stylesheet" media="screen">
<link href="/css/elusive-icons.min.css" rel="stylesheet">
<link rel="stylesheet" type="text/css" href="/css/show.css" />
<link rel="stylesheet" type="text/css" href="/css/jquery-ui.min.css" />
@endsection

@section('content')
<div id="page_container">
      <div class="custom-breadcrumb">
       <a class="breadcrumb-element breadcrumb-with-icon breadcrumb-blue" title="Picture" href="{{asset("/")}}">
            <span class="glyphicon glyphicon-home"></span> 
        </a>
        <a class="breadcrumb-element breadcrumb-with-icon" title="Picture" href="{{asset("/")}}">
            <span>Picture</span>
        </a>
    <a class="breadcrumb-element" title="{{$picture['subcategory']['category']}} HD Picture" href="/by_category/{{$picture['subcategory']['category_id']}}">
            <span>{{$picture['subcategory']['category']}}</span>
        </a>
        <a class="breadcrumb-element" title="{{$picture['subcategory']['name']}} HD Picture" href="/by_sub_category/{{$picture['subcategory']['id']}}">
            <span>{{$picture['subcategory']['name']}}</span>
        </a>

      </div>

      <div id="before_wallpaper_container">
        <div class="container">
            @if($picture['author'] != null)
            <div class="floatleft">
                <span class="author-container" id="author_container">
                    Author: {{$picture['author']}} <i class="el el-folder-open"></i>
                </span>
            </div>
            @endif

          <div class="tags-container">
            <div id="list_tags">
              <div class="tags-title">
                <i class="el el-tags"></i>
                <span>Tags:</span>
                @if (Auth::check())
                  @if(Auth::user()->type == "admin" or Auth::user()->id == $picture['uploader']['id'])
                  <a
                    id="edit_tags"
                    title="Edit Tags"
                    onclick="editTags(); return false;"
                  >
                    <i class="el el-wrench"></i>
                  </a>
                  @endif
                @endif
              </div>
              @foreach ($picture['tag'] as $item)
                <div class="tag-element">
                    <a href="/by_tag/{{$item['tag_id']}}">{{$item['name']}}</a>
                </div>
              @endforeach
            </div>
            <div id="new_tags">
              <div class="input-group">
                <input
                  type="text"
                  id="new_tag"
                  class="form-control"
                  placeholder="separate,tags,by,comma"
                />
                <button id="add_tag_btn" class="form-control">Add!</button>
                <div id="tag_suggetsion_container"></div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="center img-container-desktop">
      <a href="{{asset($picture['link'])}}">
          <picture>
            <source
              media="(max-width:350px)"
              srcset="{{asset($picture['link'])}}"
            />
            <source
              media="(max-width:500px)"
              srcset="{{asset($picture['link'])}}"
            />
            <img
              class="main-content"
              width="{{$picture['resolutionwh']['width']}}"
              height="{{$picture['resolutionwh']['height']}}"
              src="{{asset($picture['link'])}}"
              alt="Picture ID:{{$picture['tagstring']}}"
          title="{{$picture['subcategory']['category'].' '.$picture['subcategory']['name'].' '.$picture['tagstring']}} Picture"
            />
          </picture>
        </a>
      </div>

      <div id="after_wallpaper_container">
        <span>This is a preview! Click the picture to view full size</span>
        <div class="floatright">
          <a
            href="/by_category/{{$picture['subcategory']['category_id']}}"
            title="{{$picture['subcategory']['category']}} HD Picture"
          >
            <strong>{{$picture['subcategory']['category']}} </strong>
          </a>
          /
          <a
            title="{{$picture['subcategory']['name']}} HD Picture" href="/by_sub_category/{{$picture['subcategory']['id']}}"
          >
            <strong>{{$picture['subcategory']['name']}}</strong>
          </a>
        </div>
        <div class="clearfix"></div>
      </div>

      <div class="clearfix"></div>

      <div class="wallpaper-options">
        <div class="main-container">
          <span class="btn btn-success btn-custom download-button" data-id="{{$picture['id']}}">
            <i class="el el-download"></i>

            Download Original {{$picture['resolution']}}
          </span>

          <span id="crop_stretch_options"></span>

          <a class="btn btn-default btn-custom" onclick="showResolutions();">
            More Resolutions <i class="el el-resize-full"></i>
          </a>
        </div>
      </div>

      <div class="container">
        <div class="flex-wrapper">
          <div class="flex-item">
            <ul class="nav nav-tabs" role="tablist">
              <li class="active center">
                <a class="light-blue" href="#submitter-info" data-toggle="tab"
                  >Submission Info</a
                >
              </li>
              <li>
                <div class="btn-group">
                  <a
                    id="prev_page"
                    rel="nofollow"
                    class="btn btn-default prev-btn"
                    href="/picture/prev_next_nav?id={{$picture['id']}}&amp;dir=prev"
                  >
                    <i class="el el-chevron-left"></i> Previous
                  </a>
                  <button
                    type="button"
                    class="btn btn-default dropdown-toggle navigation-dropdown"
                    data-toggle="dropdown"
                  >
                    <span class="caret"></span>
                  </button>
                  <ul class="dropdown-menu navigation-dropdown">
                    <li>
                      <a
                        rel="nofollow"
                        class="sharing-icon"
                        href="/picture/prev_next_nav?id={{$picture['id']}}&amp;dir=prev&amp;scope=category&amp;scope_id={{$picture['subcategory']['category_id']}}"
                      >
                        <i class="el el-chevron-left"></i> Prev In Category
                      </a>
                    </li>
                    <li>
                      <a
                        rel="nofollow"
                        class="sharing-icon"
                        href="/picture/prev_next_nav?id={{$picture['id']}}&amp;dir=prev&amp;scope=subcategory&amp;scope_id={{$picture['subcategory']['id']}}"
                      >
                        <i class="el el-chevron-left"></i> Prev In Subcategory
                      </a>
                    </li>
                    <li>
                      <a
                        rel="nofollow"
                        class="sharing-icon"
                        href="/picture/prev_next_nav?id={{$picture['id']}}&amp;dir=prev&amp;scope=user&amp;scope_id={{$picture['uploader']['id']}}"
                      >
                        <i class="el el-chevron-left"></i> Prev By User
                      </a>
                    </li>
                  </ul>
                </div>
                <div class="btn-group pull-right">
                  <a
                    id="next_page"
                    rel="nofollow"
                    class="btn btn-default next-btn"
                    href="/picture/prev_next_nav?id={{$picture['id']}}&amp;dir=next"
                  >
                    Next <i class="el el-chevron-right"></i>
                  </a>
                  <button
                    type="button"
                    class="btn btn-default dropdown-toggle navigation-dropdown"
                    data-toggle="dropdown"
                  >
                    <span class="caret"></span>
                  </button>
                  <ul class="dropdown-menu navigation-dropdown floatright">
                    <li>
                      <a
                        rel="nofollow"
                        class="sharing-icon"
                        href="/picture/prev_next_nav?id={{$picture['id']}}&amp;dir=next&amp;scope=category&amp;scope_id={{$picture['subcategory']['category_id']}}"
                      >
                        Next In Category <i class="el el-chevron-right"></i>
                      </a>
                    </li>
                    <li>
                      <a
                        rel="nofollow"
                        class="sharing-icon"
                        href="/picture/prev_next_nav?id={{$picture['id']}}&amp;dir=next&amp;scope=subcategory&amp;scope_id={{$picture['subcategory']['id']}}"
                      >
                        Next In Subcategory <i class="el el-chevron-right"></i>
                      </a>
                    </li>
                    <li>
                      <a
                        rel="nofollow"
                        class="sharing-icon"
                        href="/picture/prev_next_nav?id={{$picture['id']}}&amp;dir=next&amp;scope=user&amp;scope_id={{$picture['uploader']['id']}}"
                      >
                        Next By User <i class="el el-chevron-right"></i>
                      </a>
                    </li>
                  </ul>
                </div>
              </li>
            </ul>

            <div class="tab-content">
              <div class="tab-pane active" id="submitter-info">
                <div class="panel panel-primary wallpaper-info-container">
                  <div class="submitter-wrapper">
                    <div class="submitter-info">
                      <div>
                        <b>
                          Shared By:
                        </b>
                        <div class="submitter-avatar">
                          <img
                            class="lazy-load"
                            alt="{{$picture['uploader']['username']}} - Avatar"
                            data-src="{{$picture['uploader']['avatar']}}"
                          />
                        </div>
                      </div>
                      <div>
                        <a
                          class="btn btn-default dropdown-toggle"
                          data-toggle="dropdown"
                        >
                          <i class="el el-user"></i> {{$picture['uploader']['username']}}
                          <span class="caret"></span>
                        </a>

                        <ul class="dropdown-menu">
                          <li>
                            <a
                              href="/users/profile/{{$picture['uploader']['id']}}"
                            >
                              <i class="el el-eye-open"></i> View Profile
                            </a>
                          </li>


                          <li>
                            <a>
                            <i class="el el-picture"></i> {{$picture['uploader']['numberofpicture']}} Pictures
                            </a>
                        </li>
                        </ul>
                      </div>
                    </div>

                    <div class="featured-stats-wrapper">
                      <div class="featured-stats-row">
                        <div class="featured-stat">
                          <h3 title="Views">
                            {{$picture['view']}}
                            <br />
                            <i class="el el-eye-open"></i>
                          </h3>
                        </div>
                        <div class="featured-stat">
                          <h3 title="Downloads">
                            {{$picture['download']}}
                            <br />
                            <i class="el el-download"></i>
                          </h3>
                        </div>
                      </div>
                    </div>
                  </div>
                  <table
                    class="table table-striped table-condensed"
                    id="wallpaper_info_table"
                  >
                    <tbody>

                      <tr>
                        <td style="max-width: 100px">
                          <span>
                            &nbsp;&nbsp;Gallery
                          </span>
                        </td>
                        <td>
                          <span>
                            <a
                              title=" {{$picture['subcategory']['name']}} HD Pictures, Images"
                              href="by_subcategory/{{$picture['subcategory']['id']}}"
                            >
                              {{$picture['subcategory']['name']}}
                            </a>
                          </span>
                        </td>
                      </tr>

                      <tr>
                        <td style="max-width: 100px">
                          <span>
                            &nbsp;&nbsp;<span class="inline-block">ID /</span>
                            <span class="inline-block">Resolution /</span>
                            <span class="inline-block">Size / Type</span>
                          </span>
                        </td>
                        <td>
                          <span>
                            <span class="inline-block">{{$picture['id']}} /</span>
                            <span class="inline-block">
                              <a
                                class="resolution-link"
                                href="by_resolution/{{$picture['resolution']}}"
                              >
                                {{$picture['resolution']}} <i class="el el-link"></i>
                              </a>
                              /
                            </span>
                            <span class="inline-block">{{$picture['size']}} / {{$picture['type']}}</span>
                          </span>
                        </td>
                      </tr>
                      <tr>
                        <td style="max-width: 100px">
                          <span>
                            &nbsp;&nbsp;Description
                          </span>
                        </td>
                        <td>
                          <span>
                              @if($picture['description'] == null)
                               . . . 
                               @else
                               {{$picture['description']}}
                               @endif
                          </span>
                        </td>
                      </tr>
                      <tr>
                        <td style="max-width: 100px">
                          <span>
                            &nbsp;&nbsp;Date Added
                          </span>
                        </td>
                        <td>
                          <span>
                            {{$picture['created_at']}}
                          </span>
                        </td>
                      </tr>

                      <tr>
                        <td style="max-width: 100px">
                          &nbsp;&nbsp;Main Colors
                        </td>
                        <td class="colors-container">
                          @foreach ($picture['color'] as $key => $color)
                              <a
                        class="color-infos color-infos{{$key+1}}"
                                style="background-color: {{$picture['color'][$key]['color']}};"
                                href="/by_color/{{trim($picture['color'][$key]['color'],"#")}}"
                              ></a>
                          @endforeach
                        </td>
                      </tr>
                      @if (Auth::check())
                      @if(Auth::user()->type == "admin" or Auth::user()->id == $picture['uploader']['id'])
                      <tr>
                                    <td>
                                        <span>&nbsp;&nbsp;
                                             Your Powers                                         </span>
                                    </td>
                                    <td>
                                            <a class="btn btn-danger btn-xs btn-power" target='_blank' id="deletepic">
                                                &nbsp;<i class="el el-remove-circle"></i> Delete Picture
                                            </a>
                                                                            </td>
                                </tr>
                      @endif
                      @endif

                    </tbody>
                  </table>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
@endsection

@section('script')
<script>
var page_type = 'content_view';
</script>
<script src="/js/bootstrap-3.3.7.min.js"></script>
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


<script>
  function showResolutions() {
    window.location.href = "/picture/crop?id=" + @json($picture['id']);
  }
  //cai nay xoa
    /*function showResolutions() {
        if(!$("#resolution_container").is(':empty')) {
            $('#collapseResolution').collapse('toggle');
        }
        else {
            $('#collapseResolution').collapse();
        }
    }*/

    $("input:radio[name='type']").change(function() {
        $("input:radio[name=type][value=crop]").next().toggleClass("label-checked");
        $("input:radio[name=type][value=stretch]").next().toggleClass("label-checked");
        $("input:hidden[name='type']").val($("input:radio[name='type']:checked").val());
    });

    function croppingLink(id, width, height) {
        window.location.href = "crop.php?id=" + id + "&w=" + width + "&h=" + height + "&type=" + $('input[name="type"]:checked').val() + "";
    }
</script>

<script src="{{ asset('/js/jquery-ui.min.js') }}"></script>
<script>
            //Autocomplete for tags
            $('#new_tag').autocomplete({
                minLength: 2,
                delay : 250,
                appendTo: '#tag_suggestion_container',
                source: function (request, response) {
                    $.ajaxSetup({
                        headers: {
                                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                            }
                    });
                    $.ajax({
                        url: "/picture/get-tag-suggestion",
                        type: 'POST',
                        data: {
                            text: request.term
                        },
                        dataType: "json",
                        success: function (data) {
                            response(data);
                        }
                    });
                }
            }).on('blur', function() {
                $(this).autocomplete("close");
            }).on('focus', function() {
                $(this).animate({width: Math.min(500, ($('#before_wallpaper_container').width() - $('#add_tag_btn').outerWidth() - 12)) + 'px'},'1000','linear');
            });

            $("#add_tag_btn").on('click', function() {
                var tags = $("#new_tag").val();
                if (tags != "") {
                  $('body').addClass('waiting');
                  $.ajaxSetup({
                      headers: {
                              'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                          }
                  });
                  var formData = {
                    picture_id: @json($picture['id']),
                    tags: tags
                  };
                  $.ajax({
                      url : '/picture/add_tags',
                      data : formData,
                      type : 'POST',
                      success : function (data){
                        if(data.hasOwnProperty('error') && data.error == false) {
                          $('body').removeClass('waiting');
                          location.reload();
                        }else if(data.hasOwnProperty('error') && data.error == true) {
                          var reason = "The operation you attempted is not permitted!";
                          if(data.hasOwnProperty('message') ) {
                              reason = data.message;
                          }
                          handleApiError(reason);
                        }
                        $('body').removeClass('waiting');
                      }
                  })
                }
            });

            $('#new_tags').keypress(function (e) {
                if(e.which == 13) {
                    $('#add_tag_btn').click();
                }
            });


            function editTags() {
              $.ajaxSetup({
                  headers: {
                          'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                      }
              });
              var formData = {
                id: @json($picture['id'])
              };
              $.ajax({
                  url : '/picture/edit_tags',
                  data : formData,
                  type : 'POST',
                  success : function (data){
                      if(data.error == true){
                          window.alert('Something wrong!');
                      }
                      else{
                        $("#list_tags").html('<div class="tags-title"><a id="complete_tag" title="Edit Complete" onclick="completeTags(); return false;" ><i class="el el-ok"></i></a>');
                        $("#list_tags").append(data);
                        $("#list_tags").append('</div>');
                      }
                  }
              })
            }

            function completeTags() {
              $.ajaxSetup({
                  headers: {
                          'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                      }
              });
              var formData = {
                id: @json($picture['id'])
              };
              $.ajax({
                  url : '/picture/complete_tags',
                  data : formData,
                  type : 'POST',
                  success : function (data){
                      if(data.error == true){
                          window.alert('Something wrong!');
                      }
                      else{
                        $("#list_tags").html('<div class="tags-title"><i class="el el-tags"></i><span>Tags: </span><a id="edit_tags" title="Edit Tags" onclick="editTags(); return false;"><i class="el el-wrench"></i></a>');
                        $("#list_tags").append(data);
                        $("#list_tags").append('</div>');
                      }
                  }
              })
            }


            $("#list_tags").on('click', '.remove-tag', function() {
                $('body').addClass('waiting');
                $.ajaxSetup({
                    headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        }
                });
                var tag_id = $(this).attr('tag_id');
                var formData = {
                  picture_id: @json($picture['id']),
                  tag_id: tag_id
                };
                $.ajax({
                    url : '/picture/remove_tags',
                    data : formData,
                    type : 'POST',
                    success : function (data){
                      if(data.hasOwnProperty('error') && data.error == false) {
                        $('#tag_' + tag_id).html("<b>This tag has been removal!</b>");
                      }else if(data.hasOwnProperty('error') && data.error == true) {
                        var reason = "The operation you attempted is not permitted!";
                        if(data.hasOwnProperty('message') ) {
                            reason = data.message;
                        }
                        handleApiError(reason);
                      }
                      $('body').removeClass('waiting');
                    }
                })
            });

            function handleApiError(error_message) {
              if(!$('.notice-error').length) {
                  $('#page_container').prepend("<div class='notice notice-error'>" +
                      "<span class='close-notice pull-right' title=\"Close This Notification!\">" +
                          "<i class=\"el el-remove\"></i>" +
                      "</span>" +
                      "<b></b>" +
                      "</div>")
              }

              $('.notice-error b').html(error_message);
              $('html,body').animate({scrollTop: $('.notice-error').offset().top - 15});
          }
        </script>
        <script>
          $('body').on('click', '.download-button',  function() {
            var element = $(this);
            var id = element.attr("data-id");
            window.location = "/picture/get-download-link/" + id;
          });
      </script>
      <script>
        $(function(){
          $("#deletepic").click(function(e){
            if (confirm('Confirm delete this picture')) {
              e.preventDefault();
              $.ajaxSetup({
                  headers: {
                          'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                      }
              });
              var formData = {
                id: @json($picture['id'])
              };
              $.ajax({
                  url : '/picture/delete',
                  data : formData,
                  type : 'POST',
                  success : function (data){
                      if(data.error == true){
                          window.alert(data.message);
                      }else{
                          window.alert("Delete Complete");
                          window.location.href = "/";
                      }
                  },
                  //timeout: 10000
              });
            }
          })
      })
      </script>
@endsection
