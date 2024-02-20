@extends('layouts.app')

@section('title', __('Terms and Conditions edit'))


@section('content')
    
    <x-h1>{{ __('Terms and Conditions') }}</x-h1>

    <form method="POST" novalidate id="datasets_delete" action="{{route('terms-and-conditions.store')}}">
        @csrf
        <textarea style="display:none;" id="content" name="content">{!! $content !!}</textarea>

        <div class="text-center mt-2">
            <input type="submit" class="btn btn-primary" value="Update">
        </div>
    </form>
    

<script>
    ClassicEditor
        .create( document.querySelector( '#content' ) )
        .catch( error => {
            console.error( error );
        } );
</script>
@endsection
