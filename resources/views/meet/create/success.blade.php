@extends('layouts.main')
@section('content-main')
<style>
    .outerdiv{
        margin: 0 25%;
        background-color: white;
        padding: 5%;
        text-align: center;
        box-shadow: 0px 1px 7px 1px #809d87;
        border-radius: 5px;
    }
    .tik-icon{
        color:green;
        font-size: 140px;
        margin:5%;
    }
</style>
<div class="row" style="margin-top: 10%;">
    <div class="col">
        <div class="outerdiv">
            <div class="row">
                <div class="col-md-6" id="makeItRain">
                </div>
                <div class="col-md-6" id="makeItRain2">
                </div>
            </div>
            <h4><b>Congratulations!</h4><br>
            <h6>Your Meet Has Been Created as is in draft mode.</h6>
            <h6>Please Continue to Publish & Open Your Meet. </h6><br>
            <a href="{{$redirect_url}}" class="btn btn-success btn-sm">Continue</a><br>
            <i class="fas fa-check-circle tik-icon"></i>
            <div>
                <h5><b>Thank you for listing your meet with us!</b></h5>
                <h5>- All Gymnastics</h5>
                
            </div>
        </div>
    </div>
</div>
@endsection
<script src="{{ asset('assets/admin/js/party.min.js') }}"></script>
<script>
    function makeItRain() {
        elt = document.getElementById("makeItRain");
        elt2 = document.getElementById("makeItRain2");
        var end = Date.now() + (2 * 1000);

        // go Buckeyes!
        var colors = ['#bb0000', '#ffffff'];
        function frame() {
            party.confetti(elt2, {
                    count: 2, // party.variation.range(20, 40),
                    angle: 60,
                    // spread: 20,
                    // origin: { x: 0 },
                    // colors: colors
                });
            party.confetti(elt, {
                    count: 2, //party.variation.range(20, 40),
                    angle: 120,
                    // spread: 20,
                    // origin: { x: 1 },
                    // colors: colors
                });

                if (Date.now() < end) {
                    requestAnimationFrame(frame);
            }
        }
                
        frame();
    }
    function delay(time) {
        return new Promise(resolve => setTimeout(resolve, time));
    }

    delay(1000).then(() => makeItRain());

    // setInterval(function() {
    //     makeItRain();
    // }, 1000);
</script>