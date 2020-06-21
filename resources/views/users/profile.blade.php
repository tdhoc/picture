@extends('layouts.app')

@section('head')
<link href="{{ asset('/css/profile.css') }}" rel="stylesheet">
<title> Profile </title>
@endsection

@section('content')
    <div class="profile-banner-user" style="background-image:url({{ $users->background }}); background-size:cover;">
        <img class="avatar-image" src="{{ $users->avatar }}" alt="{{ $users->username }}">

        <div class="profile-banner-user-info">
            <span class="username">{{ $users->username }}</span>
        </div>
        <div class="btn-group">
            <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown">
                <span class="caret"></span>
            </button>
            <ul class="dropdown-menu">
                <li>
                    <a href="{{ url('users/profile',$users->username) }}">
                        <i class="glyphicon glyphicon-user"></i> Profile
                    </a>
                </li>
                <li>
                    <a href="">
                        <i class="glyphicon glyphicon-picture"></i> {{ $count }} Pictures
                    </a>
                </li>
                <li>
                    <a>
                        <i class="glyphicon glyphicon-eye-open"></i> {{ $view }} View
                    </a>
                </li>
                <li>
                    <a>
                        <i class="glyphicon glyphicon-download"></i> {{ $download }} Downloaded
                    </a>
                </li>
            </ul>
        </div>
    </div>

    <div class="row">
        <div class="col-md-12">
            <div class="panel panel-default">
                <div class="panel-body panel-quick-links">
                    <div title="Based Upon Where You Are Most Active!" class="btn btn-success btn-custom disabled"><b>  Most Activity: </b></div>
                    <a class="btn btn-primary btn-custom">{{ $count }} Picture Submissions</a>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-12">
            <div class="panel panel-default content-preview-container">
                <div class="panel-heading panel-wallpaper">
                    <h2 class="panel-title">
                        <div>{{ $users->username }}&#039;s All Picture Submissions</div>
                    </h2>
                    <div class="clearfix"></div>
                </div>
                <div class="panel-body center">
                @foreach ($recentPictures as $picture)
                    <a class="thumb-link" title="Check out this Picture" href="{{ url('picture/show',$picture->id) }}">
                        <img src="{{ $picture->thumb }}" alt="{{ $picture->description }}" />
                    </a>
                @endforeach
                </div>
            </div>
        </div>
    </div>

    @if ($view > 0)
    <div class="row">
        <div class="col-md-12">
            <div class="panel panel-default content-preview-container">
                <div class="panel-heading panel-wallpaper">
                    <h2 class="panel-title">
                        <div>{{ $users->username }}&#039;s Most View Pictures</div>
                    </h2>
                    <div class="clearfix"></div>
                </div>
                <div class="panel-body center">
                @foreach ($viewPictures as $picture)
                    @if ($picture->view >0)
                    <a class="thumb-link" title="{{ $picture->view }} View" href="{{ url('picture/show',$picture->id) }}">
                        <img src="{{ $picture->thumb }}" alt="{{ $picture->description }}" />
                    </a>
                    @endif
                @endforeach
                </div>
            </div>
        </div>
    </div>
    @endif

    @if ($download > 0)
    <div class="row">
        <div class="col-md-12">
            <div class="panel panel-default content-preview-container">
                <div class="panel-heading panel-wallpaper">
                    <h2 class="panel-title">
                        <div>{{ $users->username }}&#039;s Most Downloaded Pictures</div>
                    </h2>
                    <div class="clearfix"></div>
                </div>
                <div class="panel-body center">
                @foreach ($downloadPictures as $picture)
                    @if ($picture->download >0)
                    <a class="thumb-link" title="{{ $picture->download }} Downloaded" href="{{ url('picture/show',$picture->id) }}">
                        <img src="{{ $picture->thumb }}" alt="{{ $picture->description }}" />
                    </a>
                    @endif
                @endforeach
                </div>
            </div>
        </div>
    </div>
    @endif

@endsection
@section('script')
<script src="/js/bootstrap-3.4.1.min.js"></script>
@endsection