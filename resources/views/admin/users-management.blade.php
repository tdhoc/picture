@extends('layouts.app')

@section('head')
<title> Users Management </title>
<meta name="csrf-token" content="{{ csrf_token() }}">
<meta name="viewport" content="width=device-width, initial-scale=1">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
<link rel="stylesheet" href="//cdn.datatables.net/1.10.7/css/jquery.dataTables.min.css">
@endsection

@section('content')
<div class="container">
		<!-- Content -->
		<div class="row">
			<div class="col-md-14" style="margin-right: 30px;">
				<div class="panel panel-default">
					<div class="panel-body"><p>
						<button id="add1" data-toggle="modal" data-target="#User"><i class="glyphicon glyphicon-plus"></i> User</button>
						<!-- Add User modal -->
						<div class="modal fade" id="User" role="dialog">
						    <div class="modal-dialog modal-sm">
							    <div class="modal-content">
							        <div class="modal-header">
							          <button type="button" class="close" data-dismiss="modal">&times;</button>
							          <h4 class="modal-title">Add User</h4>
							        </div>
							        <div class="modal-body">
							        	<!-- Add User form -->
							        	<form id='addUser-form' method="POST" >
											<div class="form-group">
												<label for="addName">Username:</label>
												<input minlength="6" maxlength="24" type="text" class="form-control" name="addName" id="addName">
												<li id="add-name-msg" style="display: none; color: red;"></li>
											</div>

                                            <div class="form-group">
												<label for="addPassword">Password:</label>
												<input minlength="8" type="password" class="form-control" name="addPassword" id="addPassword">
												<li id="add-pass-msg" style="display: none; color: red;"></li>
											</div>

                                            <div class="form-group">
												<label for="addEmail">Email:</label>
												<input maxlength="100" type="email" class="form-control" name="addEmail" id="addEmail">
												<li id="add-email-msg" style="display: none; color: red;"></li>
											</div>

                                            <div class="form-group">
												<label for="addType">Type:</label>
                                                <select name="addType" id="addType" class="form-control">
                                                    <option value="user">user</option>
                                                    <option value="admin">admin</option>
												</select>
												<li id="add-type-msg" style="display: none; color: red;"></li>
											</div>
										</form>
									</div>
									<div class="modal-footer">
										<button type="submit" id="addUser" class="btn btn-primary"><i class="glyphicon glyphicon-ok"></i></button>
										<button type="button" class="btn btn-default align-right" data-dismiss="modal"><i class="glyphicon glyphicon-remove"></i></button>
									</div>		
							    </div>
						    </div>
						</div>
						<!-- End Add User modal -->

						<h2 align="center"> Users List</h2><br>

						<!-- User List -->
						<table class="table table-bordered table-hover" id="sv-table">
							<thead>
								<td align="center" style="width: 8em;"><b>ID</b></td>
								<td align="center"><b>Username</b></td>
								<td align="center"><b>Email</b></td>
                                <td align="center"><b>Type</b></td>
								<td align="center" style="width: 8em;"><b>Management</b></td>
							</thead>
							<tbody>
								@foreach ($users as $user)
									<tr class="data-row odd gradeX" align="center" id='user'>
										<td><b> {{ $user->id }} </b></td>
										<td><b> {{ $user->username }} </b></td>
                                        <td><b> {{ $user->email }} </b></td>
                                        <td><b> {{ $user->type }} </b></td>
										<td class="center">
											<button class="edit1" id="editButton" data-id='{{ $user->id }}' data-email='{{ $user->email }}' data-type='{{ $user->type }}' data-auth='{{ $authUser->id }}'><i class="glyphicon glyphicon-pencil"></i></button>
											<button class='delete1' id="deleteButton" data-delete='{{ $user->id }}' data-type='{{ $user->type }}' data-auth='{{ $authUser->id }}'><i class="glyphicon glyphicon-trash"></i></button>
										</td>
									</tr>
									@endforeach
							</tbody>
						</table>
						<!-- End User List -->

						<!-- Edit User modal -->
						<div class="modal fade" id="editUser" role="dialog" style="display: none;">
						    <div class="modal-dialog modal-sm">
							    <div class="modal-content">
							        <div class="modal-header">
							          <button type="button" class="close" data-dismiss="modal">&times;</button>
							          <h4 class="modal-title">Edit User: </h4>
                                      <p id ='editId'></p>
							        </div>

									<!-- Edit User form -->
							        <div class="modal-body">
							        	<form id="edit-form1" method="POST" >
											<div class="form-group">
													<label for="editEmail">Email:</label>
													<input type="text" class="form-control" name="editEmail" id="editEmail">
													<li id="edit-email-msg" style="display: none; color: red;"></li>
												</div>

												<div class="form-group">
													<label for="editType">Type:</label>
													<select name="editType" id="editType" class="form-control">
                                                        <option value="user">user</option>
                                                        <option value="admin">admin</option>
                                                    </select>
													<li id="edit-type-msg" style="display: none; color: red;"></li>
												</div>
										</form>
									</div>
									<div class="modal-footer">
										<button type="submit" id="editConfirm" class="btn btn-primary"><i class="glyphicon glyphicon-ok"></i></button>
										<button type="button" class="btn btn-default align-right" data-dismiss="modal"><i class="glyphicon glyphicon-remove"></i></button>
									</div>
									<!-- Edit User form -->
								</div>
							</div>
						</div>
						<!-- End Edit User modal -->
								

						<!-- Delete User modal -->
						<div class="modal fade" id="deleteUser" role="dialog">
						    <div class="modal-dialog modal-sm">
							    <div class="modal-content">
							        <div class="modal-header">
							          <button type="button" class="close" data-dismiss="modal">&times;</button>
							          <h4 class="modal-title">Delete User</h4>
							        </div>

							        <div class="modal-body">
							        	<form id='delete-form1' method="POST" >
									        <div class="form-group">
												<label for="deleteid">Confirm Delete User ID:</label>
												<p id ='deleteid'></p>
											</div>
										</form>
							        </div>
							        <div class="modal-footer">
							        	<button type="submit" id="deleteConfirm" class="btn btn-primary align-left"><i class="glyphicon glyphicon-ok"></i></button>
							        	<button type="button" class="btn btn-default align-right" data-dismiss="modal"><i class="glyphicon glyphicon-remove"></i></button>
							        </div>
							    </div> 
						    </div>
						</div>
						<!-- End Delete User modal -->

					</p></div>
				</div>
			</div>
		</div>
		<!-- End Content -->
	</div>
@endsection

@section('script')

<script src="/js/bootstrap-3.4.1.min.js"></script>
<script src="//cdn.datatables.net/1.10.7/js/jquery.dataTables.min.js"></script>

<script>
    //create table
	$(document).ready(function() {
        $('#sv-table').dataTable( {
            "pageLength": 100
        } );
    } );
    
    // open delete User
	$(document).ready(function() {
        $('.delete1').each(function(i){
            var type = $(this).data('type');
			var id = $(this).data('delete');
			var auth = $(this).data('auth');
            if (type == "admin" && id != auth) {
                $(this).hide();
            }
        });
  		$('.delete1').click(function(){
		   	$(this).addClass('delete-item-trigger-clicked');
		    var options = {
		      'backdrop': 'static'
		    };
		    $('#deleteUser').modal(options);
  		})
  		
  		// on show Delete User
  		$('#deleteUser').on('show.bs.modal', function() {
		    var el = $(".delete-item-trigger-clicked");
		    var row = el.closest(".data-row");
            var id = el.data('delete');
		    $("#deleteid").text(id);
		})
		// on hide Delete User
		$('#deleteUser').on('hide.bs.modal', function() {
    		$('.delete-item-trigger-clicked').removeClass('delete-item-trigger-clicked');
   		 	$("#edit-form1").trigger("reset");
   		})
  	})

	// delete User
	$(function(){
		$('#deleteConfirm').click(function(e){
 			e.preventDefault();
			$.ajaxSetup({
				headers: {
				    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
			    }
			});
			var formData = {
					deleteid: $('#deleteid').text()
			};
			$.ajax({
				url : 'deleteUser',
				data : formData,
				type : 'POST',
				success : function (data){
					if(data.error == true){
					}
					else{
						window.location.href = "/admin/users-management";
						window.alert('Deleted User!');
					}
				}
			})
 		})
	})

	// open edit User
	$(document).ready(function() {
        $('.edit1').each(function(i){
            var type = $(this).data('type');
			var id = $(this).data('id');
			var auth = $(this).data('auth');
            if (type == "admin" && id != auth) {
                $(this).hide();
            }
        });
	   	$('.edit1').click(function(){
	   	$(this).addClass('edit-item-trigger-clicked');
	    var options = {
	      'backdrop': 'static'
	    };
	    $('#editUser').modal(options);
	    })
	  // on show edit User
	    $('#editUser').on('show.bs.modal', function() {
	    	var el = $(".edit-item-trigger-clicked");
            var row = el.closest(".data-row");
            var id = el.data('id');
	    	var email = el.data('email');
            var type = el.data('type');
            $("#editId").val(id);
	    	$("#editEmail").val(email);
	   	 	$("#editType").val(type);
	    })
	  // on hide edit User
	    $('#editUser').on('hide.bs.modal', function() {
	    	$('.edit-item-trigger-clicked').removeClass('edit-item-trigger-clicked');
	   		$("#edit-email-msg").hide();
			$("#edit-type-msg").hide();
	   	 	$("#edit-form1").trigger("reset");
	    })
	})

	// Edit User
	$(function(){
		$('#editConfirm').click(function(e){
			e.preventDefault();
			$.ajaxSetup({
			    headers: {
			        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
			    }
			});
			var formData = {
                    editid : $('#editId').val(),
					editemail : $('#editEmail').val(),
					edittype : $('#editType').val(),
			};
			$.ajax({
				url : 'editUser',
				data : formData,
				type : 'POST',
				success : function (data){
					$("#edit-email-msg").hide();
					$("#edit-type-msg").hide();
					if(data.error == true){
						
						if(data.message.editemail != undefined){
							$("#edit-email-msg").show().text(data.message.editemail[0]);
						}
						if(data.message.edittype != undefined){
							$("#edit-type-msg").show().text(data.message.edittype[0]);
						}
					}
					else{
						window.location.href = "/admin/users-management";
						window.alert('Edited User!');
					}
				}
			})
		})
	})

	// on show add User
	$(document).ready(function() {
		$('#User').on('hide.bs.modal', function() {
            $("#add-name-msg").hide();
            $("#add-pass-msg").hide();
            $("#add-email-msg").hide();
			$("#add-type-msg").hide();
	    	$("#addUser-form").trigger("reset");
	 	})
	})

	// Add User
	$(function(){
		$('#addUser').click(function(e){
			e.preventDefault();
			$.ajaxSetup({
			    headers: {
			        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
			    }
			});
			var formData = {
                    username : $('#addName').val(),
                    password : $('#addPassword').val(),
                    email : $('#addEmail').val(),
					type : $('#addType').val(),
			}
			$.ajax({
				url : 'addUser',
				data : formData,
				type : 'POST',
				success : function (data){
                    $("#add-name-msg").hide();
                    $("#add-pass-msg").hide();
                    $("#add-email-msg").hide();
                    $("#add-type-msg").hide();
					if(data.error == true){
						if(data.message.username != undefined){
							$("#add-name-msg").show().text(data.message.username[0]);
                        }
                        if(data.message.password != undefined){
							$("#add-pass-msg").show().text(data.message.password[0]);
                        }
                        if(data.message.email != undefined){
							$("#add-email-msg").show().text(data.message.email[0]);
						}
						if(data.message.type != undefined){
							$("#add-type-msg").show().text(data.message.type[0]);
						}
					}
					else{
						window.location.href = "/admin/users-management";
						window.alert('Added User!');
					}
				}
			})
		})
	})
</script>
@endsection