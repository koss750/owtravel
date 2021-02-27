@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">Dashboard</div>

                <div class="card-body">
                    @foreach ($cards["results"]["data"] as $card)
                        <p>{{$card['bank']}} {{$card['account']}}</p>
                        <p>{{$card['number']}}</p>
                        <p>{{$card['expiry']}} {{$card['cvc']}}</p>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
