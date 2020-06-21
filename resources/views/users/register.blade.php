
@extends('layouts.app')

@section('head')
<title> Register On Picture </title>
<link href="{{ asset('/css/register.css') }}" rel="stylesheet">
@endsection

@section('content')

<div class="row">
    <div class="col-md-8 col-md-offset-2">
        <div class="panel panel-primary">
            <div class="panel-heading">
                <div class="panel-title" style="text-align: center;">Register On Picture</div>
            </div>
            <div class="panel-body">
                <div class="alert alert-danger" style="display: none;">
                    <li class="alert-message" id="username-alert" style="display: none; color: red;"></li>                        
                    <li class="alert-message" id="password-alert" style="display: none; color: red;"></li>
                    <li class="alert-message" id="password-confirm-alert" style="display: none; color: red;"></li>
                    <li class="alert-message" id="email-alert" style="display: none; color: red;"></li>
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

                    <div class="form-group">
                        <label for="password-confirm">Confirm Password:</label>
                        <input class="form-control" minlength="8" name="password-confirm" type="password" id="password-confirm" value="">
                    </div>

                    <div class="form-group">
                        <label for="email">Email:</label>
                        <input class="form-control" maxlength="100" name="email" type="email" id="email">
                        </div>

                    <div class="form-group">
                        <div class="col-xs-6">
                            <span class="btn btn-success btn-block" id="finalize_register">Register</span>
                        </div>
                        <div class="col-xs-6">
                            <a href="{{asset('/')}}" class="btn btn-danger btn-block">Cancel</a>
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
        $('#finalize_register').click(function(e){
            e.preventDefault();
            $.ajaxSetup({
                headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    }
            });
            var formData = {
                username : $('#username').val(),
                password : $('#password').val(),
                password_confirmation : $('#password-confirm').val(),
                email : $('#email').val(),
            };
            $.ajax({
                url : 'register',
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
                        if(data.message.password_confirmation != undefined){
                            $("#password-confirm-alert").show().text(data.message.password_confirmation[0]);
                        }
                        if(data.message.email != undefined){
                            $("#email-alert").show().text(data.message.email[0]);
                        }
                    }
                    else{
                        window.location.href = "/users/login";
                        window.alert('Register complete!');
                    }
                }
            })
        })
    })
</script>

@endsection