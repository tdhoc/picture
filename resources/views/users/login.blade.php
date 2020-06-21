@extends('layouts.app')

@section('head')
<title> Sign Into Picture </title>
<link href="{{ asset('/css/login.css') }}" rel="stylesheet">
@endsection

@section('content')
<div class="row">
    <div class="col-md-8 col-md-offset-2">
        <div class="panel panel-primary">
            <div class="panel-heading">
                <div class="panel-title" style="text-align: center;">Sign In to Picture</div>
            </div>
            <div class="panel-body">
                <div class="alert alert-danger" style="display: none;">
                    <li class="alert-message" id="username-alert" style="display: none; color: red;"></li>                        
                    <li class="alert-message" id="password-alert" style="display: none; color: red;"></li>
                    <li class="alert-message" id="signin-alert" style="display: none; color: red;"></li>
                </div>
                <!-- Register Form -->
                <div id="register_form">
                    <div class="form-group">
                        <label for="username">Username:</label>
                        <input class="form-control" minlength="6" maxlength="24" name="username" type="text" id="username" value="{{old('username')}}">
                    </div>

                    <div class="form-group">
                        <label for="password">Password:</label>
                        <input class="form-control" minlength="8" name="password" type="password" id="password" value="">
                    </div>

                    <div class="form-group" style="margin-bottom: 1.5em; width: 14.5em">
                        <input id="remember" class="styled" name="remember" type="checkbox">
                        <label class="styled" for="remember"><div><i class="glyphicon glyphicon-remove"></i></div><span>Remember Me</span></label>
                    </div>

                    <div class="row">
                        <div class="col-xs-6">
                            <span class="btn btn-success btn-block" id="signin">Sign In</span>
                        </div>
                        <div class="col-xs-6">
                            <a href="/users/register" class="btn btn-primary btn-block">Register</a>
                        </div>
                    </div>
                </div>
                <!-- End form -->
            </div>
        </div>
    </div>
</div>
@endsection

@section('script')
<script src="/js/bootstrap-3.4.1.min.js"></script>
<script>
    $(function(){
		$('#signin').click(function(e){
			e.preventDefault();
			$.ajaxSetup({
			    headers: {
			        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
			    }
			});
			var formData = {
			    	username : $('#username').val(),
					password : $('#password').val(),
                    remember : $('#remember').prop("checked")
			};
			$.ajax({
				url : 'login',
				data : formData,
				type : 'POST',
				success : function (data){
                    $('.alert.alert-danger').hide();
					if(data.error == true){
                        $('.alert.alert-danger').show();
                        $('.alert-message').hide();
                        if(data.message.username != undefined){
                            $("#username-alert").show().text(data.message.username[0]);
                        }
                        if(data.message.password != undefined){
                            $("#password-alert").show().text(data.message.password[0]);
                        }
                        if(data.message.signin != undefined){
                            $("#signin-alert").show().text(data.message.signin[0]);
                        }
					}
					else{
						window.location.href = "/";
					}
				}
			})
		})
	})
</script>
@endsection
