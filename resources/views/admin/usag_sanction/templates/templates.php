<script id="UsagSanctionSearchTemplate" type="text/x-jsrender">
<div class="col-12 col-xs-12 col-sm-3 col-md-12 col-lg-3 mb-1" style="font-size:12px;">
    <div class="small-box {{:cardBg}}">
        <div class="inner">
            <h5 class="mb-3" style="font-size: 15px;">
                <span class="fas fa-fw fa-plus-square"></span>
                {{:sanction}}
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
                <span class="ml-auto usagHide" id="usagSanctionHide" data-id="{{:usag_id}}">
                   <i class="{{:hideIcon}}"></i>
                </span>
            <a href="#" class="usagDelete" id="usagSanctionDelete" data-id="{{:usag_id}}">
                <i class="fas fa-trash"></i>
            </a>
        </div>
    </div>
</div>


</script>

<script id="UsagSanctionPaginationTemplate" type="text/x-jsrender">

<div class="mt-0 mb-5 col-12 searchSanctionPagination">
    <div class="row paginatorRow">

        <div class="col-lg-10 col-md-6 col-sm-12 d-flex justify-content-end">
            <ul class="pagination" role="navigation">
                <li class="page-item disabled" aria-disabled="true" aria-label="« Previous">
                    <span class="page-link" aria-hidden="true">‹</span>
                </li>

                <li class="page-item active" aria-current="page"><span class="page-link">1</span></li>
                <li class="page-item"><a class="page-link" href="http://david-portal.test/admin/usag-sanctions?page=2">2</a></li>
                <li class="page-item"><a class="page-link" href="http://david-portal.test/admin/usag-sanctions?page=3">3</a></li>
                <li class="page-item"><a class="page-link" href="http://david-portal.test/admin/usag-sanctions?page=4">4</a></li>

                <li class="page-item">
                    <a class="page-link" href="http://david-portal.test/admin/usag-sanctions?page=2" rel="next" aria-label="Next »">›</a>
                </li>
            </ul>

        </div>
    </div>
</div>

</script>
