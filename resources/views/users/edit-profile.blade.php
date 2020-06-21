@extends('layouts.app')

@section('head')
<title> Edit Profile </title>
<link href="{{ asset('/css/edit-profile.css') }}" rel="stylesheet">
@endsection

@section('content')

<form id="mainForm" method="post" enctype="multipart/form-data">
    <div id="page_container">
        <h1 class="title">{{ $user->username }}'s Profile</h1>
        <div class="profile-banner">
            <div class="banner-container">
                <div class="profile-banner-user" style="background-image:url({{ $user->background }}); background-size:cover; margin-bottom: 20px;">
                    <label for="avatar" class="custom-avatar">
                        <img class="avatar-image" src="{{ $user->avatar }}" alt="{{ $user->username }}">
                        <input id="avatar" type="file" name="avatar">
                        <input type="hidden" name="_token" id="token" value="{{ csrf_token() }}">
                    </label>
                    <label for="background" class="custom-background">
                        <img class="background-image" src="data:image/gif;base64,R0lGODlhAQABAPcAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAACH5BAEAAP8ALAAAAAABAAEAAAgEAP8FBAA7">
                        <input id="background" type="file" name="background">
                    </label>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-8 col-md-offset-2">
            <div class="panel panel-primary panel-body">
                <div class="alert alert-danger" style="display: none;">                     
                    <li class="alert-message" id="password-alert" style="display: none; color: red;"></li>
                    <li class="alert-message" id="new-password-alert" style="display: none; color: red;"></li>
                    <li class="alert-message" id="new-password-confirm-alert" style="display: none; color: red;"></li>
                    <li class="alert-message" id="email-alert" style="display: none; color: red;"></li>
                    <li class="alert-message" id="edit-alert" style="display: none; color: red;"></li>
                </div>
                <!-- Edit Form -->
                <div id="edit_form">

                    <div class="form-group">
                        <label for="password">Password:
                            <li style="display: inline-block; color: red; font-size: 13px">(Required)</li>
                        </label>
                        <input class="form-control" minlength="8" name="password" type="password" id="password" value="">
                    </div>

                    <div class="form-group">
                        <label for="new_password">New Password:
                            <li style="display: inline-block; color: blue; font-size: 13px">(Optional)</li>
                        </label>
                        <input class="form-control" minlength="8" name="new_password" type="password" id="new_password" value="">
                    </div>

                    <div class="form-group">
                        <label for="new_password_confirmation">Confirm New Password:</label>
                        <input class="form-control" minlength="8" name="new_password_confirmation" type="password" id="new_password_confirmation" value="">
                    </div>

                    <div class="form-group">
                        <label for="email">Email:</label>
                        <input class="form-control" maxlength="100" name="email" type="email" id="email" value="{{ $user->email }}">
                        </div>

                    <div class="form-group">
                        <button type="submit" class="btn btn-lg btn-primary" id="save">
                            <span class="glyphicon glyphicon-ok"></span> Save
                        </button>
                        <button type="button" class="btn btn-lg btn-primary" id="cancel">
                            <span class="glyphicon glyphicon-remove"></span> Cancel
                        </button>
                    </div>
                </div>
                <!-- End form -->
            </div>
        </div>
    </div>
</form>
@endsection

@section('script')
<script src="/js/bootstrap-3.4.1.min.js"></script>
<script>
    $(function(){
        $('#mainForm').submit(function(e){
            e.preventDefault();
            $.ajaxSetup({
            headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });
            $.ajax({
                url : '/users/edit-profile',
                data : new FormData(this),
                type : 'POST',
                dataType: 'JSON',
                contentType: false,
                processData: false,
                success : function (data){
                    $('.alert.alert-danger').hide();
                    if(data.error == true) {
                        $('.alert.alert-danger').show();
                        $('.alert-message').hide();
                        if(data.message.password != undefined){
                            $("#password-alert").show().text(data.message.password[0]);
                        }
                        if(data.message.new_password != undefined){
                            $("#new-password-alert").show().text(data.message.new_password[0]);
                        }
                        if(data.message.new_password_confirmation != undefined){
                            $("#new-password-confirm-alert").show().text(data.message.new_password_confirmation[0]);
                        }
                        if(data.message.email != undefined){
                            $("#email-alert").show().text(data.message.email[0]);
                        }
                        if(data.message.edit != undefined){
                            $("#edit-alert").show().text(data.message.edit[0]);
                        }
                    } else {
                        window.alert('Edit complete!');
                        window.location.href = "/users/profile/{{ $user->username }}", true;
                    }
                }
            })
        })

        $('#cancel').click(function(e){
            window.location.href = "/";
        })
    })
</script>

@endsection