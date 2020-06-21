@extends('layouts.app')

@section('head')
<title> Category </title>
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
						<button id="add1" data-toggle="modal" data-target="#Category"><i class="glyphicon glyphicon-plus"></i> Category</button>
						<!-- Add Category modal -->
						<div class="modal fade" id="Category" role="dialog">
						    <div class="modal-dialog modal-sm">
							    <div class="modal-content">
							        <div class="modal-header">
							          <button type="button" class="close" data-dismiss="modal">&times;</button>
							          <h4 class="modal-title">Add Category</h4>
							        </div>
							        <div class="modal-body">
							        	<!-- Add Category form -->
							        	<form id='addCategory-form' method="POST" >
											<div class="form-group">
												<label for="addName">Category:</label>
												<input type="text" class="form-control" name="addName" id="addName">
												<li id="add-name-msg" style="display: none; color: red;"></li>
											</div>
										</form>
									</div>
									<div class="modal-footer">
										<button type="submit" id="addCategory" class="btn btn-primary"><i class="glyphicon glyphicon-ok"></i></button>
										<button type="button" class="btn btn-default align-right" data-dismiss="modal"><i class="glyphicon glyphicon-remove"></i></button>
									</div>		
							    </div>
						    </div>
						</div>
						<!-- End Add category modal -->

                        <button id="add2" data-toggle="modal" data-target="#SubCategory"><i class="glyphicon glyphicon-plus"></i> Subcategory</button>
                        <!-- Add Sub Category modal -->
						<div class="modal fade" id="SubCategory" role="dialog">
						    <div class="modal-dialog modal-sm">
							    <div class="modal-content">
							        <div class="modal-header">
							          <button type="button" class="close" data-dismiss="modal">&times;</button>
							          <h4 class="modal-title">Add Subcategory</h4>
							        </div>
							        <div class="modal-body">
							        	<!-- Add Sub Category form -->
							        	<form id='addSubcategory-form' method="POST" >
											<div class="form-group">
												<label for="addSubName">Sub Category:</label>
												<input type="text" class="form-control" name="addSubName" id="addSubName">
												<li id="add-sub-name-msg" style="display: none; color: red;"></li>
											</div>
											<div class="form-group">
												<label for="category">Category:</label>
												<select name="category" id="category" class="form-control">
													@foreach ($categories as $category)
														<option value="{{$category->id}}">{{ $category->name }}</option>
													@endforeach
												</select>
												<li id="add-sub-category-msg" style="display: none; color: red;"></li>
											</div>
										</form>
									</div>
									<div class="modal-footer">
										<button type="submit" id="addSubcategory" class="btn btn-primary "><i class="glyphicon glyphicon-ok"></i></button>
										<button type="button" class="btn btn-default align-right" data-dismiss="modal"><i class="glyphicon glyphicon-remove"></i></button>
									</div>		
							    </div>
						    </div>
						</div>
						<!-- End Add Sub-Category modal -->
						
						<h2 align="center"> Category List</h2><br>

						<!-- Category List -->
						<table class="table table-bordered table-hover" id="sv-table">
							<thead>
								<td align="center" style="width: 8em;"><b>ID</b></td>
								<td align="center"><b>Category</b></td>
								<td align="center"><b>Subcategory</b></td>
								<td align="center" style="width: 8em;"><b>Management</b></td>
							</thead>
							<tbody>
								@foreach ($categories as $category)
									<tr class="data-row odd gradeX" align="center" id='categoryList'>
										<td><b> {{ $category->id }} </b></td>
										<td><b> {{ $category->name }} </b></td>
										<td>
											@foreach ($subcategories as $subcategory)
												@if ($subcategory->category_id == $category->id)
													<div style="height: 2.5em;">
													<b style="float: left; width: 70%; margin-top: 5px;">{{ $subcategory->name }}</b>
													<p style="float: right; width: 30%">
														<button class="edit2" id="editSubcategoryButton" data-subcategoryid='{{ $subcategory->id }}' data-subcategoryname='{{ $subcategory->name }}'><i class="glyphicon glyphicon-pencil"></i></button>
														<button class='delete2' id="deleteSubcategoryButton" data-deletesubcategory='{{ $subcategory->id }}'><i class="glyphicon glyphicon-trash"></i></button>
													</p> <br>
													</div>
												@endif
											@endforeach
										</td>
										<td class="center">
											<button class="edit1" id="editCategoryButton" data-categoryid='{{ $category->id }}' data-categoryname='{{ $category->name }}'><i class="glyphicon glyphicon-pencil"></i></button>
											<button class='delete1' id="deleteCategoryButton" data-deletecategory='{{ $category->id }}'><i class="glyphicon glyphicon-trash"></i></button>
										</td>
									</tr>
									@endforeach
							</tbody>
						</table>
						<!-- End Category List -->

						<!-- Edit Category modal -->
						<div class="modal fade" id="editCategory" role="dialog" style="display: none;">
						    <div class="modal-dialog modal-sm">
							    <div class="modal-content">
							        <div class="modal-header">
							          <button type="button" class="close" data-dismiss="modal">&times;</button>
							          <h4 class="modal-title">Edit Category:</h4>
							        </div>

									<!-- Edit Category form -->
							        <div class="modal-body">
							        	<form id="edit-form1" method="POST" >
											<div class="form-group">
													<label for="editId">ID:</label>
													<input type="text" class="form-control" name="editId" id="editId">
													<li id="edit-id-msg" style="display: none; color: red;"></li>
												</div>

												<div class="form-group">
													<label for="editName">Category:</label>
													<input type="text" class="form-control" name="editName" id="editName">
													<li id="edit-name-msg" style="display: none; color: red;"></li>
												</div>
										</form>
									</div>
									<div class="modal-footer">
										<button type="submit" id="editCategoryConfirm" class="btn btn-primary"><i class="glyphicon glyphicon-ok"></i></button>
										<button type="button" class="btn btn-default align-right" data-dismiss="modal"><i class="glyphicon glyphicon-remove"></i></button>
									</div>
									<!-- Edit Category form -->
								</div>
							</div>
						</div>
						<!-- End Edit Category modal -->
								

						<!-- Delete Category modal -->
						<div class="modal fade" id="deleteCategory" role="dialog">
						    <div class="modal-dialog modal-sm">
							    <div class="modal-content">
							        <div class="modal-header">
							          <button type="button" class="close" data-dismiss="modal">&times;</button>
							          <h4 class="modal-title">Delete Category</h4>
							        </div>

							        <div class="modal-body">
							        	<form id='delete-form1' method="POST" >
									        <div class="form-group">
												<label for="deleteid">Confirm Delete Category ID:</label>
												<p id ='deleteid'></p>
											</div>
										</form>
							        </div>
							        <div class="modal-footer">
							        	<button type="submit" id="deleteCategoryConfirm" class="btn btn-primary align-left"><i class="glyphicon glyphicon-ok"></i></button>
							        	<button type="button" class="btn btn-default align-right" data-dismiss="modal"><i class="glyphicon glyphicon-remove"></i></button>
							        </div>
							    </div> 
						    </div>
						</div>
						<!-- End Delete Category modal -->

						<!-- Edit Sub Category modal -->
						<div class="modal fade" id="editSubcategory" role="dialog" style="display: none;">
						    <div class="modal-dialog modal-sm">
							    <div class="modal-content">
							        <div class="modal-header">
							          <button type="button" class="close" data-dismiss="modal">&times;</button>
							          <h4 class="modal-title">Edit Subcategory:</h4>
							        </div>

									<!-- Edit Sub Category form -->
							        <div class="modal-body">
							        	<form id="edit-form2" method="POST" >
											<div class="form-group">
													<label for="editSubId">ID:</label>
													<input type="text" class="form-control" name="editSubId" id="editSubId">
													<li id="edit-sub-id-msg" style="display: none; color: red;"></li>
												</div>

												<div class="form-group">
													<label for="editSubName">Subcategory:</label>
													<input type="text" class="form-control" name="editSubName" id="editSubName">
													<li id="edit-sub-name-msg" style="display: none; color: red;"></li>
												</div>

												<div class="form-group">
													<label for="editSubType">Category:</label>
													<select name="editSubType" id="editSubType" class="form-control">
														@foreach ($categories as $category)
															<option value="{{$category->id}}">{{ $category->name }}</option>
														@endforeach
													</select>
													<li id="edit-sub-type-msg" style="display: none; color: red;"></li>
												</div>
										</form>
									</div>
									<div class="modal-footer">
										<button type="submit" id="editSubcategoryConfirm" class="btn btn-primary"><i class="glyphicon glyphicon-ok"></i></button>
										<button type="button" class="btn btn-default align-right" data-dismiss="modal"><i class="glyphicon glyphicon-remove"></i></button>
									</div>
									<!-- Edit Sub Category form -->
								</div>
							</div>
						</div>
						<!-- End Edit Sub Category modal -->
								

						<!-- Delete Sub Category modal -->
						<div class="modal fade" id="deleteSubcategory" role="dialog">
						    <div class="modal-dialog modal-sm">
							    <div class="modal-content">
							        <div class="modal-header">
							          <button type="button" class="close" data-dismiss="modal">&times;</button>
							          <h4 class="modal-title">Delete Subcategory</h4>
							        </div>

							        <div class="modal-body">
							        	<form id='delete-form2' method="POST" >
									        <div class="form-group">
												<label for="deletesubid">Confirm Delete Subcategory ID:</label>
												<p id ='deletesubid'></p>
											</div>
										</form>
							        </div>
							        <div class="modal-footer">
							        	<button type="submit" id="deleteSubcategoryConfirm" class="btn btn-primary align-left"><i class="glyphicon glyphicon-ok"></i></button>
							        	<button type="button" class="btn btn-default align-right" data-dismiss="modal"><i class="glyphicon glyphicon-remove"></i></button>
							        </div>
							    </div> 
						    </div>
						</div>
						<!-- End Delete Sub Category modal -->

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

//Category
	// open delete category
	$(document).ready(function() {
  		$('.delete1').click(function(){
		   	$(this).addClass('delete-item-trigger-clicked');
		    var options = {
		      'backdrop': 'static'
		    };
		    $('#deleteCategory').modal(options);
  		})
  		
  		// on show Delete Category
  		$('#deleteCategory').on('show.bs.modal', function() {
		    var el = $(".delete-item-trigger-clicked"); // See how its usefull right here? 
		    var row = el.closest(".data-row");
		    var id = el.data('deletecategory');
		    $("#deleteid").text(id);
		})
		// on hide Delete Category
		$('#deleteCategory').on('hide.bs.modal', function() {
    		$('.delete-item-trigger-clicked').removeClass('delete-item-trigger-clicked');
   		 	$("#edit-form1").trigger("reset");
   		})
  	})

	// delete category
	$(function(){
		$('#deleteCategoryConfirm').click(function(e){
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
				url : 'deleteCategory',
				data : formData,
				type : 'POST',
				success : function (data){
					if(data.error == true){
					}
					else{
						window.location.href = "/admin/category";
						window.alert('Deleted Category!');
					}
				}
			})
 		})
	})

	// open edit category
	$(document).ready(function() {
	   	$('.edit1').click(function(){
	   	$(this).addClass('edit-item-trigger-clicked');
	    var options = {
	      'backdrop': 'static'
	    };
	    $('#editCategory').modal(options);
	    })
	  // on show edit category
	    $('#editCategory').on('show.bs.modal', function() {
	    	var el = $(".edit-item-trigger-clicked");
	    	var row = el.closest(".data-row");
	    	var id = el.data('categoryid');
	    	var name = el.data('categoryname');
	    	$("#editId").val(id);
	   	 	$("#editName").val(name);
	    })
	  // on hide edit category
	    $('#editCategory').on('hide.bs.modal', function() {
	    	$('.edit-item-trigger-clicked').removeClass('edit-item-trigger-clicked');
	   		$("#edit-id-msg").hide();
			$("#edit-name-msg").hide();
	   	 	$("#edit-form1").trigger("reset");
	    })
	})

	// Edit Category
	$(function(){
		$('#editCategoryConfirm').click(function(e){
			e.preventDefault();
			$.ajaxSetup({
			    headers: {
			        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
			    }
			});
			var formData = {
					editid : $('#editId').val(),
					editname : $('#editName').val(),
			};
			$.ajax({
				url : 'editCategory',
				data : formData,
				type : 'POST',
				success : function (data){
					$("#edit-id-msg").hide();
					$("#edit-name-msg").hide();
					if(data.error == true){
						
						if(data.message.editid != undefined){
							$("#edit-id-msg").show().text(data.message.editid[0]);
						}
						if(data.message.editname != undefined){
							$("#edit-name-msg").show().text(data.message.editname[0]);
						}
					}
					else{
						window.location.href = "/admin/category";
						window.alert('Edited Category!');
					}
				}
			})
		})
	})

	// on show add category
	$(document).ready(function() {
		$('#Category').on('hide.bs.modal', function() {
			$("#add-id-msg").hide();
			$("#add-name-msg").hide();
	    	$("#addCategory-form").trigger("reset");
	 	})
	})

	// Add Category
	$(function(){
		$('#addCategory').click(function(e){
			e.preventDefault();
			$.ajaxSetup({
			    headers: {
			        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
			    }
			});
			var formData = {
					name : $('#addName').val(),
			}
			$.ajax({
				url : 'addCategory',
				data : formData,
				type : 'POST',
				success : function (data){
					$("#add-name-msg").hide();
					if(data.error == true){
						if(data.message.name != undefined){
							$("#add-name-msg").show().text(data.message.name[0]);
						}
					}
					else{
						window.location.href = "/admin/category";
						window.alert('Added Category!');
					}
				}
			})
		})
	})

//Sub Category
	// open delete sub category
	$(document).ready(function() {
  		$('.delete2').click(function(){
		   	$(this).addClass('delete-item-trigger-clicked');
		    var options = {
		      'backdrop': 'static'
		    };
		    $('#deleteSubcategory').modal(options);
  		})
  		
  		// on show delete sub category
  		$('#deleteSubcategory').on('show.bs.modal', function() {
		    var el = $(".delete-item-trigger-clicked"); // See how its usefull right here? 
		    var row = el.closest(".data-row");
		    var id = el.data('deletesubcategory');
		    $("#deletesubid").text(id);
		})
		// on hide delete sub category
		$('#deleteSubcategory').on('hide.bs.modal', function() {
    		$('.delete-item-trigger-clicked').removeClass('delete-item-trigger-clicked');
   		 	$("#edit-form2").trigger("reset");
   		})
  	})

	// delete sub category
	$(function(){
		$('#deleteSubcategoryConfirm').click(function(e){
 			e.preventDefault();
			$.ajaxSetup({
				headers: {
				    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
			    }
			});
			var formData = {
					deleteid: $('#deletesubid').text()
			};
			$.ajax({
				url : 'deleteSubcategory',
				data : formData,
				type : 'POST',
				success : function (data){
					if(data.error == true){
					}
					else{
						window.location.href = "/admin/category";
						window.alert('Deleted Sub Category!');
					}
				}
			})
 		})
	})

	// open edit sub category
	$(document).ready(function() {
	   	$('.edit2').click(function(){
			$(this).addClass('edit-item-trigger-clicked');
			var options = {
			'backdrop': 'static'
			};
			$('#editSubcategory').modal(options);
	    })
	  // on edit sub category
	    $('#editSubcategory').on('show.bs.modal', function() {
	    	var el = $(".edit-item-trigger-clicked");
	    	var row = el.closest(".data-row");
	    	var id = el.data('subcategoryid');
	    	var name = el.data('subcategoryname');
	    	$("#editSubId").val(id);
	   	 	$("#editSubName").val(name);
	    })
	  // on hide edit sub category
	    $('#editSubcategory').on('hide.bs.modal', function() {
	    	$('.edit-item-trigger-clicked').removeClass('edit-item-trigger-clicked');
	   		$("#edit-sub-id-msg").hide();
			$("#edit-sub-name-msg").hide();
	   	 	$("#edit-form2").trigger("reset");
	    })
	})

	// edit sub category
	$(function(){
		$('#editSubcategoryConfirm').click(function(e){
			e.preventDefault();
			$.ajaxSetup({
			    headers: {
			        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
			    }
			});
			var formData = {
					editid : $('#editSubId').val(),
					editname : $('#editSubName').val(),
					edittype : $('#editSubType').val(),
			};
			$.ajax({
				url : 'editSubcategory',
				data : formData,
				type : 'POST',
				success : function (data){
					$("#edit-sub-id-msg").hide();
					$("#edit-sub-name-msg").hide();
					$("#edit-sub-type-msg").hide();
					if(data.error == true){
						
						if(data.message.editid != undefined){
							$("#edit-sub-id-msg").show().text(data.message.editid[0]);
						}
						if(data.message.editname != undefined){
							$("#edit-sub-name-msg").show().text(data.message.editname[0]);
						}
						if(data.message.edittype != undefined){
							$("#edit-sub-type-msg").show().text(data.message.edittype[0]);
						}
					}
					else{
						window.location.href = "/admin/category";
						window.alert('Edited Sub Category!');
					}
				}
			})
		})
	})

	// on show add sub category
	$(document).ready(function() {
		$('#SubCategory').on('hide.bs.modal', function() {
			$("#add-id-msg").hide();
			$("#add-name-msg").hide();
			$("#add-email-msg").hide();
			$("#add-class-msg").hide();
	    	$("#addSubcategory-form").trigger("reset");
	 	})
	})

	// Add Sub Category
	$(function(){
		$('#addSubcategory').click(function(e){
			e.preventDefault();
			$.ajaxSetup({
			    headers: {
			        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
			    }
			});
			var formData = {
					name : $('#addSubName').val(),
					category_id: $('#category').val(),
			}
			$.ajax({
				url : 'addSubcategory',
				data : formData,
				type : 'POST',
				success : function (data){
					$("#add-name-msg").hide();
					$("#add-email-msg").hide();
					$("#add-class-msg").hide();
					if(data.error == true){
						if(data.message.name != undefined){
							$("#add-sub-name-msg").show().text(data.message.name[0]);
						}
						if(data.message.category_id != undefined){
							$("#add-sub-category-msg").show().text(data.message.category_id[0]);
						}
					}
					else{
						window.location.href = "/admin/category";
						window.alert('Added Sub category!');
					}
				}
			})
		})
	})
</script>
@endsection