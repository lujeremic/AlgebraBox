@extends('layouts.index')

@section('title', 'AlgebraBox | The greatest cloud storage')
@section('homepage-css-files')
<link rel="stylesheet" href="{{ asset('css/font-awesome/css/font-awesome.min.css') }}"></link>
<link rel="stylesheet" href="{{ asset('css/home.css') }}"></link>
@stop
@section('homepage-js-files')
<script src="{{ asset('js/homepage.js') }}"></script>
@stop
@section('content')
<div class="row">
	<div class="col-md-1">

	</div>
	<div class="col-md-8">
		<h1>Algebra Box</h1>
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
		@if(Session::has('upload_warning_messages'))
		<div class="alert alert-warning"><em> {!! session('upload_warning_messages') !!}</em></div>
		@endif
		@if(Session::has('dangerMsg'))
		<div class="alert alert-danger"><em> {!! session('dangerMsg') !!}</em></div>
		@endif
		@if(count($user_disk))
		<div class="table-responsive">
			<table id="userStorageTable" class="table table-borderless">
				<thead>
					<tr>
						<th class="center"><input id="checkAll" type="checkbox" /></th>
						<th >Name</th>
						<th class="center">Modified</th>
					</tr>
				</thead>
				<tbody>
					@for($i = 0; $i< $user_disk->number_of_directories; $i++)
					<tr>
						<td class="center checkItem"><input id="checkDir-{{$i + 1}}" type="checkbox" /></td>
						<td class="name">
							<a href="{{$user_disk->directories[$i]['path']}}"><span class="typeIcon"><i class="fa fa-folder-o" aria-hidden="true"></i></span>{{$user_disk->directories[$i]['name']}}</a>
						</td>
						<td class="center"><h4>{{$user_disk->directories[$i]['last_modified']}}</h4></td>
					</tr>
					@endfor
					@for($i = 0; $i < $user_disk->number_of_files; $i++)
					<tr>
						<td class="center checkItem"><input id="checkFile-{{$i + 1}}" type="checkbox" /></td>
						<td class="name">
							<a href="{{$user_disk->files[$i]['path']}}">{{$user_disk->files[$i]['name']}}</a>
						</td>
						<td class="center"><h4>{{$user_disk->files[$i]['last_modified']}}</h4></td>
					</tr>
					@endfor
				</tbody>
			</table>
		</div>
		<ul>
		</ul>
		@endif
		@if (count($errors) > 0)
		<ul>
			@foreach ($errors->all() as $error)
			<li>{{ $error }}</li>
			@endforeach
		</ul>
		@endif

	</div>
	<div class="col-md-3">
		<form action="home" method="post" enctype="multipart/form-data">
			{{ csrf_field() }}
			<br />
			<h3>New folder</h3>
			<input type="text" name="directory_name" />
			<br /><br />
			<input type="hidden" name="action" value="create-folder"/>
			<input type="submit" value="Create folder" />
		</form>
		<form action="home" method="post" enctype="multipart/form-data">
			{{ csrf_field() }}
			<br />
			<h3>Upload you files</h3>
			<input type="file" name="files[]" multiple/>
			<br /><br />
			<input type="hidden" name="action" value="upload-files"/>
			<input type="submit" value="Upload" />
		</form>
	</div>

</div>
@stop

