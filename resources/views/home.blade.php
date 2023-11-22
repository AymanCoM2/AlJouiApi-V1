@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">{{ __('AlJouai') }}</div>
                    <div class="card-body">
                        @if (session('status'))
                            <div class="alert alert-success" role="alert">
                                {{ session('status') }}
                            </div>
                        @endif
                        {{ __('Home Page') }}
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
@section('extra-script-1')
@endsection
{{-- <script>
    var botmanWidget = {
        frameEndpoint: '/chat-frame'
    };
</script>
<script src='https://cdn.jsdelivr.net/npm/botman-web-widget@0/build/js/widget.js'></script> --}}
