<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta http-equiv="x-ua-compatible" content="ie=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <meta name="description" content="JavaScript image cropper.">
  <meta name="author" content="Chen Fengyuan">
  <title>Cropper.js</title>
  <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.13.0/css/all.css" crossorigin="anonymous">
  <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.0/css/bootstrap.min.css" crossorigin="anonymous">
  <link rel="stylesheet" href="/css/cropper.css">
  <link rel="stylesheet" href="/css/crop-main.css">
  
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
  <style>
      img {
  display: block;

  /* This rule is very important, please don't ignore this */
  max-width: 100%;
}
  </style>
</head>
<body style="margin: 0px; background: #accded url('/img/tile.jpg') repeat 0 0; height: 100%; width: 100%;">
  <!-- Content -->
  <div class="page_container" >
    <nav class="navbar navbar-expand-md navbar-light bg-light justify-content-center" style="padding: 7px">
            <a class="navbar-brand" title="Home Page" href="/"><i class="fa fa-image"></i>&nbsp;Pictures</a>
            <button type="button" class="navbar-toggler" data-toggle="collapse" data-target="#navbarCollapse">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarCollapse">
                <div class="navbar-nav">
                    <a class="nav-item nav-link" href="{{ asset('/picture/submit') }}" title="Upload Pictures"><i class="fa fa-arrow-circle-up"></i></i></i>&nbsp;Upload</a>
                    <form class="navbar-form navbar-left" method="GET" action="/search">
                        <div class="input-group">
                            <input class="form-control" placeholder="Search" type="text" name="keyword" />
                            <span class="input-group-btn"><button type="submit" class="btn btn-default" title="Search Pictures"><i class="fa fa-search"></i></button></span>
                        </div>
                    </form>
                </div>
                <div class="navbar-nav ml-auto">
                    @if (Auth::check())
                        <div class="dropdown navbar-form">
                            <button class="btn btn-default dropdown-toggle" type="button" data-toggle="dropdown">
                                @if (Auth::user()->type == "admin")
                                    <i class="fa fa-star"></i>&nbsp;Admin </i>
                                @else
                                    <i class="fa fa-user"></i>&nbsp;{{Auth::user()->username}}</i>
                                @endif
                            </button>
                            <ul class="dropdown-menu">
                                @if (Auth::user()->type == "admin")
                                    <li><a  class="nav-item nav-link" href="/admin/users-management"><i class="fa fa-users"></i>&nbsp;Users</a></li>
                                    <li><a class="nav-item nav-link" href="/admin/category"><i class="fa fa-list"></i>&nbsp;Categories</a></li>
                                    <li><a class="nav-item nav-link" href="/users/logout"><i class="fa fa-sign-out"></i>&nbsp;Logout</a></li>
                                @else
                                    <li><a class="nav-item nav-link" href="{{ url('users/profile', Auth::user()) }}"><i class="fa fa-home"></i>&nbsp;My Page</a></li>
                                    <li><a class="nav-item nav-link" href="/users/edit-profile"><i class="fa fa-cog"></i></i>&nbsp;Edit Profile</a></li>
                                    <li><a class="nav-item nav-link" href="/users/logout"><i class="fa fa-sign-out"></i>&nbsp;Logout</a></li>
                                @endif
                            </ul>
                        </div>
                    @else
                    <div class="navbar-nav">
                        <li class="nav-item"><a class="nav-link" href="/users/login"><i class="fa fa-sign-in"></i>&nbsp;Login</a></li>
                        <li class="nav-item"><a class="nav-link" href="/users/register"><i class="fa fa-edit"></i>&nbsp;Register</a></li>
                    </div>
                    @endif
                </div>
            </div>
        </nav>
    <div class="row">
      <div class="col-md-9">
        <div class="docs-demo">
          <div class="img-container">
              <input type="text" style="display: none;"  value="{{$name}}" id="pictureid">
          <img id="image" src="{{$picture['link']}}" alt="Picture">
          </div>
        </div>
      </div>
      <div class="col-md-3">
        <!-- <h3>Preview:</h3> -->
        <div class="docs-preview clearfix" style="padding-top: 10px">
          <div class="img-preview preview-lg"></div>
        </div>

        <!-- <h3>Data:</h3> -->
        <div class="docs-data">
          <div class="input-group input-group-sm">
            <span class="input-group-prepend">
              <label class="input-group-text" for="dataX">X</label>
            </span>
            <input type="text" class="form-control" id="dataX" placeholder="x">
            <span class="input-group-append">
              <span class="input-group-text">px</span>
            </span>
          </div>
          <div class="input-group input-group-sm">
            <span class="input-group-prepend">
              <label class="input-group-text" for="dataY">Y</label>
            </span>
            <input type="text" class="form-control" id="dataY" placeholder="y">
            <span class="input-group-append">
              <span class="input-group-text">px</span>
            </span>
          </div>
          <div class="input-group input-group-sm">
            <span class="input-group-prepend">
              <label class="input-group-text" for="dataWidth">Width</label>
            </span>
            <input type="text" class="form-control" id="dataWidth" placeholder="width">
            <span class="input-group-append">
              <span class="input-group-text">px</span>
            </span>
          </div>
          <div class="input-group input-group-sm">
            <span class="input-group-prepend">
              <label class="input-group-text" for="dataHeight">Height</label>
            </span>
            <input type="text" class="form-control" value="100" id="dataHeight" placeholder="height">
            <span class="input-group-append">
              <span class="input-group-text">px</span>
            </span>
          </div>
          <div class="input-group input-group-sm">
            <span class="input-group-prepend">
              <label class="input-group-text" for="dataRotate">Rotate</label>
            </span>
            <input type="text" class="form-control" id="dataRotate" placeholder="rotate">
            <span class="input-group-append">
              <span class="input-group-text">deg</span>
            </span>
          </div>
        </div>
      </div>
    </div>
    <div class="row" id="actions">
      <div class="col-md-9 docs-buttons">
        <!-- <h3>Toolbar:</h3> -->


      
        <div class="btn-group d-flex flex-nowrap docs-toggles" data-toggle="buttons">
          <label class="btn btn-primary active">
            <input type="radio" class="sr-only" id="aspectRatio1" name="aspectRatio" value="NaN">
            <span class="docs-tooltip" data-toggle="tooltip" title="Free Crop Box">
              Free
            </span>
          </label>
          <label class="btn btn-primary">
            <input type="radio" class="sr-only" id="aspectRatio2" name="aspectRatio" value="1.3333333333333333">
            <span class="docs-tooltip" data-toggle="tooltip" title="4:3 Crop Box">
              4:3
            </span>
          </label>
          <label class="btn btn-primary">
            <input type="radio" class="sr-only" id="aspectRatio3" name="aspectRatio" value="1">
            <span class="docs-tooltip" data-toggle="tooltip" title="1:1 Crop Box">
              1:1
            </span>
          </label>
          <label class="btn btn-primary">
            <input type="radio" class="sr-only" id="aspectRatio4" name="aspectRatio" value="0.6666666666666666">
            <span class="docs-tooltip" data-toggle="tooltip" title="2:3 Crop Box">
              2:3
            </span>
          </label>
          <label class="btn btn-primary">
            <input type="radio" class="sr-only" id="aspectRatio5" name="aspectRatio" value="1.7777777777777777">
            <span class="docs-tooltip" data-toggle="tooltip" title="16:9 Crop Box">
              16:9
            </span>
          </label>
        </div>


        <div class="btn-group">
          <button type="button" class="btn btn-primary" data-method="rotate" data-option="-45" title="Rotate Left">
            <span class="docs-tooltip" data-toggle="tooltip" title="Rotate Left">
              <i class="fa fa-undo"></i>
            </span>
          </button>
          <button type="button" class="btn btn-primary" data-method="rotate" data-option="45" title="Rotate Right">
            <span class="docs-tooltip" data-toggle="tooltip" title="Rotate Right">
              <i class="fa fa-repeat"></i>
            </span>
          </button>
        </div>


        <div class="btn-group btn-group-crop">
          <button type="button" class="btn btn-success" data-method="getCroppedCanvas" data-option="{ &quot;maxWidth&quot;: 4096, &quot;maxHeight&quot;: 4096 }">
            <span class="docs-tooltip" data-toggle="tooltip" title="Download">
              Download Image
            </span>
          </button>
        </div>

        <!-- Show the cropped image in modal -->
        <div class="modal fade docs-cropped" id="getCroppedCanvasModal" role="dialog" aria-hidden="true" aria-labelledby="getCroppedCanvasTitle" tabindex="-1">
          <div class="modal-dialog">
            <div class="modal-content">
              <div class="modal-header">
                <h5 class="modal-title" id="getCroppedCanvasTitle">Preview</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                  <span aria-hidden="true">&times;</span>
                </button>
              </div>
              <div class="modal-body"></div>
              <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                <a class="btn btn-primary" id="download" href="javascript:void(0);" download="{{$name}}">Download</a>
              </div>
            </div>
          </div>
        </div><!-- /.modal -->


      </div><!-- /.docs-buttons -->

    </div>
  </div>


  <!-- Scripts -->
  <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js" crossorigin="anonymous"></script>
  <script src="/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
  <script src="/js/cropper.js"></script>
  <script src="/js/crop-main.js"></script>

</body>
</html>
