@extends('layouts.index')

@section('title', 'AlgebraBox | The greatest cloud storage')

@section('content')
<div class="row">
	<ol class="breadcrumb">
		<li class="active">Home</li>
	</ol>
</div>
<div class="row">
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
	<form action="home/upload" method="post" enctype="multipart/form-data">
		{{ csrf_field() }}
		<br />
		Add your files
		<input type="file" name="files[]" multiple/>
		<br /><br />
		<input type="submit" value="Upload" />
	</form>
</div>
@stop
