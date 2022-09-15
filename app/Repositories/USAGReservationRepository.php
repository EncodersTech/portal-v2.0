<?php

namespace App\Repositories;

use App\Models\USAGReservation;
use Illuminate\Database\Eloquent\Builder;

/**
 * Class USAGReservationRepository
 */
class USAGReservationRepository
{
    public function searchUsagReser($searchData)
    {
        $query = USAGReservation::with(['level_category', 'gym', 'usag_sanction.level_category', 'parent', 'children']);

        $query->when($searchData != '', function (Builder $query) use ($searchData) {
            $query->where(function (Builder $query) use ($searchData) {
                $query->orwhereHas('usag_sanction', function (Builder $query) use ($searchData) {
                    $query->Where('number', 'like', $searchData . '%');
                    $query->orWhere('usag_meet_name', 'like', '%' . $searchData . '%');
                });
                $query->orWhereHas('gym', function (Builder $query) use ($searchData) {
                    $query->where('name', 'like', '%' . $searchData . '%');
                });
            });
        });

        $query->orderByDesc('created_at')->whereIn('status', [USAGReservation::RESERVATION_STATUS_PENDING, USAGReservation::RESERVATION_STATUS_HIDE]);

        return $query->paginate(12);
    }
}
