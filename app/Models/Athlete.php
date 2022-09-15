<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use App\Exceptions\CustomBaseException;

class Athlete extends Model
{
    public const CREATE_RULES = [
        'first_name' => ['required', 'string', 'max:255'],
        'last_name' => ['required', 'string', 'max:255'],
        'gender' => ['required', 'in:male,female'],
        'dob' => ['required', 'date_format:m/d/Y', 'before:today'],
        'tshirt_size_id' => ['sometimes', 'nullable', 'integer'],
        'leo_size_id' => ['sometimes', 'nullable', 'integer'],
        'is_us_citizen' => ['sometimes'],

        'usag_no' => ['required_with:usag_level_id', 'numeric', 'digits_between:1,19'],
        'usag_level_id' => ['required_with:usag_no', 'integer'],
        'usag_active' => ['sometimes'],

        'usaigc_no' => ['required_with:usaigc_level_id', 'numeric', 'digits_between:1,19'],
        'usaigc_level_id' => ['required_with:usaigc_no', 'integer'],
        'usaigc_active' => ['sometimes'],

        'aau_no' => ['required_with:aau_level_id', 'alpha_num', 'max:255'],
        'aau_level_id' => ['required_with:aau_no', 'integer'],
        'aau_active' => ['sometimes'],

        'nga_no' => ['required_with:nga_level_id', 'alpha_num', 'max:255'],
        'nga_level_id' => ['required_with:nga_no', 'integer'],
        'nga_active' => ['sometimes']
    ];

    public const UPDATE_RULES = self::CREATE_RULES;

    protected static $importRules = [
        'method' => ['required', 'string', 'in:csv,api'],
        'body' => ['required_if:method,==,api', 'nullable', 'string', 'in:usag,usaigc,nga'],
        'duplicates' => ['required', 'string', 'in:ignore,overwrite,fail'],
        'delimiter' => ['required_if:method,==,csv', 'nullable', 'string'],
    ];

    protected static $importFileRules = [
        'required_if:method,==,csv',
        'nullable',
        'file',
        'mimes:csv,txt',
    ];

    protected $appends = ['athlete_member_ship_attr','athlete_level_attr'];

    protected $guarded = ['id'];

    protected $dates = ['dob'];

    public function gym()
    {
        return $this->belongsTo(Gym::class);
    }

    public function tshirt()
    {
        return $this->belongsTo(ClothingSize::class, 'tshirt_size_id');
    }

    public function leo()
    {
        return $this->belongsTo(ClothingSize::class, 'leo_size_id');
    }

    public function usag_level()
    {
        return $this->belongsTo(AthleteLevel::class, 'usag_level_id');
    }

    public function usaigc_level()
    {
        return $this->belongsTo(AthleteLevel::class, 'usaigc_level_id');
    }

    public function aau_level()
    {
        return $this->belongsTo(AthleteLevel::class, 'aau_level_id');
    }

    public function nga_level()
    {
        return $this->belongsTo(AthleteLevel::class, 'nga_level_id');
    }

    public static function getImportFileRules()
    {
        return array_merge(self::$importFileRules, [
            'max:' . Setting::athleteImportMaxSize()
        ]);
    }

    public static function getImportRules()
    {
        return array_merge(self::$importRules, [
            'csv_file' => self::getImportFileRules()
        ]);
    }

    public function fullName()
    {
        return $this->first_name . ' ' . $this->last_name;
    }

    //this attribute used in Athletes List table (index page) AthleteList.vue. for sorting memberships.
    public function getAthleteMemberShipAttrAttribute()
    {
        if ($this->aau_active) {
            return 'AAU';
        } elseif ($this->nga_active) {
            return 'NGA';
        } elseif ($this->usag_active) {
            return 'USAG';
        } elseif ($this->usaigc_active) {
            return 'USAIGC';
        }

        return null;
    }

    //this attribute used in Athletes List table (index page) AthleteList.vue. for sorting level.
    public function getAthleteLevelAttrAttribute()
    {
        if (isset($this->aau_no) && isset($this->aau_level_id)) {
            return $this->aau_level->name;
        } elseif (isset($this->nga_no) && isset($this->nga_level_id)) {
            return $this->nga_level->name;
        }elseif (isset($this->usag_no) && isset($this->usag_level_id)) {
            return $this->usag_level->name;
        }elseif (isset($this->usaigc_no) && isset($this->usaigc_level_id)) {
            return $this->usaigc_level->name;
        }

        return null;
    }

    public function updateProfile(array $attr) {
        DB::beginTransaction();

        try {

            $old = [];
            $new = [];
            $tshirtSize = null;
            $leoSize = null;

            foreach (self::UPDATE_RULES as $key => $value) {
                switch($key) {
                    case 'dob':
                        $old[$key] = $this->dob;
                        $dob = new \DateTime($attr['dob']);
                        /*if ($dob > now())
                            throw new CustomBaseException('Invalid birth date.', '-1');*/
                        $new[$key] = $dob;
                        $this->dob = $dob;
                        break;

                    case 'tshirt_size_id':
                        $old[$key] = $this->tshirt_size_id;
                        if (isset($attr['tshirt_size_id'])) {
                            $tshirtSize = ClothingSize::find($attr['tshirt_size_id']);
                            if (($tshirtSize == null) || $tshirtSize->chart->is_leo)
                                throw new CustomBaseException('No such T-Shirt size.', '69');
                            $tshirtSize = $tshirtSize->id;
                        }
                        $new[$key] = $tshirtSize;
                        $this->tshirt_size_id = $tshirtSize;
                        break;

                    case 'leo_size_id':
                        $old[$key] = $this->leo_size_id;
                        if (isset($attr['leo_size_id'])) {
                            $leoSize = ClothingSize::find($attr['leo_size_id']);
                            if (($leoSize == null) || !$leoSize->chart->is_leo)
                                throw new CustomBaseException('No such Leotard size.', '69');
                            $leoSize = $leoSize->id;
                        }
                        $new[$key] = $leoSize;
                        $this->leo_size_id = $leoSize;
                        break;

                    case 'is_us_citizen':
                        $old[$key] = $this->is_us_citizen;
                        $this->is_us_citizen = isset($attr['is_us_citizen']);
                        $new[$key] = $this->is_us_citizen;
                        break;

                    case 'usag_no':
                        $old['usag_no'] = $this->usag_no;
                        $old['usag_level_id'] = $this->usag_level_id;
                        $old['usag_active'] = $this->usag_active;
                        if (isset($attr['usag_no'])) {
                            $duplicate = $this->gym->athletes()->where('usag_no', $attr['usag_no'])->first();
                            if (($duplicate !== null) && ($duplicate->id != $this->id))
                                throw new CustomBaseException('There is already an athlete with USAG No ' .
                                    $attr['usag_no'] . ' in this gym.', '-1');

                            $level = AthleteLevel::find($attr['usag_level_id']);
                            if (($level == null) || ($level->sanctioning_body->id != SanctioningBody::USAG))
                                throw new CustomBaseException('No such USAG level.', '-1');

                            if ((($attr['gender'] == 'male') && !$level->level_category->male) ||
                                (($attr['gender'] == 'female') && !$level->level_category->female))
                                throw new CustomBaseException('Invalid Gender / USAG Level combination', -1);

                            $this->usag_no = $attr['usag_no'];
                            $this->usag_level_id = $level->id;
                            $this->usag_active = isset($attr['usag_active']);
                        } else {
                            $this->usag_no = null;
                            $this->usag_level_id = null;
                            $this->usag_active = false;
                        }

                        $new['usag_no'] = $this->usag_no;
                        $new['usag_level_id'] = $this->usag_level_id;
                        $new['usag_active'] = $this->usag_active;
                        break;

                    case 'usaigc_no':
                        $old['usaigc_no'] = $this->usaigc_no;
                        $old['usaigc_level_id'] = $this->usaigc_level_id;
                        $old['usaigc_active'] = $this->usaigc_active;
                        if (isset($attr['usaigc_no'])) {
                            $duplicate = $this->gym->athletes()->where('usaigc_no', $attr['usaigc_no'])->first();
                            if (($duplicate !== null) && ($duplicate->id != $this->id))
                                throw new CustomBaseException('There is already an athlete with USAIGC No ' .
                                    $attr['usaigc_no'] . ' in this gym.', '-1');

                            $level = AthleteLevel::find($attr['usaigc_level_id']);
                            if (($level == null) || ($level->sanctioning_body->id != SanctioningBody::USAIGC))
                                throw new CustomBaseException('No such USAIGC level.', '-1');

                            if ((($attr['gender'] == 'male') && !$level->level_category->male) ||
                                (($attr['gender'] == 'female') && !$level->level_category->female))
                                throw new CustomBaseException('Invalid Gender / USAIGC Level combination', -1);

                            $this->usaigc_no = $attr['usaigc_no'];
                            $this->usaigc_level_id = $level->id;
                            $this->usaigc_active = isset($attr['usaigc_active']);
                        } else {
                            $this->usaigc_no = null;
                            $this->usaigc_level_id = null;
                            $this->usaigc_active = false;
                        }

                        $new['usaigc_no'] = $this->usaigc_no;
                        $new['usaigc_level_id'] = $this->usaigc_level_id;
                        $new['usaigc_active'] = $this->usaigc_active;
                        break;

                    case 'aau_no':
                        $old['aau_no'] = $this->aau_no;
                        $old['aau_level_id'] = $this->aau_level_id;
                        $old['aau_active'] = $this->aau_active;
                        if (isset($attr['aau_no'])) {
                            $duplicate = $this->gym->athletes()->where('aau_no', $attr['aau_no'])->first();
                            if (($duplicate !== null) && ($duplicate->id != $this->id))
                                throw new CustomBaseException('There is already an athlete with AAU No ' .
                                    $attr['aau_no'] . ' in this gym.', '-1');

                            $level = AthleteLevel::find($attr['aau_level_id']);
                            if (($level == null) || ($level->sanctioning_body->id != SanctioningBody::AAU))
                                throw new CustomBaseException('No such AAU level.', '-1');

                            if ((($attr['gender'] == 'male') && !$level->level_category->male) ||
                                (($attr['gender'] == 'female') && !$level->level_category->female))
                                throw new CustomBaseException('Invalid Gender / AAU Level combination', -1);

                            $this->aau_no = $attr['aau_no'];
                            $this->aau_level_id = $level->id;
                            $this->aau_active = isset($attr['aau_active']);
                        } else {
                            $this->aau_no = null;
                            $this->aau_level_id = null;
                            $this->aau_active = false;
                        }

                        $new['aau_no'] = $this->aau_no;
                        $new['aau_level_id'] = $this->aau_level_id;
                        $new['aau_active'] = $this->aau_active;
                        break;

                    case 'nga_no':
                        $old['nga_no'] = $this->nga_no;
                        $old['nga_level_id'] = $this->nga_level_id;
                        $old['nga_active'] = $this->nga_active;
                        if (isset($attr['nga_no'])) {
                            $duplicate = $this->gym->athletes()->where('nga_no', $attr['nga_no'])->first();
                            if (($duplicate !== null) && ($duplicate->id != $this->id))
                                throw new CustomBaseException('There is already an athlete with NGA No ' .
                                    $attr['nga_no'] . ' in this gym.', '-1');

                            $level = AthleteLevel::find($attr['nga_level_id']);
                            if (($level == null) || ($level->sanctioning_body->id != SanctioningBody::NGA))
                                throw new CustomBaseException('No such NGA level.', '-1');

                            if ((($attr['gender'] == 'male') && !$level->level_category->male) ||
                                (($attr['gender'] == 'female') && !$level->level_category->female))
                                throw new CustomBaseException('Invalid Gender / NGA Level combination', -1);

                            $this->nga_no = $attr['nga_no'];
                            $this->nga_level_id = $level->id;
                            $this->nga_active = isset($attr['nga_active']);
                        } else {
                            $this->nga_no = null;
                            $this->nga_level_id = null;
                            $this->nga_active = false;
                        }

                        $new['nga_no'] = 'N'.preg_replace('/[^0-9]/', '', $this->nga_no);
                        $new['nga_level_id'] = $this->nga_level_id;
                        $new['nga_active'] = $this->nga_active;
                        break;

                    case 'usag_level_id':
                    case 'usag_active':
                    case 'usaigc_level_id':
                    case 'usaigc_active':
                    case 'aau_level_id':
                    case 'aau_active':
                    case 'nga_level_id':
                    case 'nga_active':
                        break;

                    default:
                        $old[$key] = $this->attributes[$key];
                        $new[$key] = $attr[$key];
                        $this->attributes[$key] = $attr[$key];
                }
            }

            $this->save();

            $diff = AuditEvent::attributeDiff($old, $new);
            AuditEvent::athleteUpdated(request()->_managed_account, auth()->user(), $this, $diff);
            DB::commit();
            return true;
        } catch(\Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function overwriteProfile(array $attr, FailedAthleteImport $failedImport) {
        DB::beginTransaction();

        try {
            $this->updateProfile($attr);
            $failedImport->delete();
            DB::commit();
            return true;
        } catch(\Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }
}
