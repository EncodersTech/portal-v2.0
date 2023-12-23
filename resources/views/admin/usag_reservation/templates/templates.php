<script id="UsagReservationSearchTemplate" type="text/x-jsrender">
<div class="col-12 col-xs-12 col-sm-3 col-md-12 col-lg-3 mb-1" style="font-size:12px;">
    <div class="small-box {{:cardBg}}">
        <div class="inner">
            <h5 class="mb-3" style="font-size: 15px;">
                <span class="fas fa-fw fa-plus-square"></span>
                {{:reservation}}
            </h5>
            <div class="" style="letter-spacing: 0.5px">
                <div class="">
                    <strong>Sanction No.:</strong> {{:sanctionNumber}}
                </div>
                <div class="">
                    <strong>Gym:</strong> {{:gym}}
                </div>
                <div class="">
                    <strong>Meet:</strong> {{:meet}}
                </div>
                <div class="">
                    <strong>Category:</strong> {{:levelCategory}}
                </div>
                <div class="">
                    <strong>Type:</strong>
                    <span class="badge badge-pill {{:typeColor}}">{{:type}}</span>
                </div>
                <div class="">
                    <strong>Last updated:</strong> {{:lastUpdated}}
                </div>
            </div>
        </div>
        <div class="text-right small-box-footer d-flex">
            <span class="ml-auto usagHide" id="usagReservationHide" data-id="{{:usag_id}}">
                <i class="{{:hideIcon}}"></i>
            </span>
            <a href="#" class="usagDelete" id="usagReservationDelete" data-id="{{:usag_id}}">
                <i class="fas fa-trash"></i>
            </a>
        </div>
    </div>
</div>

</script>
