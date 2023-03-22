@forelse($usagSanctions as $usagSanction)
    <div class="col-12 col-xs-12 col-sm-3 col-md-12 col-lg-3 mb-1" style="font-size:12px;">
        <div class="small-box {{ \App\Models\USAGSanction::actionColor($usagSanction->action) }}">
            <div class="inner">
                <h5 class="mb-3" style="font-size: 15px;">
                    @if($usagSanction->action == \App\Models\USAGSanction::SANCTION_ACTION_ADD)
                        <span class="fas fa-fw fa-plus-square"></span>
                        New USAG Sanction
                    @else
                        <span class="fas fa-fw fa-pen-square"></span>
                        USAG Sanction Update
                    @endif
                </h5>
                <div class="" style="letter-spacing: 0.5px">
                    <div class="">
                        <strong>Sanction No.:</strong> {{ $usagSanction->number }}
                    </div>
                    <div class="">
                        <strong>Gym:</strong> {{ isset($usagSanction->gym)?$usagSanction->gym->name:'N/A' }}
                    </div>
                    <div class="">
                        <strong>Meet:</strong>
                        <span>
                            {{ isset($usagSanction->meet)?$usagSanction->meet->name:'N/A' }}
                        </span>
                    </div>
                    <div class="">
                        <strong>Category:</strong> {{ isset($usagSanction->level_category)?$usagSanction->level_category->name:'N/A' }}
                    </div>
                    <div class="">
                        <strong>Type:</strong>
                        @if($usagSanction->action == \App\Models\USAGSanction::SANCTION_ACTION_ADD)
                            <span class="badge badge-pill badge-light">New Sanction</span>
                        @elseif($usagSanction->action == \App\Models\USAGSanction::SANCTION_ACTION_UPDATE)
                            <span class="badge badge-pill badge-warning">Details Updated</span>
                        @elseif($usagSanction->action == \App\Models\USAGSanction::SANCTION_ACTION_DELETE)
                            <span class="badge badge-pill badge-info">Sanction Removed</span>
                        @else
                            <span class="badge badge-pill badge-dark">Vendor Change</span>
                        @endif
                    </div>
                    <div class="">
                        <strong>Last
                            updated:</strong> {{ \Carbon\Carbon::parse($usagSanction->timestamp)->format('m/d/y h:i:s a') }}
                    </div>
                </div>
            </div>
            <div class="text-right small-box-footer d-flex">
                <span class="ml-auto usagHide" id="usagSanctionHide" data-id="{{$usagSanction->id}}">
                    @if($usagSanction->status == \App\Models\USAGSanction::SANCTION_STATUS_HIDE)
                        <i class="fas fa-eye-slash"></i>
                    @else
                        <i class="fas fa-eye"></i>
                    @endif
                </span>
                <a href="javascript:void(0);" class="usagDelete" id="usagSanctionDelete" data-id="{{$usagSanction->id}}">
                    <i class="fas fa-trash"></i>
                </a>
            </div>
        </div>
    </div>
@empty
    <h3 style="width:420px; margin:0 auto;">No any USAG Sanction available.</h3>
@endforelse

<div class="mt-0 mb-5 col-12 usagSanctionPagination">
    <div class="row paginatorRow">
        <div class="col-lg-2 col-md-6 col-sm-12 pt-2">
            @if(count($usagSanctions) > 0)
                <span class="d-inline-flex">
                    Showing
                    <span class="font-weight-bold ml-1 mr-1">{{ $usagSanctions->firstItem() }}</span> -
                    <span class="font-weight-bold ml-1 mr-1">{{ $usagSanctions->lastItem() }}</span> of
                    <span class="font-weight-bold ml-1">{{ $usagSanctions->total() }}</span>
                </span>
            @endif
        </div>
        <div class="col-lg-10 col-md-6 col-sm-12 d-flex justify-content-end">
            {{ $usagSanctions->links() }}
        </div>
    </div>
</div>
