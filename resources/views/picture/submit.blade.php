@extends('layouts.app')

@section('head')
<title>Submit Your Image</title>
<link href="{{ asset('/css/submit-page.css') }}" rel="stylesheet">
@endsection

@section('content')
<div class="center">
    <h1 class="title-small"><i class="el el-list-alt"></i> Add Info To Your Content</h1>
</div>

<div class="container">
    <div class="row">
        <div class="col-md-4">
            <a href="{{$picture_temp->url}}" target="_blank" title="Click to view in full size! ({{$resolution[0].'x'.$resolution[1]}})">
                <img class="img-responsive thumb-file" src="{{$picture_temp->url}}">
            </a>
            <div id="queue_file_status">
                <br>
                <div class="panel panel-primary panel-body">
                    <strong>Resolution: {{$resolution[0].'x'.$resolution[1]}}</strong>
                    <hr class="hr-custom-black">
                    <strong>Size: {{$size}}</strong>
                    <hr class="hr-custom-black">
                        <b>Add tags to increase visibility!</b>
                    <hr class="hr-custom-black">
                    <input id="picture" value="{{$picture_temp->id}}" style="display: none">
                    <div class="center">
                        <button id="delete-file" class="btn btn-danger btn-custom">
                            <i class="el el-trash"></i> Delete File
                        </button>
                    </div>
                </div>
                <div class="panel panel-warning" style="display: none;">
                    <div class="panel-heading">
                        <b>Limiting Reasons</b>
                    </div>
                    <div class="panel-body">
                        <ul class="ul-limiting">                                                                <li>
                            Wrong image ratio for Avatar Abyss
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-8">
            <div id="queue_file_actions">
<!-- Info Adding Section -->
                <div class="panel panel-info">
                    <div class="panel-body">
                        <div class="col-xs-12 col-sm-6">
                            <label>Category:</label>
                            <select name="category_id" id="category_id" class="form-control">
                                @foreach ($category as $data)
                                    <option value="{{$data->id}}">{{ $data->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-xs-12 col-sm-6">
                            <label>Sub-Category:</label>
                            <input type="text" id="sub_category" name="sub_category" class="form-control" placeholder="Enter Sub-Category Name" required>
                            <div id="sub_category_suggestion_container"></div>
                        </div>
                    </div>
                </div>
                <div class="panel panel-info">
                    <div class="panel-body">
                        <div class="col-xs-12">
                            <label>Tags</label>
                            <input type="text" class="form-control" name="tags" id="tags" placeholder="separate,tags,by,comma - Tags should be relevant to the content!">
                            <div id="tag_suggestion_container"></div>
                        </div>
                    </div>
                </div>
                <div class="panel panel-info">
                    <div class="panel-body">
                        <div class="col-xs-12">
                            <label>Name/Caption</label>
                            <input type="text" class="form-control" name="description" id="description" placeholder="Enter A Caption / Short Description" maxlength="100">
                        </div>
                    </div>
                </div>
                <div class="panel panel-info">
                    <div class="panel-body">
                        <div class="col-xs-12 form-group">
                            <label>Author Name</label>
                            <input type="text" class="form-control" name="author_name" id="author_name" placeholder="If you know the author, but are not them!">
                        </div>
                        <div class="col-xs-12">
                            <input name="is_author" id="is_author" type="checkbox" value="accept">
                            <b>I am the Author of this content</b>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div id="transition_container">
        <div class="lds-container">
            <div class="lds-hourglass"></div>
        </div>
    </div>
    <div id="submit_info" class="container-refresh">
        <button class="btn btn-success btn-lg btn-block" id="submit-data">
            <i class="el el-refresh"></i> Add/Submit Info And Refresh
        </button>
        <br>
    </div>
</div>
@endsection

@section('script')

<script src="/js/bootstrap-3.4.1.min.js"></script>
<link rel="stylesheet" href="/css/jquery-ui.css">
<script src="/js/jquery-ui.min.js"></script>
<script>
    $(function(){
        $("#submit-data").click(function(e){
            $('#submit_info').css('background', 'transparent');
            $('#transition_container').css('display', 'block');
            e.preventDefault();
            $.ajaxSetup({
                headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    }
            });
            var formData = {
                picture : $("#picture").val(),
                category: $("#category_id").val(),
                subcategory: $("#sub_category").val(),
                tag: $('#tags').val(),
                description: $('#description').val(),
                author: $('#author_name').val(),
                is_author: $('#is_author').is(":checked"),
            };
            $.ajax({
                url : 'submit',
                data : formData,
                type : 'POST',
                error: function(jqXHR, textStatus){
                    setTimeout(function() {
                        $('#transition_container').css('display', '');
                        $('#submit_info').css('background', '');
                    }, 2000);
                },
                success : function (data){
                    $('#transition_container').css('display', '');
                    $('#submit_info').css('background', '');
                    window.location.href = "submit";
                    if(data.error == true){
                        window.alert(data.message);
                    }
                },
                //timeout: 10000
            });
        })
    })

    $(function(){
        if($('#author_name').length) {
            $('input[name=is_author]').change(function() {
                if($('input[name=is_author]').is(':checked')) {
                    $('input[name=author_name]').attr("disabled", true).val("");
                }
                else {
                    $('input[name=author_name]').removeAttr("disabled");
                }
            });
        }
        
        if($('#sub_category').length) {
            $('#sub_category').autocomplete({
                minLength: 2,
                delay : 250,
                appendTo: '#sub_category_suggestion_container',
                source: function(request, response) {
                    $.ajaxSetup({
                        headers: {
                                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                            }
                    });
                    $.ajax({
                        url: "get-sub-category-suggestion",
                        type: 'POST',
                        data:  {
                            text : request.term,
                            category_id : $("#category_id").val()
                        },
                        dataType: "json",
                        success: function(data) {
                            response(data);
                        }
                    });
                }
            });
        }
        if($('#tags').length) {
            $('#tags').autocomplete({
                minLength: 2,
                delay: 250,
                appendTo: '#tag_suggestion_container',
                source: function (request, response) {
                    $.ajaxSetup({
                        headers: {
                                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                            }
                    });
                    $.ajax({
                        url: "get-tag-suggestion",
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
            });
        }
    })


</script>
@endsection