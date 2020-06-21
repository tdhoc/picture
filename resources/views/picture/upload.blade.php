@extends('layouts.app')

@section('head')
<title>Picture Upload</title>
<link rel="stylesheet" href="/css/blueimp-gallery.min.css">
<link rel="stylesheet" href="/css/jquery.fileupload.min.css">
<link rel="stylesheet" href="/css/jquery.fileupload-ui.min.css">
<style>
    body.main {
    margin: 0px;
    background: #accded url("/img/tile.jpg") repeat 0 0;
}
</style>
@endsection

@section('content')
<div class="alert alert-info">Max upload size is 100M, only images allowed</div>
<form id="fileupload" action="{{ route('pictures.store') }}" method="post" enctype="multipart/form-data">
    <!-- The fileupload-buttonbar contains buttons to add/delete files and start/cancel the upload -->
    <div class="row fileupload-buttonbar">
        <div class="col-lg-7">
            <!-- The fileinput-button span is used to style the file input field as button -->
            <span class="btn btn-success fileinput-button">
                <i class="glyphicon glyphicon-plus"></i>
                <span>Add files...</span>
                <input type="file" name="files[]" multiple>
            </span>
            <button type="submit" id="complete" class="btn btn-primary start">
                <i class="glyphicon glyphicon-upload"></i>
                <span>Start upload</span>
            </button>
            <button type="reset" id="quit" class="btn btn-warning cancel">
                <i class="glyphicon glyphicon-ban-circle"></i>
                <span>Cancel upload</span>
            </button>
            <button type="button" class="btn btn-danger delete">
                <i class="glyphicon glyphicon-trash"></i>
                <span>Delete Selected</span>
            </button>
            <input type="checkbox" class="toggle">
            <!-- The global file processing state -->
            <span class="fileupload-process"></span>
        </div>
        <!-- The global progress state -->
        <div class="col-lg-5 fileupload-progress fade">
            <!-- The global progress bar -->
            <div class="progress progress-striped active" role="progressbar" aria-valuemin="0" aria-valuemax="100">
                <div class="progress-bar progress-bar-success" style="width:0%;"></div>
            </div>
            <!-- The extended global progress state -->
            <div class="progress-extended">&nbsp;</div>
        </div>
    </div>
    <!-- The table listing the files available for upload/download -->
    <table role="presentation" class="table table-striped"><tbody class="files"></tbody></table>
</form>
<!-- The blueimp Gallery widget -->
<div id="blueimp-gallery" class="blueimp-gallery blueimp-gallery-controls" data-filter=":even">
    <div class="slides"></div>
    <h3 class="title"></h3>
    <a class="prev">‹</a>
    <a class="next">›</a>
    <a class="close">×</a>
    <a class="play-pause"></a>
    <ol class="indicator"></ol>
</div>
@endsection

@section('script')

<script src="/js/bootstrap-3.4.1.min.js"></script>
@include('_partials.x-template')
<script src="{{ asset('js/vendor/jquery.ui.widget.min.js') }}"></script>
<script src="{{ asset('js/tmpl.min.js') }}"></script>
<script src="{{ asset('js/load-image.all.min.js') }}"></script>
<script src="{{ asset('js/canvas-to-blob.min.js') }}"></script>
<script src="{{ asset('js/jquery.blueimp-gallery.min.js') }}"></script>
<script src="{{ asset('js/jquery.iframe-transport.min.js') }}"></script>
<script src="{{ asset('js/jquery.fileupload.min.js') }}"></script>
<script src="{{ asset('js/jquery.fileupload-process.min.js') }}"></script>
<script src="{{ asset('js/jquery.fileupload-image.min.js') }}"></script>
<script src="{{ asset('js/jquery.fileupload-validate.min.js') }}"></script>
<script src="{{ asset('js/jquery.fileupload-ui.min.js') }}"></script>
<script src="{{ asset('js/app.js') }}"></script>
<script>
$(function(){
    $('#complete').click(function(e){
        setTimeout(function() {
            window.location.href = "submit";
        }, 5000);
    })
})

$(function(){
    $('#quit').click(function(e){
        setTimeout(function() {
            window.location.href = "/";
        }, 5000);
    })
})
</script>
@endsection