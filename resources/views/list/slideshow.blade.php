@extends('layouts.app')

@section('head')
<title> </title>
<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/css/bootstrap.min.css">
<link href="/css/bootstrap-3.3.7-custom.min.css" rel="stylesheet" media="screen">
<link href="/css/elusive-icons.min.css" rel="stylesheet">
<link href="/css/list_slideshow.css" rel="stylesheet">
@if($sort['sort'] == 'relevance')
<link type="text/css" rel='stylesheet' href="/css/spectrum.css" />
@endif
@endsection

@section('content')
<div id="show_websites"></div>
<div id="page_container">
  <div class="custom-breadcrumb">
    <a class="breadcrumb-element breadcrumb-with-icon breadcrumb-blue" title="Picture" href="{{asset("/")}}">
      <span class="glyphicon glyphicon-home"></span>
    </a>
    <a class="breadcrumb-element breadcrumb-with-icon" title="Picture" href="{{asset("/")}}">
      <span>Picture</span>
    </a>
    {!!$null!!}

  </div>
  <h1 class='center title'>
    {{$pagetitle}}
  </h1>

        @if($sort['sort'] == 'relevance')
        <div class='center'>
    <input type='text' id="flat" name="hex_color" value="{{$id}}">
                    <br/>
        <span id="btn_submit_color" class="btn btn-primary btn-custom btn-lg">
            <i class="el el-play"></i>
              Use this color!
    </span>
</div>
<br/>
        @endif
  <div class="custom-navigation">
    <a data-toggle="collapse" href="#sorting_options" class="btn btn-primary btn-multi-line">
      <i class="el el-filter"></i>&nbsp;Sorting Options
      <small>
        (currently: {{$sort['current']}})
      </small>
    </a>
    <a class='btn btn-default btn-custom' href="sub_categories.php?id=3">
      <i class="el el-th-list"></i> View All Subcategories
    </a>
  </div>
  

  <div id="sorting_options" class="collapse center">
    <form class="form-inline" method="post">
      <div class='sorting'>
        <div class='sorting-container'>
          <div class='sorting-container-small'>
            <div class='sorting-btn sorting-selector sorting-nw'>
              Infinite Scroll
              <input type='radio' class='sorting-radio' value="infinite" name="view" />
            </div>
          </div>
          <div class='sorting-container-small'>
            <div class='sorting-btn sorting-selector sorting-ne'>
              Pagination
              <input type='radio' class='sorting-radio' value="paged" name="view" />
            </div>
          </div>
          <div class='sorting-container-small'>
            <div class='sorting-btn sorting-selector sorting-se'>
              Slideshow
              <input type='radio' class='sorting-radio' value="slideshow" name="view" checked />
            </div>
          </div>
        </div>
        <div class='sorting-container'>
          <div class='sorting-container-big sorting-select dropup'>
            <select name='min_resolution' data-class='sorting-nw sorting-ne' data-header="Select a Resolution" {{ $sort['sort'] == 'relevance' ? "disabled" : "" }}>
              <option value='0x0'>All Resolutions</option>
              <option value='1366x768'>1366x768</option>
              <option value='1600x900'>1600x900</option>
              <option value='1920x1080' data-subtext="Full HD">1920x1080</option>
              <option value='1920x1200'>1920x1200</option>
              <option value='2560x1440'>2560x1440</option>
              <option value='2560x1600'>2560x1600</option>
              <option value='3840x2160' data-subtext="UltraHD 4K">3840x2160</option>
              <option value='5120x2880' data-subtext="5K Retina">5120x2880</option>
              <option value='7680x4320' data-subtext="UltraHD 8K">7680x4320</option>
            </select>
          </div>
          <div class='sorting-container-small'>
            <div class='sorting-btn {{ $sort['sort'] == 'relevance' ? "disabled-view" : "sorting-selector" }} sorting-sw'>
              At Least
              <input type='radio' class='sorting-radio' value=">=" name="resolution_equals"
                {{ $sort['resolution_equals'] == '>=' ? "checked" : "" }} />
            </div>
          </div>
          <div class='sorting-container-small'>
            <div class='sorting-btn {{ $sort['sort'] == 'relevance' ? "disabled-view" : "sorting-selector" }} sorting-se'>
              Exactly
              <input type='radio' class='sorting-radio' value="=" name="resolution_equals"
                {{ $sort['resolution_equals'] == '=' ? "checked" : "" }} />
            </div>
          </div>
        </div>

        <div class='sorting-container'>
          <div class='sorting-container-big sorting-select dropup'>
            <select name='sort' data-class='sorting-nw sorting-ne' data-header="Sorted By" {{ $sort['sort'] == 'relevance' ? "disabled" : "" }}>
              <option value='newest'>Newest</option>
              <option value='view'>Most Viewed</option>
              <option value='download'>Most Downloaded</option>
            </select>
          </div>

          <div class='sorting-container-small dropup sorting-select'>
            <select name='elementsperpage' data-class="sorting-sw" data-header="Picture per Page">
              <option value="3">3</option>
              <option value="15">15</option>
              <option value="30">30</option>
              <option value="45">45</option>
              <option value="60">60</option>
              <option value="75">75</option>
              <option value="90">90</option>
            </select>
          </div>
          <div class='sorting-container-small'>
            <span class="el el-refresh sorting-icon"></span>
            <button class='sorting-btn sorting-refresh sorting-se' id="applysort">Apply</button>
          </div>
        </div>
      </div>
    </form>
  </div>

  <div class="center">
    <div class="btn-group shortcuts">
      <button type="button" class="btn btn-info dropdown-toggle" data-toggle="dropdown" aria-expanded="false">
        <i class="el el-info-circle"></i> Keyboard Shortcuts <span class="caret"></span>
      </button>
      <ul class="dropdown-menu" role="menu">
        <li><span class="shortcut"><i class="el el-chevron-right"></i></span> Next Slide</li>
        <li><span class="shortcut"><i class="el el-chevron-left"></i></span> Previous Slide</li>
        <li><span class='shortcut'>I</span> More Info</li>
        <li><span class='shortcut'>F</span> SlideShow Auto: Forward</li>
        <li><span class='shortcut'>B</span> SlideShow Auto: Backward</li>
        <li><span class='shortcut'>P</span> SlideShow Auto: Pause</li>
      </ul>
    </div>
  </div>

  <a id="topslide"></a>
  <ul class="pgwSlideshow">
    @foreach ($picturelist as $picture)
    <li>
      <a href="{{asset('picture/show/'.$picture['id'])}}">
        <img data-id="{{$picture['id']}}" data-width="{{$picture['resolutionwh']['width']}}"
          data-height="{{$picture['resolutionwh']['height']}}"
          alt="HD Picture | Image ID:{{$picture['id']}}"
          data-description="{{'<a href="/by_category/'.$picture['subcategory']['category_id'] . '">' . $picture['subcategory']['category'] . '</a> - <a href="/by_subcategory/' .$picture["subcategory"]["id"] . '">'. $picture["subcategory"]["name"] . '</a>'}}"
          src="{{asset($picture['thumb'])}}" data-large-src="{{asset($picture['link'])}}">
      </a>
    </li>
    @endforeach

  </ul>



  <div class="center">
    {!!$pagedata!!}
  </div>
  <br />
  <div class="container custom-container">
    <div class="row">
      <div class="col-xs-12 col-sm-6 col-md-4 col-custom">
        <div class="panel panel-primary shadow">
          {!!$footer['column1']!!}
        </div>
      </div>

      <div class="col-xs-12 col-sm-6 col-md-4 col-custom">
        <div class="panel panel-primary shadow">
          {!!$footer['column2']!!}
        </div>
      </div>

      <div class="col-xs-12 col-sm-12 col-md-4 col-custom">
        <div class="panel panel-primary shadow">
          {!!$footer['column3']!!}
        </div>
      </div>
    </div>
  </div>
</div>

<div class="modal fade" id="wallpaperModal" tabindex="-1" role="dialog" aria-labelledby="wallpaperModal"
  aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
    </div>
  </div>
</div>
@endsection
@section('script')
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
    //Apple iPhone 6+, 6s+, 7+, 8+
    else if(width == 1242 && height == 2208 && ratio == 3)
    {
        width = 1080;
        height = 1920;
    }

    $('select[name=min_resolution] option').first().after('<option value="'+width+'x'+height+'" data-subtext="Your Resolution">'+width+'x'+height+'</option>');
    $sortdata = @json($sort);
    if($("select[name=min_resolution] option[value='" + $sortdata['resolution'] + "']").length == 0 ){
        $('select[name=min_resolution]').append('<option value="' + $sortdata['resolution'] + '" selected>' + $sortdata['resolution'] + '</option>');
    }else{
        $('select[name=min_resolution]').val($sortdata['resolution']);
    }

    if( $("select[name=sort] option[value='" + $sortdata['sort'] + "']").length == 0 ){
        $('select[name=sort]').val("newest");
    }else{
        $('select[name=sort]').val($sortdata['sort']);
    }

    if( $("select[name=elementsperpage] option[value='" + $sortdata['elementsperpage'] + "']").length == 0 ){
        $('select[name=elementsperpage]').val("30");
    }else{
        $('select[name=elementsperpage]').val($sortdata['elementsperpage']);
    }


    //Transform each sorting select into an dropdown
    $('.sorting-select select').each(function() {

        var listElement = "";
        var subtext;
        var option;
        var select = $(this);

        select.after('<div class="sorting-select-dropdown"></div>');
        var dropdown = select.next();
        dropdown.addClass(select.attr('data-class'));

        if(select.attr('disabled') !== undefined)
            dropdown.append('<button type="button" class="btn dropdown-toggle disabled"><span class="sorting-btn-select">'+$("option:selected", this).text()+'</span>&nbsp;<i class="el el-chevron-down"></i></button>');
        else {
            dropdown.append('<button aria-expanded="false" type="button" class="btn dropdown-toggle" data-toggle="dropdown"><span class="sorting-btn-select">'+$("option:selected", this).text()+'</span>&nbsp;<i class="el el-chevron-down"></i></button>');

            select.children().each(function() {
                option = $(this);

                subtext = "";
                if(option.attr('data-subtext') !== undefined)
                    subtext = '<span class="subtext">'+option.attr('data-subtext')+'</span>';

                if(option.is(':selected'))
                    listElement += '<li data-value="'+option.val()+'" class="select-selected"><a><span class="sorting-select-text"><span class="dropdown-content">'+option.text()+'</span>'+subtext+'</span><i class="el el-ok sorting-tick"></i></a></li>';
                else
                    listElement += '<li data-value="'+option.val()+'"><a><span class="sorting-select-text"><span class="dropdown-content">'+option.text()+'</span>'+subtext+'</span><i class="el el-ok sorting-tick"></i></a></li>';
            });
            dropdown.append('<div class="dropdown-menu open"><div class="popover-title">'+select.attr('data-header')+'</div><ul class="dropdown-menu inner" role="menu">'+listElement+'</ul></div>');
        }
    });

    //Manage the click on a 'select' dropdown, update css and the 'html select'
    $('.sorting-select-dropdown').on('click', 'li', function(event) {
        if( !$(this).hasClass('select-selected') ) {
            $(this).closest('.sorting-select-dropdown').find('.sorting-btn-select').html($(this).find('.dropdown-content').html());
            $(this).closest('.sorting-select').find('select').val($(this).attr('data-value'));
            $(this).parent().find('.select-selected').removeClass('select-selected');
            $(this).addClass('select-selected');
        }
    });

    // Add the right icon according to checked or unchecked
    $('.sorting-selector').each(function () {
        var element = $(this);

        if( element.find('input').is(':checked') )
            element.append("<i class='el el-check sorting-icon'></i>");
        else
            element.append("<i class='el el-check-empty sorting-icon'></i>");
    });

    // Another "radio" element is selected
    $('.sorting-selector').click(function () {
        var element = $(this);
        var input = element.find('input');
        if( !input.is(':checked') ) {
            $('input[name='+input.attr('name')+']:checked').next().toggleClass('el-check el-check-empty')
            input.prop("checked", true);
            element.find('.sorting-icon').toggleClass('el-check-empty el-check');
        }
    });
</script>
<script>
  $(function(){
		$('#applysort').click(function(e){
            e.preventDefault();
            $.ajaxSetup({
                headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    }
            });
            var formData = {
                view: $('input[name=view]:checked').val(),
                min_resolution:  $('select[name=min_resolution]').val(),
                resolution_equals: $('input[name=resolution_equals]:checked').val(),
                sort:  $('select[name=sort]').val(),
                elementsperpage: $('select[name=elementsperpage]').val()
            };
            $.ajax({
                url : '/sort',
                data : formData,
                type : 'POST',
                success : function (data){
                    if(data.error == true){
                        window.alert('Something wrong!');
                    }
                    else{
                        location.reload();
                    }
                }
            })
        })
    })
</script>
    <script>
        //Manage the quick jump button
        $('.quick-jump-btn').click(function() {
            var page_value = $(this).prev().val();
            if(@json($by) == 'search'){
               if( $.isNumeric( page_value ) && page_value > 0 ) {
                  if(page_value == 1) {
                      window.location.href =  "/search?keyword=" + @json($id)+ "&page=1";
                  }
                  else {
                      window.location.href = "/search?keyword=" + @json($id)+ "&page=" + (@json($next) - 2);
                  }
              }
              else {
                  $(this).prev().css("border-color", "red");
              }
            }else{
              if( $.isNumeric( page_value ) && page_value > 0 ) {
                  if(page_value == 1) {
                      window.location.href = "/" + @json($by) + "/" +@json($id);
                  }
                  else {
                      window.location.href = "/" + @json($by) + "/" +@json($id) + "/" + (@json($next) - 2);
                  }
              }
              else {
                  $(this).prev().css("border-color", "red");
              }
            }
            
        });
    </script>
<script src="{{ asset('/js/pgwslideshow_v9.min.js') }}"></script>
<script>
        var pgwSlideshow;
        var visiblePopover = false;

        $(document).ready(function() {
          if(@json($by) == 'search'){
            if(@json($next) > 2){
                $prev = "/search?keyword=" +  @json($id) + "&page=" + (@json($next) - 2);
            }else{
                $prev = "none"
            }
            pgwSlideshow =  $('.pgwSlideshow').pgwSlideshow({
                nextPage:"/search?keyword=" + @json($id) + "&page=" + @json($next),
                prevPage:$prev,
                titleAutoForward:"SlideShow Auto: Forward",
                titleAutoBackward:"SlideShow Auto: Backward",
                titlePause:"SlideShow Auto: Pause"
            });

          }else{
            $id = @json($id);
            if(@json($next) > 2){
                $prev = "/" + @json($by) + "/" + $id + "/" + (@json($next) - 2);
            }else{
                $prev = "none"
            }
            pgwSlideshow =  $('.pgwSlideshow').pgwSlideshow({
                nextPage:"/" + @json($by) + "/" + $id + "/" + @json($next),
                prevPage:$prev,
                titleAutoForward:"SlideShow Auto: Forward",
                titleAutoBackward:"SlideShow Auto: Backward",
                titlePause:"SlideShow Auto: Pause"
            });
          }
        });

        function showInfos() {
            var element = $(".moreInfoSlide");

            //Content already loaded
            if( typeof element.data('bs.popover') !== 'undefined' ) {
                if(visiblePopover) {
                    element.popover('hide');
                    visiblePopover = false;
                }
                else {
                    pgwSlideshow.pauseAutoSlide();
                    element.popover('show');
                    visiblePopover = true;
                }
            }
            else {
                pgwSlideshow.pauseAutoSlide();

                element.popover({placement : 'top', title: "<i class='floatright el el-remove'></i>Submission Info", html: true, content: '<div class="center">Loading <img src="/img/ajax-loader-er.gif" alt=""></div>', trigger: 'manual'});
                element.popover('show');
                $.ajaxSetup({
                headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    }
            });
                $.post("/get_submission_infos", {picture_id: pgwSlideshow.getWallpaperID()},
                    function(data) {
                        element.attr('data-content', data);
                    }).done(function() {
                    var popover = element.data('bs.popover');
                    popover.setContent();
                    element.popover('hide').popover('show');
                    visiblePopover = true;

                    $('.popover').on('click', '.el-remove', function() {
                        $(".moreInfoSlide").popover('hide');
                        visiblePopover = false;
                    });
                });
            }
        };
    </script>
    @if($sort['sort'] == 'relevance')
    <script type="text/javascript" src="{{asset('/js/spectrum.js')}}"></script>
    <script type="text/javascript">
        $("#flat").spectrum({
            showInput: true,
            showInitial: true,
            allowInput: true,
            flat: true,
            clickoutFiresChange: true,
            showAlpha: false,
            showPalette: true,
            localStorageKey: "spectrum.by_color",
            showButtons: false,
            preferredFormat: "hex",
            palette: [
                ["rgb(0, 0, 0)", "rgb(28, 28, 28)", "rgb(56, 56, 56)", "rgb(85, 85, 85)", "rgb(113, 113, 113)",
                    "rgb(141, 141, 141)", "rgb(170, 170, 170)", "rgb(198, 198, 198)", "rgb(226, 226, 226)","rgb(255, 255, 255)"],
                ["rgb(152, 0, 0)", "rgb(255, 0, 0)", "rgb(255, 153, 0)", "rgb(255, 255, 0)", "rgb(0, 255, 0)",
                    "rgb(0, 255, 255)", "rgb(74, 134, 232)", "rgb(0, 0, 255)", "rgb(153, 0, 255)", "rgb(255, 0, 255)"],
                ["rgb(230, 184, 175)", "rgb(244, 204, 204)", "rgb(252, 229, 205)", "rgb(255, 242, 204)", "rgb(217, 234, 211)",
                    "rgb(208, 224, 227)", "rgb(201, 218, 248)", "rgb(207, 226, 243)", "rgb(217, 210, 233)", "rgb(234, 209, 220)"],
                ["rgb(221, 126, 107)", "rgb(234, 153, 153)", "rgb(249, 203, 156)", "rgb(255, 229, 153)", "rgb(182, 215, 168)",
                    "rgb(162, 196, 201)", "rgb(164, 194, 244)", "rgb(159, 197, 232)", "rgb(180, 167, 214)", "rgb(213, 166, 189)"],
                ["rgb(204, 65, 37)", "rgb(224, 102, 102)", "rgb(246, 178, 107)", "rgb(255, 217, 102)", "rgb(147, 196, 125)",
                    "rgb(118, 165, 175)", "rgb(109, 158, 235)", "rgb(111, 168, 220)", "rgb(142, 124, 195)", "rgb(194, 123, 160)"],
                ["rgb(166, 28, 0)", "rgb(204, 0, 0)", "rgb(230, 145, 56)", "rgb(241, 194, 50)", "rgb(106, 168, 79)",
                    "rgb(69, 129, 142)", "rgb(60, 120, 216)", "rgb(61, 133, 198)", "rgb(103, 78, 167)", "rgb(166, 77, 121)"],
                ["rgb(91, 15, 0)", "rgb(102, 0, 0)", "rgb(120, 63, 4)", "rgb(127, 96, 0)", "rgb(39, 78, 19)",
                    "rgb(12, 52, 61)", "rgb(28, 69, 135)", "rgb(7, 55, 99)", "rgb(32, 18, 77)", "rgb(76, 17, 48)"]
            ],
            maxSelectionSize: 10
        });

        //Remove the '#' from the color so there's no redirection
        $('#btn_submit_color').click(function() {
            var color = "" + $("#flat").spectrum("get");
            $('input[name="hex_color"]').val(color.replace('#', ''));
            window.location.href = '/by_color/' + $('input[name=hex_color]').val();
        });

    </script>
    @endif
    <script>
    $('body').on('click', '.download-button',  function() {
      var element = $(this);
      var id = element.attr("data-id");
      window.location = "/picture/get-download-link/" + id;
    });
</script>

@endsection