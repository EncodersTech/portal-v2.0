<?php

namespace App\Models;

use App\Exceptions\CustomBaseException;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use App\Models\FailedCoachImport;

class Coach extends Model
{
    public const CREATE_RULES = [
        'first_name' => ['required', 'string', 'max:255'],
        'last_name' => ['required', 'string', 'max:255'],
        'gender' => ['required', 'in:male,female'],
        'dob' => ['required', 'date_format:m/d/Y', 'before:today'],
        'tshirt_size_id' => ['sometimes', 'nullable', 'integer'],

        'usag_no' => ['required_with:usag_level_id', 'numeric', 'digits_between:1,19'],
        'usag_active' => ['sometimes'],
        'dob' => ['nullable', 'date_format:m/d/Y'],
        'usag_expiry' => ['nullable', 'date_format:m/d/Y'],
        'usag_safety_expiry' => ['nullable', 'date_format:m/d/Y'],
        'usag_safesport_expiry' => ['nullable', 'date_format:m/d/Y'],
        'usag_background_expiry' => ['nullable', 'date_format:m/d/Y'],
        'usag_u100_certification' => ['sometimes'],

        'usaigc_no' => ['nullable', 'numeric', 'digits_between:1,19'],
        'usaigc_background_check' => ['sometimes'],
        'usaigc_active' => ['sometimes'],

        'aau_no' => ['required_with:aau_level_id', 'alpha_num', 'max:255'],
        'nga_no' => ['nullable','alpha_num', 'max:255'],
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

    protected $guarded = ['id'];

    protected $dates = [
        'dob',
        'usag_expiry',
        'usag_safety_expiry',
        'usag_safesport_expiry',
        'usag_background_expiry'
    ];

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

    public function gym()
    {
        return $this->belongsTo(Gym::class);
    }

    public function tshirt()
    {
        return $this->belongsTo(ClothingSize::class, 'tshirt_size_id');
    }

    public function fullName()
    {
        return $this->first_name . ' ' . $this->last_name;
    }

    public function updateProfile(array $attr) {
        DB::beginTransaction();

        try {

            $old = [];
            $new = [];
            $tshirtSize = null;

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

                    case 'usag_no':
                        $old['usag_no'] = $this->usag_no;
                        $old['usag_active'] = $this->usag_active;
                        $old['usag_expiry'] = $this->usag_expiry;
                        $old['usag_safety_expiry'] = $this->usag_safety_expiry;
                        $old['usag_safesport_expiry'] = $this->usag_safesport_expiry;
                        $old['usag_background_expiry'] = $this->usag_background_expiry;
                        $old['usag_u100_certification'] = $this->usag_u100_certification;
                        if (isset($attr['usag_no'])) {
                            $duplicate = $this->gym->coaches()->where('usag_no', $attr['usag_no'])->first();
                            if (($duplicate !== null) && ($duplicate->id != $this->id))
                                throw new CustomBaseException('There is already an coach with USAG No ' .
                                    $attr['usag_no'] . ' in this gym.', '-1');

                            $this->usag_no = $attr['usag_no'];
                            $this->usag_active = isset($attr['usag_active']);
                            $this->usag_expiry = isset($attr['usag_expiry']) ? new \DateTime($attr['usag_expiry']) : null;
                            $this->usag_safety_expiry = isset($attr['usag_safety_expiry']) ? new \DateTime($attr['usag_safety_expiry']) : null;
                            $this->usag_safesport_expiry = isset($attr['usag_safesport_expiry']) ? new \DateTime($attr['usag_safesport_expiry']) : null;
                            $this->usag_background_expiry = isset($attr['usag_background_expiry']) ? new \DateTime($attr['usag_background_expiry']) : null;
                            $this->usag_u100_certification = isset($attr['usag_u100_certification']);
                        } else {
                            $this->usag_no = null;
                            $this->usag_active = false;
                            $this->usag_expiry = null;
                            $this->usag_safety_expiry = null;
                            $this->usag_safesport_expiry = null;
                            $this->usag_background_expiry = null;
                            $this->usag_u100_certification = false;
                        }

                        $new['usag_no'] = $this->usag_no;
                        $new['usag_active'] = $this->usag_active;
                        /*$new['usag_expiry'] = $this->usag_expiry;
                        $new['usag_safety_expiry'] = $this->usag_safety_expiry;
                        $new['usag_safesport_expiry'] = $this->usag_safesport_expiry;
                        $new['usag_background_expiry'] = $this->usag_background_expiry;
                        $new['usag_u100_certification'] = $this->usag_u100_certification;*/
                        break;

                    case 'usaigc_no':
                        $old['usaigc_no'] = $this->usaigc_no;
                        $old['usaigc_background_check'] = $this->usaigc_background_check;
                        if (isset($attr['usaigc_no'])) {
                            $duplicate = $this->gym->coaches()->where('usaigc_no', $attr['usaigc_no'])->first();
                            if (($duplicate !== null) && ($duplicate->id != $this->id))
                                throw new CustomBaseException('There is already an coach with USAIGC No ' .
                                    $attr['usaigc_no'] . ' in this gym.', '-1');

                            $this->usaigc_no = $attr['usaigc_no'];
                            $this->usaigc_background_check = isset($attr['usaigc_background_check']);
                        } else {
                            $this->usaigc_no = null;
                            $this->usaigc_background_check = false;
                        }

                        $new['usaigc_no'] = $this->usaigc_no;
                        $new['usaigc_background_check'] = $this->usaigc_background_check;
                        break;

                    case 'aau_no':
                        $old['aau_no'] = $this->aau_no;
                        if (isset($attr['aau_no'])) {
                            $duplicate = $this->gym->coaches()->where('aau_no', $attr['aau_no'])->first();
                            if (($duplicate !== null) && ($duplicate->id != $this->id))
                                throw new CustomBaseException('There is already an coach with AAU No ' .
                                    $attr['aau_no'] . ' in this gym.', '-1');

                            $this->aau_no = $attr['aau_no'];
                        } else {
                            $this->aau_no = null;
                        }

                        $new['aau_no'] = $this->aau_no;
                        break;

                    case 'nga_no':
                        $old['nga_no'] = $this->nga_no;
                        if (isset($attr['nga_no'])) {
                            $duplicate = $this->gym->coaches()->where('nga_no', $attr['nga_no'])->first();
                            if (($duplicate !== null) && ($duplicate->id != $this->id))
                                throw new CustomBaseException('There is already an coach with NGA No ' .
                                    $attr['nga_no'] . ' in this gym.', '-1');

                            $this->nga_no = $attr['nga_no'];
                        } else {
                            $this->nga_no = null;
                        }

                        $new['nga_no'] = $this->nga_no;
                        break;

                    case 'usag_active':
                    case 'usaigc_active':
                        $old['usaigc_active'] = $this->usaigc_active;
                        if (isset($attr['usaigc_active'])) {
                            $this->usaigc_active = $attr['usaigc_active'];
                        }else{
                            $this->usaigc_active = false;
                        }
                        break;

                    case 'usag_expiry':
                    case 'usag_safety_expiry':
                    case 'usag_safesport_expiry':
                    case 'usag_background_expiry':
                    case 'usag_u100_certification':
                    case 'usaigc_background_check':
                        break;

                    default:
                        $old[$key] = $this->attributes[$key];
                        $new[$key] = $attr[$key];
                        $this->attributes[$key] = $attr[$key];
                }
            }

            $this->save();

            $diff = AuditEvent::attributeDiff($old, $new);
            AuditEvent::coachUpdated(request()->_managed_account, auth()->user(), $this, $diff);
            DB::commit();
            return true;
        } catch(\Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function overwriteProfile(array $attr, FailedCoachImport $failedImport) {
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
