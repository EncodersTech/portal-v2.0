<?php

namespace App\Queries;

use App\Models\Meet;
use Illuminate\Database\Eloquent\Builder;

/**
 * Class FeaturedMeetDataTable
 */
class FeaturedMeetDataTable
{
    /*
     * @return mixed
     */
    public function get(): Builder
    {
        /** @var Meet $query */
        return Meet::with(['gym','levels','meetCategories','venue_state'])->where('is_featured', true)->select('meets.*');
    }
}
