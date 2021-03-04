@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">You are requesting the details for {{$params['count']}} cards. We've sent a text with a verification code to  {{$params['phone']}}</div>

                <div class="card-body">
                    <form action="?" method="post">
                        <input type="text" name="code" />
                        <input type="hidden" name="user_id" value="{{$params['user_id']}}" />
                        <input type="submit">
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
<form name="sAddToBasket" method="post" action="https://www.overclockers.co.uk/checkout/addArticle" class="basketform">
    <input type="hidden" name="sActionIdentifier" value="">
    <input type="hidden" name="sAddAccessories" id="sAddAccessories" value="">
    <input type="hidden" name="sAdd" value="CP-3C9-AM">
    <div id="detailCartButton">
        <div class="detail_quantity_label" data-instock-amount="2">
            <label for="sQuantity_disable-hover">Amount:</label>
            <div class="outer-select" style="width: 65px;"><select id="sQuantity" name="sQuantity" style="width: 65px;">
                    <option value="1">1</option>
                    <option value="2">2</option>
                </select><div class="inner-select"><span class="select-text">1</span></div></div>
            <div class="instock-info" style="display:none;">
                <span class="info-icon"></span>
                <span class="popup-text">
We currently only have 2 of these in stock. More stock should be ordered where necessary and possible.
</span>
                <span class="popup-title">
Info for your selected Amount
</span>
            </div>
        </div>
        <div class="space">&nbsp;</div>
        <input type="submit" id="basketButton" class="sAddToBasketButton" title="AMD Ryzen 9 3900X Twelve Core 4.6GHz (Socket AM4) Processor - Retail Add to basket" name="Add to basket" value="Add to basket" style="">
        <div class="space">&nbsp;</div>
    </div>
    <div class="space">&nbsp;</div>
</form>
