<!DOCTYPE html>
<html>
<head>
	<meta charset="UTF-8">
	<meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/css/bootstrap.min.css">
    @yield('head')
</head>
<body class="main" style=" margin: 0px;
    background: #accded url('/img/tile.jpg') repeat 0 0;">
    <div class="page_container">
		<!-- navbar -->
		<nav class="navbar navbar-default justify-content-between" role="navigation" style="margin-bottom: 0px">
            <div class="navbar-header">
                <button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-ex1-collapse">
                    <span class="sr-only">Toggle navigation</span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                </button>
                <a class="navbar-brand" title="Home Page" href="/"><span class='glyphicon glyphicon glyphicon-picture'></span>&nbsp;Pictures</a>
            </div>
            <div class="collapse navbar-collapse navbar-ex1-collapse" >
                <ul class="nav navbar-nav">
                    <li>
                        <a href="{{ asset('/picture/submit') }}" title="Upload Pictures"><i class='glyphicon glyphicon glyphicon-upload'></i>&nbsp;Upload</a>
                    </li>
                </ul>

                <form class="navbar-form navbar-left" method="GET" action="/search">
                    <div class="input-group">
                        <input class="form-control" placeholder="Search" type="text" name="keyword" />
                        <span class="input-group-btn"><button type="submit" class="btn btn-default" title="Search Pictures"><span class='glyphicon glyphicon-search'></span></button></span>
                    </div>
                </form>


                <ul class="nav navbar-nav navbar-right">
                    @if (Auth::check())
                        <div class="dropdown navbar-form">
                            <button class="btn btn-default dropdown-toggle" type="button" data-toggle="dropdown">
                                @if (Auth::user()->type == "admin")
                                    <span class='glyphicon glyphicon-star'></span>&nbsp;Admin <span class="caret"></span>
                                @else
                                    <span class='glyphicon glyphicon-user'></span>&nbsp;{{Auth::user()->username}} <span class="caret"></span>
                                @endif
                            </button>
                            <ul class="dropdown-menu">
                                @if (Auth::user()->type == "admin")
                                    <li><a href="{{ url('users/profile', Auth::user()) }}"><span class="glyphicon glyphicon-home"></span>&nbsp;My Page</a></li>
                                    <li><a href="/users/edit-profile"><span class="glyphicon glyphicon-cog"></span>&nbsp;Edit Profile</a></li>
                                    <li><a href="/admin/users-management"><span class="glyphicon glyphicon-user"></span>&nbsp;Users</a></li>
                                    <li><a href="/admin/category"><span class="glyphicon glyphicon-list"></span>&nbsp;Categories</a></li>
                                    <li><a href="/users/logout"><span class="glyphicon glyphicon-log-out"></span>&nbsp;Logout</a></li>
                                @else
                                    <li><a href="{{ url('users/profile', Auth::user()) }}"><span class="glyphicon glyphicon-home"></span>&nbsp;My Page</a></li>
                                    <li><a href="/users/edit-profile"><span class="glyphicon glyphicon-cog"></span>&nbsp;Edit Profile</a></li>
                                    <li><a href="/users/logout"><span class="glyphicon glyphicon-log-out"></span>&nbsp;Logout</a></li>
                                @endif
                            </ul>
                        </div>
                    @else
                        <li>
                            <a rel="nofollow" href="/users/login"><span class="glyphicon glyphicon-log-in"></span>&nbsp;Login</a>
                        </li>
                        <li>
                            <a href="/users/register"><span class="glyphicon glyphicon-edit"></span>&nbsp;Register</a>
                        </li>
                    @endif
                    
                </ul>
            </div>
        </nav>
    </div>
    @yield('content')
    <div class="footer"><br><br><br></div>
    
	<script src="/js/jquery.min.js"></script>
    @yield('script')
</body>

</html>