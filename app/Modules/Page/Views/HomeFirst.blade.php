@extends('layouts.auth')

@section('content')

    @if( isset($includeIndex) && $includeIndex === true )
        <h1>Welcome Home</h1>

    @else
        <h1 style="margin-top: 0">Yönlendiriliyorsunuz...</h1>

    @endif

@endsection