@extends('layouts.index')

@section('title', 'AlgebraBox | The greatest cloud storage')

@section('content')
<div class="row">
	<ol class="breadcrumb">
		<li class="active">Home</li>
	</ol>
</div>
<div class="row">
	<div class="col-md-4">

	</div>
	<div class="col-md-4">
		{{dump($user_disk)}}
		@if(count($user_disk))
		<ul>
			@for($i = 0; $i< $user_disk->number_of_directories; $i++)
			<li><a href="{{$user_disk->directories[$i]['path']}}">{{$user_disk->directories[$i]['name']}}</a></li>
			@endfor
			@for($i = 0; $i< $user_disk->number_of_files; $i++)
			<li>{{$user_disk->files[$i]['name']}}</li>
			@endfor
		</ul>
		@endif
		@if (count($errors) > 0)
		<ul>
			@foreach ($errors->all() as $error)
			<li>{{ $error }}</li>
			@endforeach
		</ul>
		@endif
		@if(Session::has('upload_notice_messages'))
		<div class="alert alert-warning"><em> {!! session('upload_warning_messages') !!}</em></div>
		@endif
		@if(Session::has('dangerMsg'))
		<div class="alert alert-danger"><em> {!! session('dangerMsg') !!}</em></div>
		@endif
	</div>
	<div class="col-md-4">
		<form action="home/action" method="post" enctype="multipart/form-data">
			{{ csrf_field() }}
			<br />
			<h3>New folder</h3>
			<input type="text" name="directory_name" />
			<br /><br />
			<input type="hidden" name="action" value="create-folder"/>
			<input type="submit" value="Create folder" />
		</form>
		<form action="home/action" method="post" enctype="multipart/form-data">
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
