@extends('layouts.index')

@section('title', 'AlgebraBox | The greatest cloud storage')
@section('homepage-head-metatags')
<meta name="csrf-token" content="{{ csrf_token() }}">
@stop
@section('homepage-css-files')
<link rel="stylesheet" href="{{ asset('css/font-awesome/css/font-awesome.min.css') }}"></link>
<link rel="stylesheet" href="{{ asset('css/home.css') }}"></link>
@stop
@section('homepage-js-files')
<script src="{{ asset('js/homepage.js') }}"></script>
<script src="{{ asset('js/stickyKit.js') }}"></script>
@stop
@section('content')
<div id="homepageLeftSidemenu">

</div>
<div id="mainContent">
	<div class="row">
		<div class="col-md-12">
			<nav class="breadcrumb">
				@for($i = 0; $i < $breadcrumb['number_of_slugs']; $i++)
				@if($breadcrumb['number_of_slugs'] -1  === $i)
				<span class="breadcrumb-item {{$breadcrumb['breadcrumb_slugs'][$i]['active']}}">{{$breadcrumb['breadcrumb_slugs'][$i]['name']}}</span>
				@else
				<a class="breadcrumb-item {{$breadcrumb['breadcrumb_slugs'][$i]['active']}}" href="{{$breadcrumb['breadcrumb_slugs'][$i]['path']}}">{{$breadcrumb['breadcrumb_slugs'][$i]['name']}}</a>
				/
				@endif
				@endfor
			</nav>
		</div>
		<div class="col-sm-9 col-md-9">
			@if(Session::has('upload_success_messages'))
			<div class="alert alert-success"><em> {!! session('upload_success_messages') !!}</em></div>
			@endif
			@if(Session::has('upload_warning_messages'))
			<div class="alert alert-warning"><em> {!! session('upload_warning_messages') !!}</em></div>
			@endif
			@if(Session::has('dangerMsg'))
			<div class="alert alert-danger"><em> {!! session('dangerMsg') !!}</em></div>
			@endif
			@foreach($errors->getMessages() as $errorMsg)
			@for($i = 0; $i < count($errorMsg); $i++)
			<div class="alert alert-danger">
				{{$errorMsg[$i]}}
			</div>
			@endfor
			@endforeach
			@if(count($user_disk))
			<div class="table-responsive">
				<table id="userStorageTable" class="table table-borderless">
					<thead>
						<tr>
							<th class="center"><input id="checkAll" type="checkbox" /></th>
							<th>Name</th>
							<th class="center">Modified</th>
						</tr>
					</thead>
					<tbody>
						@for($i = 0; $i< $user_disk->number_of_directories; $i++)
						<tr>
							<td class="center checkItem"><input id="checkDir-{{$i + 1}}" type="checkbox" /></td>
							<td class="name">
								<a href="{{$user_disk->directories[$i]['path']}}">
									<span class="typeIcon"><i class="fa fa-folder-o" aria-hidden="true"></i></span>
									<span class="file_name">{{$user_disk->directories[$i]['name']}}</span></a>
							</td>
							<td class="center"><h4>{{$user_disk->directories[$i]['last_modified']}}</h4></td>
						</tr>
						@endfor
						@for($i = 0; $i < $user_disk->number_of_files; $i++)
						<tr>
							<td class="center checkItem"><input id="checkFile-{{$i + 1}}" type="checkbox" /></td>
							<td class="name">
								<a href="{{$user_disk->files[$i]['path']}}">
									<span class="typeIcon"><i class="fa fa-pencil-square-o" aria-hidden="true"></i></span>
									<span class="file_name">{{$user_disk->files[$i]['name']}}</span>
								</a>
							</td>
							<td class="center"><h4>{{$user_disk->files[$i]['last_modified']}}</h4></td>
						</tr>
						@endfor
					</tbody>
				</table>
			</div>

			@endif
			<!-- Trigger the modal with a button -->
			<button id="openMoveFilesModal" type="button" class="hidden btn btn-info btn-lg" data-toggle="modal" data-target="#moveFilesModal"></button>
			<!-- Modal -->
			<div class="modal fade" id="moveFilesModal" role="dialog">
				<div class="modal-dialog modal-lg">
					<div class="modal-content">
						<div class="modal-header">
							<button type="button" class="close" data-dismiss="modal">&times;</button>
							<h4 class="modal-title">Move Your files</h4>
						</div>
						<div class="modal-body">
							<div class="table-responsive">
								<ul id="userDirScheme" class="dirManager">
									@for($i = 0; $i< $directory_manager->total_menu_items ; $i++)
									<li data-link="{{$directory_manager->menu_items[$i]['link']}}">
										<div class="menuItemContainer">
											@if($directory_manager->menu_items[$i]['total_menu_items'] > 0)
											<span id="menuItemSubDirectories-{{$i + 1}}"><i class="fa fa-caret-right"></i></span>
											@endif
											<span class="typeIcon"><i class="fa fa-folder-o" aria-hidden="true"></i></span>
											<span class="file_name">{{$directory_manager->menu_items[$i]['name']}}</span>
										</div>
									</li>
									@endfor
								</ul>
							</div>
						</div>
						<div class="modal-footer">
							<button id="triggerMoveFilesForm" type="button" class="btn btn-primary" disabled>Move</button>
							<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
						</div>
					</div>
				</div>
			</div>
		</div>
		<div class="col-sm-3 col-md-3">
			<div id="userActionContainer">
				<form id="CNDForm" action="home" method="post" enctype="multipart/form-data">
					{{ csrf_field() }}
					<button id="createNewFolder" type="button" class="btn btn-block btn-primary">New folder</button>
					@if(count($errors->get('directory_name')) > 0)
					<br />
					@foreach($errors->get('directory_name') as $errorMsg)
					<div class="form-errors alert alert-danger">%
						{{$errorMsg}}
					</div>
					@endforeach
					@endif
					<div class="form-errors createFolder alert alert-danger" style="display: none">The directory name field is required.</div>
					<input id="newDirectoryName" type="hidden" name="directory_name" />
					<input type="hidden" name="action" value="create-folder"/>
				</form>
				<form id="UFForm" action="home" method="post" enctype="multipart/form-data">
					{{ csrf_field() }}
					<br />
					<button id="uploadFiles" type="button" class="btn btn-block btn-primary">Upload Files</button>
					<input id="showFileManager" style="display: none" type="file" name="files[]" multiple/>
					@if(count($errors->get('files')) > 0)
					<br />
					@foreach($errors->get('files') as $errorMsg)
					<div class="alert alert-danger">
						{{$errorMsg}}
					</div>
					@endforeach
					@endif
					<input type="hidden" name="action" value="upload-files"/>
				</form>
				<div id="renameFileContainer">

				</div>
				<div id="moveFilesContainer">

				</div>
				<div id="copyFilesContainer">

				</div>
				<div id="deleteFilesContainer">

				</div>
			</div>
		</div>
	</div>
</div>
@stop

