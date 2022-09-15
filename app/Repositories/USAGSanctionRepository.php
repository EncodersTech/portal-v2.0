<?php

namespace App\Repositories;

use App\Models\USAGSanction;
use Illuminate\Database\Eloquent\Builder;

/**
 * Class USAGSanctionRepository
 */
class USAGSanctionRepository
{
    public function searchUsagSanc($searchData)
    {
        $query = USAGSanction::with(['gym', 'meet', 'level_category', 'parent', 'children'])
            ->whereIn('status',[USAGSanction::SANCTION_STATUS_PENDING, USAGSanction::SANCTION_STATUS_HIDE]);

        $query->when($searchData != '', function (Builder $q) use ($searchData) {
            $q->where(function (Builder $q) use ($searchData) {
                $q->Where('number', 'like',$searchData . '%');
                $q->orWhere('usag_meet_name', 'like', '%' . $searchData . '%');
            });

            $q->orWhereHas('gym', function (Builder $query) use ($searchData) {
                $query->where('name', 'like', '%' . $searchData . '%');
            });
        });

        $query->orderByDesc('created_at');

        return $query->paginate(12);
    }
}
