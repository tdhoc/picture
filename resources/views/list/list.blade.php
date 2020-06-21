@extends('layouts.app')

@section('head')
<title> Picture </title>
<link href="/css/bootstrap-3.3.7-custom.min.css" rel="stylesheet" media="screen">
<link href="/css/elusive-icons.min.css" rel="stylesheet">
<link rel="stylesheet" href="/css/list.css">
@endsection

@section('content')
    <div id="page_container">
      <div class="custom-breadcrumb">
        <a class="breadcrumb-element breadcrumb-with-icon breadcrumb-blue" title="Picture" href="{{asset("/")}}">
                <span class="glyphicon glyphicon-home"></span> 
            </a>
            <a class="breadcrumb-element breadcrumb-with-icon" title="Pictures" href="{{asset("/")}}">
                <span>Picture</span>
            </a>
            <span class="breadcrumb-element">
                <span>{{$null}}</span>
            </span>
      </div>

      <h1 class="center title">
        {{$title}}
      </h1>

      <div class="below-custom-nav-tabs"></div>

      <div id="sub_categories_container">
        <div class="custom-nav-letters">
          {!!$custom_nav!!}
        </div>

        <div class="panel panel-default">
          <div class="panel-body">
            <h2 class="center title-small">
              {{$title2}}
            </h2>
            <div class="sub-categories-list">

              {!!$list!!}

            </div>

            <br />
            <div class="center">
              <div class="btn btn-default btn-lg btn-top">
                <i class="el el-arrow-up"></i>
                Back To Top
              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="clearfix"></div>
    </div>
@endsection

@section('script')
    <script src="/js/bootstrap-3.4.1.min.js"></script>
    <script>
        $(".btn-top").on("click", function() {
            window.scrollTo(0, 0);
        });
    </script>
@endsection
