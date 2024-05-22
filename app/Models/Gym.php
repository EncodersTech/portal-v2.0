<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use App\Helper;
use Illuminate\Support\Facades\Log;
use App\Exceptions\CustomBaseException;
use Illuminate\Support\Facades\DB;
use App\Models\FailedCoachImport;
use App\Models\ClothingSize;
use App\Services\USAIGCService;
use App\Services\NGAService;
use App\Traits\Excludable;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class Gym extends Model
{
    use Excludable;

    protected $fillable = [
        'name',
        'short_name',
        'profile_picture',
        'addr_1',
        'addr_2',
        'city',
        'state_id',
        'zipcode',
        'country_id',
        'office_phone',
        'mobile_phone',
        'fax',
        'website',
        'usag_membership',
        'usaigc_membership',
        'aau_membership',
        'nga_membership',
    ];

    public const CREATE_RULES = [
        'name' => ['required', 'string', 'max:255'],
        'short_name' => ['required', 'string', 'max:25'],
        'addr_1' => ['required', 'string', 'max:255'],
        'addr_2' => ['nullable', 'string', 'max:255'],
        'city' => ['required', 'string', 'max:255'],
        'state' => ['required', 'string', 'size:2'],
        'zipcode' => ['required', 'regex:/^\d{5}([ \-]\d{4})?$/'],
        'country' => ['required', 'string', 'size:2'],
        'office_phone' => ['required', 'phone:AUTO,US'],
        'mobile_phone' => ['nullable', 'phone:AUTO,US'],
        'fax' => ['nullable', 'phone:AUTO,US'],
        'website' => ['nullable', 'url', 'max:255'],
        'usag_membership' => ['sometimes', 'numeric', 'digits_between:1,19'],
        'usaigc_membership' => ['sometimes', 'numeric', 'digits_between:1,19'],
        'aau_membership' => ['sometimes', 'alpha_num', 'max:255'],
        'nga_membership' => ['sometimes', 'alpha_num', 'max:255'],
    ];

    public const UPDATE_RULES = self::CREATE_RULES;

    public const PROFILE_PICTURE_RULES = [
        'gym_picture' => ['nullable', 'image', 'mimes:jpeg,png,jpg', 'dimensions:min_width=100,min_height=100']
    ];

    protected $appends = ['gym_state'];

    public const IMPORT_METHOD_CSV = 1;
    public const IMPORT_METHOD_API = 2;

    private const _USAG_ATHLETE_FIELDS = [
        'LastName',
        'FirstName',
        'PersonID',
        'Gender',
        'DOB',
        'AthType',
        'AthDiscipline',
        'AthExpiration',
        'AthStatus',
        'Citizen',
    ];

    private const _USAG_COACH_FIELDS = [
        'LastName',
        'FirstName',
        'PersonID',
        'ProType',
        'ProDiscipline',
        'Safety',
        'U100',
        'U110',
        'Background',
        'ProExpiration',
        'ProStatus',
    ];

    // private const _USAIGC_ATHLETE_FILEDS = [
    //     'ClubNum',
    //     'ClubName',
    //     'ClubState',
    //     'LastName',
    //     'FirstName',
    //     'AthleteNumber',
    //     'CompLevel',
    //     'DOB',
    //     'Event'
    // ];

    private const _USAIGC_ATHLETE_FILEDS = [
        'Select',
        'ProfileID',
        'Type',
        'League',
        'Name',
        'Gender',
        'BirthDate',
        'ActivelyRegistered',
        'LastYearRegistered',
        'CompetitionLevel',
        'PrimaryEmail',
        'IsActive'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function state()
    {
        return $this->belongsTo(State::class);
    }

    public function country()
    {
        return $this->belongsTo(Country::class);
    }

    public function athletes() {
        return $this->hasMany(Athlete::class);
    }

    public function coaches() {
        return $this->hasMany(Coach::class);
    }

    public function meets()
    {
        return $this->hasMany(Meet::class);
    }

    public function usag_sanctions()
    {
        return $this->hasMany(USAGSanction::class);
    }

    public function usag_reservations()
    {
        return $this->hasMany(USAGReservation::class);
    }

    public function joined_meets()
    {
        return Meet::whereHas('registrations', function ($q) {
            $q->where('gym_id', $this->id);
        });
    }

    public function getJoinedMeetsAttribute()
    {
        return $this->joined_meets()->get();
    }

    public function getGymStateAttribute()
    {
        if(!empty($this->state) && $this->state != null){
            return $this->state->name;
        }

        return '';
    }

    public function temporary_meets()
    {
        return $this->hasMany(TemporaryMeet::class);
    }

    public function failed_athlete_imports() {
        return $this->hasMany(FailedAthleteImport::class);
    }

    public function failed_coach_imports() {
        return $this->hasMany(FailedCoachImport::class);
    }

    public function registrations()
    {
        return $this->hasMany(MeetRegistration::class);
    }

    public function hasActiveMeets()
    {
        return $this->meets()->where('is_archived', false)->count() > 0;
    }

    /*public function canUseACH()
    {
        return (($this->country->code == 'US') || ($this->country->code == 'US'));
    }*/

    public static function getProfilePictureRules()
    {
        $rules = self::PROFILE_PICTURE_RULES;
        $rules['gym_picture'][] = 'max:' . Setting::profilePictureMaxSize();
        return $rules;
    }

    public function compiledAddress(bool $cityStateCountry = true, bool $oneLiner = false, bool $html = false)
    {
        $lineBreak = ($html ? '<br/>' : "\n");
        $result = $this->addr_1 . ($this->addr_2 != null ? ', ' . $this->addr_2 : '');

        if ($cityStateCountry) {
            $result .= ($oneLiner ? ', ' : $lineBreak);
            $result .= $this->city . ($this->state->code == 'WW' ? '' : ', ' . $this->state->code) . ' ' . $this->zipcode;
            $result .= ($oneLiner ? ', ' : $lineBreak) . $this->country->name;
        }

        return $result;
    }

    public function storeProfilePicture(UploadedFile $profilePicture) : bool
    {
        $old = $this->profile_picture;
        $this->profile_picture = Storage::url(Storage::putFile('public/images/gym', $profilePicture));
        Helper::removeOldFile($old, config('app.default_gym_picture'));
        return $this->save();
    }

    public function clearProfilePicture() : bool
    {
        $default = config('app.default_gym_picture');
        $old = $this->profile_picture;
        $this->profile_picture = $default;
        Helper::removeOldFile($old, $default);
        return $this->save();
    }

    public function updateProfile(array $attr)
    {
        $result = Helper::verifyStateCountryCombo($attr['state'], $attr['country']);
        $state = $result['state'];
        $country = $result['country'];
        $old = [];
        $new = [];

        DB::beginTransaction();

        try {
            foreach (self::UPDATE_RULES as $key => $value) {
                switch ($key) {
                    case 'state':
                        $old['state'] = $this->state->id;
                        $old['state'] = $this->state->name;
                        $new['state'] = $state->id;
                        $new['state'] = $state->name;
                        $this->state()->associate($state);
                        break;

                    case 'country':
                        $old['country'] = $this->country->id;
                        $old['country'] = $this->country->name;
                        $new['country'] = $country->id;
                        $new['country'] = $country->name;
                        $this->country()->associate($country);
                        break;

                    case 'usag_membership':
                    case 'usaigc_membership':
                    case 'aau_membership':
                    case 'nga_membership':
                        $membership = (isset($attr[$key]) ? $attr[$key] : null );

                        if ($membership !== null) {
                            $existingMemberhsip = Gym::where($key, $membership)
                                                        ->where('id', '!=', $this->id)
                                                        ->first();
                            if ($existingMemberhsip !== null)
                                throw new CustomBaseException("Membership #" . $membership . ' is already taken. Please contact us.', -1);
                        }

                        $old[$key] = $this->attributes[$key];
                        $new[$key] = $membership;

                        if (isset($old[$key]) && !isset($new[$key]))
                            throw new CustomBaseException("Once set, a membership can only be replaced by a new number and cannot be removed.", -1);

                        $this->attributes[$key] = $membership;
                        break;

                    case 'name':
                    case 'short_name':
                    case 'addr_1':
                    case 'addr_2':
                    case 'city':
                        $attr[$key] = Helper::title($attr[$key]);
                        // continues below

                    default:
                        $old[$key] = $this->attributes[$key];
                        $new[$key] = $attr[$key];
                        $this->attributes[$key] = $attr[$key];
                }
            }
            $this->save();

            $diff = AuditEvent::attributeDiff($old, $new);
            AuditEvent::gymUpdated(request()->_managed_account, auth()->user(), $this, $diff);

            if ($this->usag_membership !== null) {
                $sanctions = USAGSanction::where('gym_usag_no', $this->usag_membership)
                                        ->where('status', USAGSanction::SANCTION_STATUS_UNASSIGNED)
                                        ->update([
                                            'gym_id' => $this->id,
                                            'status' => USAGSanction::SANCTION_STATUS_PENDING,
                                        ]);

                $reservations = USAGReservation::where('gym_usag_no', $this->usag_membership)
                                        ->where('status', USAGReservation::RESERVATION_STATUS_UNASSIGNED)
                                        ->update([
                                            'gym_id' => $this->id,
                                            'status' => USAGReservation::RESERVATION_STATUS_PENDING,
                                        ]);
            }

            DB::commit();
            return true;
        } catch(\Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function toggleArchived(bool $archived)
    {
        DB::beginTransaction();

        try {
            if (!($archived xor $this->is_archived))
                throw new CustomBaseException(
                    'This gym is ' . ($this->is_archived ? 'already' : 'not') . ' archived.', -1
                );

            $this->is_archived = $archived;
            $this->save();

            AuditEvent::gymArchivalStatusChanged(request()->_managed_account, auth()->user(), $this, $archived);
            DB::commit();
            return true;
        } catch(\Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function createAthlete(array $attr)
    {
        DB::beginTransaction();

        try {

            $dob = new \DateTime($attr['dob']);
            $tshirtSize = null;
            $leoSize = null;

            /*if ($dob > now())
                throw new CustomBaseException('Invalid birth date.', '-1');*/

            if (isset($attr['tshirt_size_id'])) {
                $tshirtSize = ClothingSize::find($attr['tshirt_size_id']);
                if (($tshirtSize == null) || $tshirtSize->chart->is_leo)
                    throw new CustomBaseException('No such T-Shirt size.', '69');
                $tshirtSize= $tshirtSize->id;
            }

            if (isset($attr['leo_size_id'])) {
                $leoSize = ClothingSize::find($attr['leo_size_id']);
                if (($leoSize == null) || !$leoSize->chart->is_leo)
                    throw new CustomBaseException('No such Leotard size.', '69');
                $leoSize = $leoSize->id;
            }



            $athlete = [
                'first_name' => Helper::title($attr['first_name']),
                'last_name' => Helper::title($attr['last_name']),
                'gender' => $attr['gender'],
                'dob' => $dob,
                'is_us_citizen' => isset($attr['is_us_citizen']),

                'tshirt_size_id' => $tshirtSize,
                'leo_size_id' => $leoSize
            ];

            if (isset($attr['usag_no'])) {
                $duplicate = $this->athletes()->where('usag_no', $attr['usag_no'])->first();
                if ($duplicate !== null)
                    throw new CustomBaseException('There is already an athlete with USAG No ' .
                        $attr['usag_no'] . ' in this gym.', '-1');

                $level = AthleteLevel::find($attr['usag_level_id']);
                if ($level == null)
                    throw new CustomBaseException('No such USAG level.', '-1');

                if ((!$level->level_category->male && ($attr['gender'] == 'male')) ||
                    (!$level->level_category->female && ($attr['gender'] == 'female')))
                    throw new CustomBaseException('Invalid Gender / USAG Level combination', -1);

                $athlete += [
                    'usag_no' => $attr['usag_no'],
                    'usag_level_id' => $level->id,
                    'usag_active' => isset($attr['usag_active'])
                ];
            }

            if (isset($attr['usaigc_no'])) {
                $duplicate = $this->athletes()->where('usaigc_no', $attr['usaigc_no'])->first();
                if ($duplicate !== null)
                    throw new CustomBaseException('There is already an athlete with USAIGC No ' .
                        $attr['usaigc_no'] . ' in this gym.', '-1');

                $level = AthleteLevel::find($attr['usaigc_level_id']);
                if ($level == null)
                    throw new CustomBaseException('No such USAIGC level.', '-1');

                if ((!$level->level_category->male && ($attr['gender'] == 'male')) ||
                    (!$level->level_category->female && ($attr['gender'] == 'female')))
                    throw new CustomBaseException('Invalid Gender / USAIGC Level combination', -1);

                $athlete += [
                    'usaigc_no' => $attr['usaigc_no'],
                    'usaigc_level_id' => $level->id,
                    'usaigc_active' => isset($attr['usaigc_active'])
                ];
            }

            if (isset($attr['aau_no'])) {
                $duplicate = $this->athletes()->where('aau_no', $attr['aau_no'])->first();
                if ($duplicate !== null)
                    throw new CustomBaseException('There is already an athlete with AAU No ' .
                        $attr['aau_no'] . ' in this gym.', '-1');

                $level = AthleteLevel::find($attr['aau_level_id']);
                if ($level == null)
                    throw new CustomBaseException('No such AAU level.', '-1');

                if ((!$level->level_category->male && ($attr['gender'] == 'male')) ||
                    (!$level->level_category->female && ($attr['gender'] == 'female')))
                    throw new CustomBaseException('Invalid Gender / AAU Level combination', -1);

                $athlete += [
                    'aau_no' => $attr['aau_no'],
                    'aau_level_id' => $level->id,
                    'aau_active' => isset($attr['aau_active']),
                ];
            }

            if (isset($attr['nga_no'])) {
                $duplicate = $this->athletes()->where('nga_no', $attr['nga_no'])->first();
                if ($duplicate !== null)
                    throw new CustomBaseException('There is already an athlete with NGA No ' .
                        $attr['nga_no'] . ' in this gym.', '-1');

                $level = AthleteLevel::find($attr['nga_level_id']);
                if ($level == null)
                    throw new CustomBaseException('No such NGA level.', '-1');

                if ((!$level->level_category->male && ($attr['gender'] == 'male')) ||
                    (!$level->level_category->female && ($attr['gender'] == 'female')))
                    throw new CustomBaseException('Invalid Gender / NGA Level combination', -1);

                $athlete += [
                    'nga_no' => $attr['nga_no'],
                    'nga_level_id' => $level->id,
                    'nga_active' => isset($attr['nga_active']),
                ];
            }

            $athlete = $this->athletes()->create($athlete);
            AuditEvent::athleteCreated(request()->_managed_account, auth()->user(), $athlete);
            DB::commit();
            return $athlete;
        } catch(\Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function removeAthlete(string $athlete) {
        DB::beginTransaction();

        try {
            $athlete = $this->athletes()->find($athlete);
            if ($athlete == null)
                throw new CustomBaseException('No such athlete.', '-1');

            AuditEvent::athleteRemoved(request()->_managed_account, auth()->user(), $athlete);
            $athlete->delete();
            DB::commit();
            return;
        } catch(\Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function removeAthleteBatch(string $athletes) {
        $athletes = explode(',', $athletes);
        if (($athletes === false) || (count($athletes) < 1))
            throw new CustomBaseException('No athletes were selected.', '-1');

        DB::beginTransaction();
        try {
            foreach ($athletes as $athlete) {
                $athlete = $this->athletes()->find($athlete);
                if ($athlete == null)
                    throw new CustomBaseException('No such athlete.', '-1');

                AuditEvent::athleteRemoved(request()->_managed_account, auth()->user(), $athlete);
                $athlete->delete();
            }
            DB::commit();
            return true;
        } catch(\Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function removeFailedAthleteImport(string $athlete) {
        DB::beginTransaction();

        try {
            $athlete = $this->failed_athlete_imports()->find($athlete);
            if ($athlete == null)
                throw new CustomBaseException('No such entry.', '-1');

            //No need to register audit event.
            $athlete->delete();
            DB::commit();
            return;
        } catch(\Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function removeFailedAthleteBatch(string $athletes) {
        $athletes = explode(',', $athletes);
        if (($athletes === false) || (count($athletes) < 1))
            throw new CustomBaseException('No entries were selected.', '-1');

        DB::beginTransaction();
        try {
            foreach ($athletes as $athlete) {
                $athlete = $this->failed_athlete_imports()->find($athlete);
                if ($athlete == null)
                    throw new CustomBaseException('No such entry.', '-1');

                //No need to register audit event.
                $athlete->delete();
            }
            DB::commit();
            return true;
        } catch(\Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function createAthleteFromFailedImport(array $attr, FailedAthleteImport $failedImport) {
        DB::beginTransaction();

        try {
            $athlete = $this->createAthlete($attr);
            $failedImport->delete();
            DB::commit();
            return $athlete;
        } catch(\Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function importAthletes(string $method, string $duplicates, ?string $body = null,
        UploadedFile $csvFile = null, string $delimiter = ',')
    {
        $result = [
            'imported' => 0,
            'failed' => 0,
            'ignored' => 0,
            'overwritten' => 0,
        ];

        DB::beginTransaction();

        try {
            switch ($method) {
                case 'api':{
                    switch ($body){
                        case 'nga':
                            $this->_importAthleteNGA($result, $duplicates);
                            break;
                        default:
                            $this->_importAthletesUSAIGCApi($result, $duplicates);
                            break;
                    }
                }
                break;
                    //throw new CustomBaseException('USAIGC server import is disabled.', -1);
                default:
                    $this->_importAthletesCSV($result, $csvFile, $duplicates, $delimiter);
            }

            DB::commit();
            return $result;
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }

        return $result;
    }
    private function _importAthleteNGA(array &$result, string $duplicates)
    {
        try {
            
            if ($this->nga_membership == null)
                throw new CustomBaseException('A gym needs to have an NGA membership number to import from NGA servers.');

            $ngaService = resolve(NGAService::class); /** @var NGAService $ngaService */
            $imports = $ngaService->getClub($this->nga_membership); // trackthis_1
            // print_r($imports);
            // print_r($imports['results'][0]['result']['row']);
            // die();
            foreach ($imports as $import) {
                $new = [
                    'first_name' => null,
                    'last_name' => null,
                    'gender' => null,
                    'dob' => null,
                    'is_us_citizen' => null,
                    'tshirt_size_id' => null,
                    'nga_no' => null,
                    'nga_level_id' => null,
                    'nga_active' => null
                ];

                try {
                    $issues = [];
                    $first_name = null;
                    $last_name = null;
                    $dob = null;
                    $gender = null;
                    $us_citizen = null;
                    $nga_no = null;
                    $level = null;
                    $active = null;
                    $tshirtSizeId = null;

                    $nga_no = trim($import['NGANumber']);
                    $a = ['nga_no' => $nga_no];
                    try {
                        $vv = Validator::make($a, [
                            'nga_no' => Athlete::CREATE_RULES['nga_no']
                        ])->validate();
                    } catch (ValidationException $ve) {
                        $issues[] = 'Invalid NGA number value `' . $import['NGANumber'] . '`';
                    }
                    $nga_no = $a['nga_no'];
                    $nga_raw = substr($nga_no,1);
                    $nga_no_with_n = 'NGA'.$nga_raw;

                    $athlete = $this->athletes()
                    ->where('nga_no', $nga_no)
                    ->orWhere('nga_no', $nga_raw)
                    ->orWhere('nga_no', $nga_no_with_n)
                    ->first();
                    
                    if (($athlete == null) || ($duplicates == 'overwrite') || ($duplicates == 'fail')) {
                        $first_name = trim($import['FirstName']);
                        $len = strlen($first_name);
                        if (($len < 1) || ($len > 255))
                            $issues[] = 'Invalid first name value `' . $import['FirstName'] . '`';

                        $last_name = trim($import['LastName']);
                        $len = strlen($last_name);
                        if (($len < 1) || ($len > 255))
                            $issues[] = 'Invalid last name value `' . $import['LastName'] . '`';


                        // bug solved with checking the date validation .......... START
                        $date_chk = explode("/",$import['DOB']);
                        if((intval($date_chk[0])<=0 || intval($date_chk[0]) >12) || 
                        (intval($date_chk[1])<=0 || intval($date_chk[1]) >31))
                        {
                            $issues[] = 'Invalid date value `' . $import['DOB'] . '`';
                        }
                        else
                        {
                            $dob = \DateTime::createFromFormat(NGAService::API_DATE_FORMAT, $import['DOB']);
                            if (($dob === null) || ($dob === false)) {
                                $dob = new \DateTime();
                                $issues[] = 'Invalid date value `' . $import['DOB'] . '`';
                            } else {
                                $dob = $dob->setTime(0, 0);
                            }
                        }
                        // bug solved with checking the date validation .......... END

                        $gender = strtolower($import['Gender']);
                        if (!in_array($gender, ['m', 'f'])) {
                            $issues[] = 'Invalid gender value `' . $import['Gender'] . '`';
                        }
                        else
                            $gender = $gender == 'm' ? "male" : "female";

                        // $us_citizen = strtolower($import['USACITIZEN']);
                        // if (!in_array($us_citizen, ['yes', 'no'])) {
                        //     $issues[] = 'Invalid US Citizen value `' . $import['USACITIZEN'] . '`';
                        // }
                        $us_citizen = false;
                        $c_id = $gender == 'male' ? LevelCategory::GYMNASTICS_MEN : LevelCategory::GYMNASTICS_WOMEN ;
                        $level = AthleteLevel::where('abbreviation', $import['Level'])->where('sanctioning_body_id', SanctioningBody::NGA)->where('level_category_id', $c_id)->first();
                        // $level = AthleteLevel::where('abbreviation', $import['Level'])->first();
                        
                        
                        if ($level == null) {
                            $issues[] = 'Invalid level value `' . $import['Level'] . '`';
                        } else if ($gender != null) {
                            if ((($gender == 'male') && !$level->level_category->male) ||
                            (($gender == 'female') && !$level->level_category->female))
                            {
                                $issues[] = 'Invalid Gender / NGA Level combination : `' .
                                    $import['Level'] . '` and `' . $gender .'`';
                            }
                        }

                        $active = strtolower($import['ActiveReg']);
                        if (!in_array($active, ['y', 'n'])) {
                            $issues[] = 'Invalid gender value `' . $import['ActiveReg'] . '`';
                        }
                        $active = $active =='y' ? true : false;
                        // $active = true;

                        $tshirtSize = trim($import['TShirt']);
                        $tshirt = ClothingSize::where('size', $tshirtSize)->where('clothing_size_chart_id',ClothingSizeChart::CHART_DEFAULT_TSHIRT)->first();
                        if(!$tshirt == null)
                            $tshirtSizeId = $tshirt['id'];

                        $issues = count($issues) > 0 ? implode("\n", $issues) : null;

                        $create = ($athlete == null);
                        $needs_to_fail = !$create && ($duplicates == 'fail');
                        if ($needs_to_fail)
                            throw new CustomBaseException($issues, FailedAthleteImport::ERROR_CODE_DUPLICATE);

                        if ($issues != null)
                            throw new CustomBaseException($issues , -1);

                        $new = [
                            'first_name' => Helper::title($first_name),
                            'last_name' => Helper::title($last_name),
                            'gender' => $gender,
                            'dob' => $dob,
                            'is_us_citizen' => $us_citizen,
                            'tshirt_size_id' => $tshirtSizeId,
                            'nga_no' => $nga_no,
                            'nga_level_id' => $level->id,
                            'nga_active' => $active
                        ];

                        $old = [];

                        if (!$create) {
                            $old = [
                                'first_name' => $athlete->first_name,
                                'last_name' => $athlete->last_name,
                                'gender' => $athlete->gender,
                                'dob' => $athlete->dob,
                                'is_us_citizen' => $athlete->is_us_citizen,
                                'tshirt_size_id' => $athlete->tshirt_size_id,
                                'nga_no' => $athlete->nga_no,
                                'nga_level_id' => $athlete->nga_level_id,
                                'nga_active' => $athlete->nga_active
                            ];
                        }

                        $diff = AuditEvent::attributeDiff($old, $new);
                        if (count($diff) < 1) { // No change, skip.
                            $result['ignored']++;
                            continue;
                        }

                        if ($create)
                            $athlete = $this->athletes()->create($new);
                        else
                            $athlete->update($new);

                        $athlete->save();
                        AuditEvent::athleteImportedApi(
                            request()->_managed_account, auth()->user(), $athlete, SanctioningBody::NGA,
                            $diff, !$create
                        );
                        $result[$create ? 'imported' : 'overwritten']++;
                    } else { // $duplicates == 'ignore'
                        $result['ignored']++;
                        continue;
                    }
                } catch(\Throwable $e) {
                    $code = FailedAthleteImport::ERROR_CODE_SERVER;

                    if ($e instanceof CustomBaseException) {
                        $code = ($e->getCode() ==  FailedAthleteImport::ERROR_CODE_DUPLICATE ?
                            FailedAthleteImport::ERROR_CODE_DUPLICATE :
                            FailedAthleteImport::ERROR_CODE_VALIDATION
                        );
                    }

                    $raw = json_encode($import);
                    if ($raw === false)
                        throw new CustomBaseException('Server error', ErrorCodeCategory::getCategoryBase('General') + 1);

                    $this->failed_athlete_imports()->create([
                        'first_name' => Helper::title($first_name),
                        'last_name' => Helper::title($last_name),
                        'gender' => $gender,
                        'dob' => $dob,
                        'is_us_citizen' => ($us_citizen != null) ? $us_citizen : false,
                        'nga_no' => $nga_no,
                        'nga_level_id' => ($level != null) ? $level->id : null,
                        'nga_active' => ($active != null) ? $active : false,
                        'method' => self::IMPORT_METHOD_API,
                        'sanctioning_body_id' => SanctioningBody::NGA,
                        'raw' =>  $raw,
                        'error_code' => $code,
                        'error_message' => $e->getMessage()
                    ]);

                    //throw $e;
                    $result['failed']++;
                }
            }
            return $result;
        } catch(\Throwable $e) {
            throw $e;
        }
    }
    private function _importAthletesCSV(array &$result, UploadedFile $csvFile, string $duplicates,
        string $delimiter) {
        $handle = false;
        try {

            if (!in_array($delimiter, [',', ';']))
                throw new CustomBaseException('Invalid delimiter.', -1);

            $content = file($csvFile->getRealPath(), FILE_IGNORE_NEW_LINES);
            if ($content === false)
                throw new CustomBaseException('A server error occured.', ErrorCodeCategory::getCategoryBase('General') + 4);

            $rows = array_map(function ($item) use ($delimiter) {
                return str_getcsv($item, $delimiter);
            }, $content);
            // trim the rows
            $rows = array_map(function ($item) {
                return array_map('trim', $item);
            }, $rows);

            $headers = array_shift($rows);
            $headers = array_map(function ($item) {
                return preg_replace('/[^A-Za-z0-9\-]/', '', $item);
            }, $headers);
            
            if ($headers === null)
                throw new CustomBaseException('This is not a valid athlete file from USAG / USAIGC.', -1);

            // Dynamically store column name indexes so if the imported file spcification changes,
            // we don't have to change hardcoded indexes.
            $_i = [];
            foreach ($headers as $index => $value)
                $_i[$value] = $index;
            $body = $this->_detectAthleteCSVFileSource($headers);
            switch ($body) {
                case SanctioningBody::USAG:
                    foreach ($rows as $row) {
                        if ($row[$_i['AthType']] != 'Athlete')
                            continue;

                        $new = [
                            'first_name' => null,
                            'last_name' => null,
                            'gender' => null,
                            'dob' => null,
                            'is_us_citizen' => null,
                            'usag_no' => null,
                            'usag_level_id' => null,
                            'usag_active' => null
                        ];

                        try {
                            $issues = [];
                            $first_name = null;
                            $last_name = null;
                            $dob = null;
                            $gender = null;
                            $us_citizen = null;
                            $usag_no = null;
                            $level = null;
                            $active = null;

                            $usag_no = trim($row[$_i['PersonID']]);
                            $a = ['usag_no' => $usag_no];
                            try {
                                $vv = Validator::make($a, [
                                    'usag_no' => Athlete::CREATE_RULES['usag_no']
                                ])->validate();
                            } catch (ValidationException $ve) {
                                $issues[] = 'Invalid USAG number value `' . $row[$_i['PersonID']] . '`';
                            }
                            $usag_no = $a['usag_no'];

                            $athlete = $this->athletes()->where('usag_no', $usag_no)->first();
                            if (($athlete == null) || ($duplicates == 'overwrite') || ($duplicates == 'fail')) {
                                $first_name = trim($row[$_i['FirstName']]);
                                $len = strlen($first_name);
                                if (($len < 1) || ($len > 255))
                                    $issues[] = 'Invalid first name value `' . $row[$_i['FirstName']] . '`';

                                $last_name = trim($row[$_i['LastName']]);
                                $len = strlen($last_name);
                                if (($len < 1) || ($len > 255))
                                    $issues[] = 'Invalid last name value `' . $row[$_i['LastName']] . '`';

                                $dob = \DateTime::createFromFormat('n/j/Y', $row[$_i['DOB']]);
                                if (($dob === null) || ($dob === false)) {
                                    $dob = new \DateTime();
                                    $issues[] = 'Invalid date value `' . $row[$_i['DOB']] . '`';
                                } else {
                                    $dob = $dob->setTime(0, 0);
                                }

                                $gender = strtolower($row[$_i['Gender']]);
                                if (!in_array($gender, ['male', 'female'])) {
                                    $gender = 'N/A';
                                    $issues[] = 'Invalid gender value `' . $row[$_i['Gender']] . '`';
                                }

                                $us_citizen = strtolower($row[$_i['Citizen']]) == 'yes';

                                $level = AthleteLevel::where('code', $row[$_i['Level']])->first();
                                if ($level == null) {
                                    $issues[] = 'Invalid level value `' . $row[$_i['Level']] . '`';
                                } else if ($gender != null) {
                                    if ((($gender == 'male') && !$level->level_category->male) ||
                                    (($gender == 'female') && !$level->level_category->female))
                                    $issues[] = 'Invalid Gender / USAG Level combination : `' .
                                        $row[$_i['Level']] . '` and `' . $gender .'`';
                                }

                                $active = strtolower($row[$_i['AthStatus']]) == 'active';

                                $issues = count($issues) > 0 ? implode("\n", $issues) : null;

                                $create = ($athlete == null);
                                $needs_to_fail = !$create && ($duplicates == 'fail');
                                if ($needs_to_fail)
                                    throw new CustomBaseException($issues, FailedAthleteImport::ERROR_CODE_DUPLICATE);

                                if ($issues != null)
                                    throw new CustomBaseException($issues , -1);

                                $new = [
                                    'first_name' => Helper::title($first_name),
                                    'last_name' => Helper::title($last_name),
                                    'gender' => $gender,
                                    'dob' => $dob,
                                    'is_us_citizen' => $us_citizen,
                                    'usag_no' => $usag_no,
                                    'usag_level_id' => $level->id,
                                    'usag_active' => $active
                                ];

                                $old = [];

                                if (!$create) {
                                    $old = [
                                        'first_name' => $athlete->first_name,
                                        'last_name' => $athlete->last_name,
                                        'gender' => $athlete->gender,
                                        'dob' => $athlete->dob,
                                        'is_us_citizen' => $athlete->is_us_citizen,
                                        'usag_no' => $athlete->usag_no,
                                        'usag_level_id' => $athlete->usag_level_id,
                                        'usag_active' => $athlete->usag_active,
                                    ];
                                }

                                $diff = AuditEvent::attributeDiff($old, $new);
                                if (count($diff) < 1) { // No change, skip.
                                    $result['ignored']++;
                                    continue;
                                }

                                if ($create)
                                    $athlete = $this->athletes()->create($new);
                                else
                                    $athlete->update($new);

                                $athlete->save();
                                AuditEvent::athleteImportedCsv(
                                    request()->_managed_account, auth()->user(), $athlete, $body,
                                    $diff, !$create
                                );
                                $result[$create ? 'imported' : 'overwritten']++;
                            } else { // $duplicates == 'ignore'
                                $result['ignored']++;
                                continue;
                            }
                        } catch(\Throwable $e) {
                            $code = FailedAthleteImport::ERROR_CODE_SERVER;

                            if ($e instanceof CustomBaseException) {
                                $code = ($e->getCode() ==  FailedAthleteImport::ERROR_CODE_DUPLICATE ?
                                    FailedAthleteImport::ERROR_CODE_DUPLICATE :
                                    FailedAthleteImport::ERROR_CODE_VALIDATION
                                );
                            }

                            $raw = json_encode($row);
                            if ($raw === false)
                                throw new CustomBaseException('Server error', ErrorCodeCategory::getCategoryBase('General') + 1);

                            $this->failed_athlete_imports()->create([
                                'first_name' => Helper::title($first_name),
                                'last_name' => Helper::title($last_name),
                                'gender' => $gender,
                                'dob' => $dob,
                                'is_us_citizen' => ($us_citizen != null) ? $us_citizen : false,
                                'usag_no' => $usag_no,
                                'usag_level_id' => ($level != null) ? $level->id : null,
                                'usag_active' => ($active != null) ? $active : false,
                                'method' => self::IMPORT_METHOD_CSV,
                                'sanctioning_body_id' => $body,
                                'raw' =>  $raw,
                                'error_code' => $code,
                                'error_message' => $e->getMessage()
                            ]);

                            //throw $e;
                            $result['failed']++;
                        }
                    }
                    break;

                case SanctioningBody::USAIGC:
                    foreach ($rows as $row) {
                        if (trim($row[$_i['Type']]) != 'Athlete')
                            continue;

                        $new = [
                            'first_name' => null,
                            'last_name' => null,
                            'gender' => null,
                            'dob' => null,
                            'is_us_citizen' => null,
                            'usaigc_no' => null,
                            'usaigc_level_id' => null,
                            'usaigc_active' => null
                        ];
                        try {
                            $issues = [];
                            $first_name = null;
                            $last_name = null;
                            $dob = null;
                            $gender = null;
                            $us_citizen = null;
                            $usaigc_no = null;
                            $level = null;
                            $active = null;

                            $usaigc_no = trim($row[$_i['League']]);
                            $usaigc_no = str_replace('IGC', '', $usaigc_no);
                            $a = ['usaigc_no' => $usaigc_no];
                            try {
                                $vv = Validator::make($a, [
                                    'usaigc_no' => Athlete::CREATE_RULES['usaigc_no']
                                ])->validate();
                            } catch (ValidationException $ve) {
                                $issues[] = 'Invalid USAIGC number value `' . $row[$_i['League']] . '`';
                            }
                            $usaigc_no = $a['usaigc_no'];

                            $athlete = $this->athletes()->where('usaigc_no', $usaigc_no)->first();
                            if (($athlete == null) || ($duplicates == 'overwrite') || ($duplicates == 'fail')) {
                                $first_name = trim($row[$_i['Name']]);
                                $len = strlen($first_name);
                                if (($len < 1) || ($len > 255))
                                    $issues[] = 'Invalid name value `' . $row[$_i['Name']] . '`';
                                else
                                {
                                    $last_name = explode(' ', $first_name)[1];
                                    $first_name = explode(' ', $first_name)[0];
                                }
                                
                                $dob = \DateTime::createFromFormat('n/j/Y', $row[$_i['BirthDate']]);
                                if (($dob === null) || ($dob === false)) {
                                    $dob = new \DateTime();
                                    $issues[] = 'Invalid date value `' . $row[$_i['BirthDate']] . '`';
                                } else {
                                    $dob = $dob->setTime(0, 0);
                                }

                                $gender = trim($row[$_i['Gender']]);
                                $gender == "F" ? "female" : "male";
                                if (!in_array($gender, ['male', 'female'])) {
                                    $issues[] = 'Invalid gender value `' . $row[$_i['Gender']] . '`';
                                }

                                $us_citizen = true;//strtolower($row[$_i['Citizen']]) == 'yes';

                                $competitionLevel = trim($row[$_i['CompetitionLevel']]);
                                // make an space between character and number
                                $competitionLevel = preg_replace('/([a-zA-Z])([0-9])/', '$1 $2', $competitionLevel);
                                $level = AthleteLevel::where('name', $competitionLevel)->first();
                                if ($level == null) {
                                    $issues[] = 'Invalid level value `' . $competitionLevel . '`';
                                } else if ($gender != null) {
                                    if ((($gender == 'male') && !$level->level_category->male) ||
                                    (($gender == 'female') && !$level->level_category->female))
                                    $issues[] = 'Invalid Gender / USAIGC Level combination : `' .
                                    $competitionLevel . '` and `' . $gender .'`';
                                }

                                $active = true;//strtolower($row[$_i['AthStatus']]) == 'active';

                                $issues = count($issues) > 0 ? implode("\n", $issues) : null;

                                $create = ($athlete == null);
                                $needs_to_fail = !$create && ($duplicates == 'fail');
                                if ($needs_to_fail)
                                    throw new CustomBaseException($issues, FailedAthleteImport::ERROR_CODE_DUPLICATE);

                                if ($issues != null)
                                    throw new CustomBaseException($issues , -1);

                                $new = [
                                    'first_name' => Helper::title($first_name),
                                    'last_name' => Helper::title($last_name),
                                    'gender' => $gender,
                                    'dob' => $dob,
                                    'is_us_citizen' => $us_citizen,
                                    'usaigc_no' => $usaigc_no,
                                    'usaigc_level_id' => $level->id,
                                    'usaigc_active' => $active
                                ];

                                $old = [];

                                if (!$create) {
                                    $old = [
                                        'first_name' => $athlete->first_name,
                                        'last_name' => $athlete->last_name,
                                        'gender' => $athlete->gender,
                                        'dob' => $athlete->dob,
                                        'is_us_citizen' => $athlete->is_us_citizen,
                                        'usaigc_no' => $athlete->usaigc_no,
                                        'usaigc_level_id' => $athlete->usaigc_level_id,
                                        'usaigc_active' => $athlete->usaigc_active
                                    ];
                                }

                                $diff = AuditEvent::attributeDiff($old, $new);
                                if (count($diff) < 1) { // No change, skip.
                                    $result['ignored']++;
                                    continue;
                                }

                                if ($create)
                                    $athlete = $this->athletes()->create($new);
                                else
                                    $athlete->update($new);

                                $athlete->save();
                                AuditEvent::athleteImportedCsv(
                                    request()->_managed_account, auth()->user(), $athlete, $body,
                                    $diff, !$create
                                );
                                $result[$create ? 'imported' : 'overwritten']++;
                            } else { // $duplicates == 'ignore'
                                $result['ignored']++;
                                continue;
                            }
                        } catch(\Throwable $e) {
                            $code = FailedAthleteImport::ERROR_CODE_SERVER;

                            if ($e instanceof CustomBaseException) {
                                $code = ($e->getCode() ==  FailedAthleteImport::ERROR_CODE_DUPLICATE ?
                                    FailedAthleteImport::ERROR_CODE_DUPLICATE :
                                    FailedAthleteImport::ERROR_CODE_VALIDATION
                                );
                            }

                            $raw = json_encode($row);
                            if ($raw === false)
                                throw new CustomBaseException('Server error', ErrorCodeCategory::getCategoryBase('General') + 1);

                            $this->failed_athlete_imports()->create([
                                'first_name' => Helper::title($first_name),
                                'last_name' => Helper::title($last_name),
                                'gender' => $gender,
                                'dob' => $dob,
                                'is_us_citizen' => ($us_citizen != null) ? $us_citizen : false,
                                'usaigc_no' => $usaigc_no,
                                'usaigc_level_id' => ($level != null) ? $level->id : null,
                                'usaigc_active' => ($active != null) ? $active : false,
                                'method' => self::IMPORT_METHOD_CSV,
                                'sanctioning_body_id' => $body,
                                'raw' =>  $raw,
                                'error_code' => $code,
                                'error_message' => $e->getMessage()
                            ]);

                            //throw $e;
                            $result['failed']++;
                        }
                    }
                    break;

                default:
                    throw new CustomBaseException('This is not a valid athlete file.', -1);
            }

            return $result;
        } catch(\Throwable $e) {
            if ($handle !== false)
                fclose($handle);
            throw $e;
        }
    }

    private function _importAthletesUSAIGCApi(array &$result, string $duplicates) {
        try {
            if ($this->usaigc_membership == null)
                throw new CustomBaseException('A gym needs to have an IGC membership number to import from USAIGC servers.');

            $usaigcService = resolve(USAIGCService::class); /** @var USAIGCService $usaigcService */
            $imports = $usaigcService->getClub($this->usaigc_membership); // trackthis_1
            foreach ($imports as $import) {
                $new = [
                    'first_name' => null,
                    'last_name' => null,
                    'gender' => null,
                    'dob' => null,
                    'is_us_citizen' => null,
                    'usaigc_no' => null,
                    'usaigc_level_id' => null,
                    'usaigc_active' => null
                ];

                try {
                    $issues = [];
                    $first_name = null;
                    $last_name = null;
                    $dob = null;
                    $gender = null;
                    $us_citizen = null;
                    $usaigc_no = null;
                    $level = null;
                    $active = null;

                    $usaigc_no = trim($import['ATHLETENUMBER']);
                    $a = ['usaigc_no' => $usaigc_no];
                    try {
                        $vv = Validator::make($a, [
                            'usaigc_no' => Athlete::CREATE_RULES['usaigc_no']
                        ])->validate();
                    } catch (ValidationException $ve) {
                        $issues[] = 'Invalid USAIGC number value `' . $import['ATHLETENUMBER'] . '`';
                    }
                    $usaigc_no = $a['usaigc_no'];

                    $athlete = $this->athletes()->where('usaigc_no', $usaigc_no)->first();
                    if (($athlete == null) || ($duplicates == 'overwrite') || ($duplicates == 'fail')) {
                        $first_name = trim($import['FIRSTNAME']);
                        $len = strlen($first_name);
                        if (($len < 1) || ($len > 255))
                            $issues[] = 'Invalid first name value `' . $import['FIRSTNAME'] . '`';

                        $last_name = trim($import['LASTNAME']);
                        $len = strlen($last_name);
                        if (($len < 1) || ($len > 255))
                            $issues[] = 'Invalid last name value `' . $import['LASTNAME'] . '`';


                        // bug solved with checking the date validation .......... START
                        $date_chk = explode("/",$import['DOB']);
                        if((intval($date_chk[0])<=0 || intval($date_chk[0]) >12) || 
                        (intval($date_chk[1])<=0 || intval($date_chk[1]) >31))
                        {
                            $issues[] = 'Invalid date value `' . $import['DOB'] . '`';
                        }
                        else
                        {
                            $dob = \DateTime::createFromFormat(USAIGCService::API_DATE_FORMAT, $import['DOB']);
                            if (($dob === null) || ($dob === false)) {
                                $dob = new \DateTime();
                                $issues[] = 'Invalid date value `' . $import['DOB'] . '`';
                            } else {
                                $dob = $dob->setTime(0, 0);
                            }
                        }
                        // bug solved with checking the date validation .......... END

                        $gender = strtolower($import['GENDER']);
                        if (!in_array($gender, ['male', 'female'])) {
                            $issues[] = 'Invalid gender value `' . $import['GENDER'] . '`';
                        }

                        $us_citizen = strtolower($import['USACITIZEN']);
                        if (!in_array($us_citizen, ['yes', 'no'])) {
                            $issues[] = 'Invalid US Citizen value `' . $import['USACITIZEN'] . '`';
                        }
                        $us_citizen = ($us_citizen == 'yes');

                        $import['COMPLEVEL'] = trim($import['COMPLEVEL']);
                        $import['COMPLEVEL'] = str_replace(' ', '', $import['COMPLEVEL']);
                        $import['COMPLEVEL'] = preg_replace('/([a-z])([A-Z])|([a-zA-Z])(\d)/', '$1$3 $2$4', $import['COMPLEVEL']);
                        $level = AthleteLevel::where('name', $import['COMPLEVEL'])->where('sanctioning_body_id', 2)->first();
                        if ($level == null) {
                            $issues[] = 'Invalid level value `' . $import['COMPLEVEL'] . '`';
                        } else if ($gender != null) {
                            if ((($gender == 'male') && !$level->level_category->male) ||
                            (($gender == 'female') && !$level->level_category->female))
                            $issues[] = 'Invalid Gender / USAIGC Level combination : `' .
                                $import['COMPLEVEL'] . '` and `' . $gender .'`';
                        }

                        $active = true;

                        $issues = count($issues) > 0 ? implode("\n", $issues) : null;

                        $create = ($athlete == null);
                        $needs_to_fail = !$create && ($duplicates == 'fail');
                        if ($needs_to_fail)
                            throw new CustomBaseException($issues, FailedAthleteImport::ERROR_CODE_DUPLICATE);

                        if ($issues != null)
                            throw new CustomBaseException($issues , -1);

                        $new = [
                            'first_name' => Helper::title($first_name),
                            'last_name' => Helper::title($last_name),
                            'gender' => $gender,
                            'dob' => $dob,
                            'is_us_citizen' => $us_citizen,
                            'usaigc_no' => $usaigc_no,
                            'usaigc_level_id' => $level->id,
                            'usaigc_active' => $active
                        ];

                        $old = [];

                        if (!$create) {
                            $old = [
                                'first_name' => $athlete->first_name,
                                'last_name' => $athlete->last_name,
                                'gender' => $athlete->gender,
                                'dob' => $athlete->dob,
                                'is_us_citizen' => $athlete->is_us_citizen,
                                'usaigc_no' => $athlete->usaigc_no,
                                'usaigc_level_id' => $athlete->usaigc_level_id,
                                'usaigc_active' => $athlete->usaigc_active
                            ];
                        }

                        $diff = AuditEvent::attributeDiff($old, $new);
                        if (count($diff) < 1) { // No change, skip.
                            $result['ignored']++;
                            continue;
                        }

                        if ($create)
                            $athlete = $this->athletes()->create($new);
                        else
                            $athlete->update($new);

                        $athlete->save();
                        AuditEvent::athleteImportedApi(
                            request()->_managed_account, auth()->user(), $athlete, SanctioningBody::USAIGC,
                            $diff, !$create
                        );
                        $result[$create ? 'imported' : 'overwritten']++;
                    } else { // $duplicates == 'ignore'
                        $result['ignored']++;
                        continue;
                    }
                } catch(\Throwable $e) {
                    $code = FailedAthleteImport::ERROR_CODE_SERVER;

                    if ($e instanceof CustomBaseException) {
                        $code = ($e->getCode() ==  FailedAthleteImport::ERROR_CODE_DUPLICATE ?
                            FailedAthleteImport::ERROR_CODE_DUPLICATE :
                            FailedAthleteImport::ERROR_CODE_VALIDATION
                        );
                    }

                    $raw = json_encode($import);
                    if ($raw === false)
                        throw new CustomBaseException('Server error', ErrorCodeCategory::getCategoryBase('General') + 1);

                    $this->failed_athlete_imports()->create([
                        'first_name' => Helper::title($first_name),
                        'last_name' => Helper::title($last_name),
                        'gender' => $gender,
                        'dob' => $dob,
                        'is_us_citizen' => ($us_citizen != null) ? $us_citizen : false,
                        'usaigc_no' => $usaigc_no,
                        'usaigc_level_id' => ($level != null) ? $level->id : null,
                        'usaigc_active' => ($active != null) ? $active : false,
                        'method' => self::IMPORT_METHOD_API,
                        'sanctioning_body_id' => SanctioningBody::USAIGC,
                        'raw' =>  $raw,
                        'error_code' => $code,
                        'error_message' => $e->getMessage()
                    ]);

                    //throw $e;
                    $result['failed']++;
                }
            }
            return $result;
        } catch(\Throwable $e) {
            throw $e;
        }
    }

    private function _detectAthleteCSVFileSource(array $headers)
    {
        $hasAll = true;
        foreach (self::_USAIGC_ATHLETE_FILEDS as $field) {
            if (!in_array($field, $headers)) {
                $hasAll = false;
                break;
            }
        }
        if ($hasAll)
            return SanctioningBody::USAIGC;

        $hasAll = true;
        foreach (self::_USAG_ATHLETE_FIELDS as $field) {
            if (!in_array($field, $headers)) {
                $hasAll = false;
                break;
            }
        }
        if ($hasAll)
            return SanctioningBody::USAG;

        // none of the above
        return null;
    }

    public function createCoach(array $attr)
    {
        DB::beginTransaction();

        try {

            $dob = new \DateTime($attr['dob']);
            $tshirtSize = null;

            /*if ($dob > now())
                throw new CustomBaseException('Invalid birth date.', '-1');*/

            if (isset($attr['tshirt_size_id'])) {
                $tshirtSize = ClothingSize::find($attr['tshirt_size_id']);
                if (($tshirtSize == null) || $tshirtSize->chart->is_leo)
                    throw new CustomBaseException('No such T-Shirt size.', '69');
                $tshirtSize= $tshirtSize->id;
            }

            $coach = [
                'first_name' => Helper::title($attr['first_name']),
                'last_name' => Helper::title($attr['last_name']),
                'gender' => $attr['gender'],
                'dob' => $dob,
                'tshirt_size_id' => $tshirtSize,
            ];

            if (isset($attr['usag_no'])) {
                $duplicate = $this->coaches()->where('usag_no', $attr['usag_no'])->first();
                if ($duplicate !== null)
                    throw new CustomBaseException('There is already a coach with USAG No ' .
                        $attr['usag_no'] . ' in this gym.', '-1');

                $coach += [
                    'usag_no' => $attr['usag_no'],
                    'usag_active' => isset($attr['usag_active']),
                    'usag_expiry' => isset($attr['usag_expiry']) ? new \DateTime($attr['usag_expiry']) : null,
                    'usag_safety_expiry' => isset($attr['usag_safety_expiry']) ? new \DateTime($attr['usag_safety_expiry']) : null,
                    'usag_safesport_expiry' => isset($attr['usag_safesport_expiry']) ? new \DateTime($attr['usag_safesport_expiry']) : null,
                    'usag_background_expiry' => isset($attr['usag_background_expiry']) ? new \DateTime($attr['usag_background_expiry']) : null,
                    'usag_u100_certification' => isset($attr['usag_u100_certification'])
                ];
            }
            // if (isset($attr['usaigc_active'])) {
            //     $coach += [
            //         'usaigc_active' => $attr['usaigc_active']
            //     ];
            // }
            if (isset($attr['usaigc_no'])) {
                // if(trim($attr['usaigc_no']) != $this->usaigc_membership)
                //     throw new CustomBaseException('USAIGC No should be same as club sanction');
                 // $duplicate = $this->coaches()->where('usaigc_no', $attr['usaigc_no'])->first();
                // if ($duplicate !== null)
                //     throw new CustomBaseException('There is already a coach with USAIGC No ' .
                //         $attr['usaigc_no'] . ' in this gym.', '-1');
                // $attr['usaigc_no'] .= '-' . rand(100000,999999);
                $coach += [
                    'usaigc_no' => $attr['usaigc_no'],
                    'usaigc_background_check' => isset($attr['usaigc_background_check'])
                ];
            }

            if (isset($attr['aau_no'])) {
                $duplicate = $this->coaches()->where('aau_no', $attr['aau_no'])->first();
                if ($duplicate !== null)
                    throw new CustomBaseException('There is already a coach with AAU No ' .
                        $attr['aau_no'] . ' in this gym.', '-1');

                $coach += [
                    'aau_no' => $attr['aau_no']
                ];
            }

            if (isset($attr['nga_no'])) {
                $duplicate = $this->coaches()->where('nga_no', $attr['nga_no'])->first();
                if ($duplicate !== null)
                    throw new CustomBaseException('There is already a coach with NGA No ' .
                        $attr['nga_no'] . ' in this gym.', '-1');

                $coach += [
                    'nga_no' => $attr['nga_no']
                ];
            }

            $coach = $this->coaches()->create($coach);
            AuditEvent::coachCreated(request()->_managed_account, auth()->user(), $coach);
            DB::commit();
            return $coach;
        } catch(\Throwable $e) {
            DB::rollBack();
            Log::channel('slack-warning')->warning($e);
            throw $e;
        }
    }

    public function removeCoach(string $coach) {
        DB::beginTransaction();

        try {
            $coach = $this->coaches()->find($coach);
            if ($coach == null)
                throw new CustomBaseException('No such coach.', '-1');

            AuditEvent::coachRemoved(request()->_managed_account, auth()->user(), $coach);
            $coach->delete();
            DB::commit();
            return;
        } catch(\Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function removeCoachBatch(string $coaches) {
        $coaches = explode(',', $coaches);
        if (($coaches === false) || (count($coaches) < 1))
            throw new CustomBaseException('No coaches were selected.', '-1');

        DB::beginTransaction();
        try {
            foreach ($coaches as $coache) {
                $coache = $this->coaches()->find($coache);
                if ($coache == null)
                    throw new CustomBaseException('No such coache.', '-1');

                AuditEvent::coachRemoved(request()->_managed_account, auth()->user(), $coache);
                $coache->delete();
            }
            DB::commit();
            return true;
        } catch(\Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function importCoaches(string $method, string $duplicates, ?string $body = null, 
        UploadedFile $csvFile = null, string $delimiter = ',')
    {
        $result = [
            'imported' => 0,
            'failed' => 0,
            'ignored' => 0,
            'overwritten' => 0,
        ];

        DB::beginTransaction();

        try {
            switch ($method) {
                case 'api':{
                    switch ($body){
                        case 'nga':
                            $this->_importCoachesNGA($result, $duplicates);
                            break;
                        default:
                            $this->_importCoachesUSAIGCApi($result, $duplicates);
                            break;
                    }
                }
                break;
                // throw new CustomBaseException('USAIGC server import is disabled.', -1);
                // break;
                default:
                    $this->_importCoachesCSV($result, $csvFile, $duplicates, $delimiter);
            }

            DB::commit();
            return $result;
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }

        return $result;
    }
    private function _importCoachesUSAIGCApi(array &$result, string $duplicates)
    {
        try{
            if ($this->usaigc_membership == null)
                throw new CustomBaseException('A gym needs to have an IGC membership number to import from USAIGC servers.');
            $usaigcService = resolve(USAIGCService::class); /** @var USAIGCService $usaigcService */
            $imports = $usaigcService->getCoach($this->usaigc_membership); // trackthis_1
            foreach ($imports as $row) {
                $new = [
                    'first_name' => null,
                    'last_name' => null,
                    'gender' => null,
                    'dob' => null,
                    'usaigc_no' => null,
                ];
                try {
                    $issues = [];
                    $first_name = null;
                    $last_name = null;
                    $dob = null;
                    $gender = null;
                    $usaigc_no = null;
                    $active = null;

                    $usaigc_no = trim($row['COACHID']);
                    $a = ['usaigc_no' => $usaigc_no];
                    $coach = $this->coaches()->where('usaigc_no', $usaigc_no)->first();

                    if (($coach == null) || ($duplicates == 'overwrite') || ($duplicates == 'fail')) {
                        $first_name = trim($row['FIRSTNAME']);
                        $len = strlen($first_name);
                        if (($len < 1) || ($len > 255))
                            $issues[] = 'Invalid first name value `' . $row['FIRSTNAME'] . '`';

                        $last_name = trim($row['LASTNAME']);
                        $len = strlen($last_name);
                        if (($len < 1) || ($len > 255))
                            $issues[] = 'Invalid last name value `' . $row['LASTNAME'] . '`';

                        
                        $gender = 'n'; //gender is not available from usaigc
                        if (!in_array($gender, ['m', 'f'])) {
                            $gender = 'N/A';
                            $issues[] = 'Invalid gender value';
                        }
                        if (($dob === null) || ($dob === false)) {
                            $dob = new \DateTime();
                            $issues[] = 'Invalid DOB value ';
                        }
                        
                        $active =  $row['STATUS'] == 'Active' ? True : False;
                        
                        $issues = count($issues) > 0 ? implode("\n", $issues) : null;
                        $create = ($coach == null);
                        $needs_to_fail = !$create && ($duplicates == 'fail');
                        if ($needs_to_fail)
                            throw new CustomBaseException($issues, FailedCoachImport::ERROR_CODE_DUPLICATE);
                        

                        if ($issues != null)
                            throw new CustomBaseException($issues , -1);
                            
                        $new = [
                            'first_name' => Helper::title($first_name),
                            'last_name' => Helper::title($last_name),
                            'gender' => $gender,
                            'dob' => $dob,
                            'usaigc_no' => $usaigc_no
                        ];

                        $old = [];

                        if (!$create) {
                            $old = [
                                'first_name' => $coach->first_name,
                                'last_name' => $coach->last_name,
                                'gender' => $coach->gender,
                                'dob' => $coach->dob,
                                'usaigc_no' => $coach->usaigc_no
                                // 'nga_no' => 'N'.filter_var($coach->nga_no, FILTER_SANITIZE_NUMBER_INT)
                            ];
                        }
                        $diff = AuditEvent::attributeDiff($old, $new);

                        if (count($diff) < 1) { // No change, skip.
                            $result['ignored']++;
                            continue;
                        }
                        
                        if ($create)
                        {
                            $coach = $this->coaches()->create($new);
                        }
                        else
                            $coach->update($new);

                        $coach->save();
                        AuditEvent::coachImportedCsv(
                            request()->_managed_account, auth()->user(), $coach, SanctioningBody::NGA,
                            $diff, !$create
                        );
                        $result[$create ? 'imported' : 'overwritten']++;
                    } else { // $duplicates == 'ignore'
                        $result['ignored']++;
                        continue;
                    }
                } catch(\Throwable $e) {
                    $code = FailedCoachImport::ERROR_CODE_SERVER;

                    if ($e instanceof CustomBaseException) {
                        $code = (
                            $e->getCode() ==  FailedCoachImport::ERROR_CODE_DUPLICATE ?
                            FailedCoachImport::ERROR_CODE_DUPLICATE :
                            $code = FailedCoachImport::ERROR_CODE_VALIDATION
                        );
                    }

                    $raw = json_encode($row);
                    if ($raw === false)
                        throw new CustomBaseException('Server error', ErrorCodeCategory::getCategoryBase('General') + 1);
                    if (($dob === null) || ($dob === false)) {
                        $dob = new \DateTime();
                        $issues[] = 'Invalid DOB value ';
                    }
                    $this->failed_coach_imports()->create([
                        'first_name' => Helper::title($first_name),
                        'last_name' => Helper::title($last_name),
                        'gender' => $gender,
                        'dob' => $dob,
                        'usaigc_no' => $usaigc_no,
                        'method' => self::IMPORT_METHOD_API,
                        'sanctioning_body_id' => SanctioningBody::USAIGC,
                        'raw' =>  $raw,
                        'error_code' => $code,
                        'error_message' => $e->getMessage()
                    ]);

                    //throw $e;
                    $result['failed']++;
                }
            }
        }catch(\Throwable $e)
        {
            throw $e;
        }
    }
    private function _importCoachesNGA(array &$result, string $duplicates) //ic
    {
        try {
            
            if ($this->nga_membership == null)
                throw new CustomBaseException('A gym needs to have an NGA membership number to import from NGA servers.');

            $ngaService = resolve(NGAService::class); /** @var NGAService $ngaService */
            $imports = $ngaService->getCoach($this->nga_membership);

            foreach ($imports as $row) {
                $new = [
                    'first_name' => null,
                    'last_name' => null,
                    'gender' => null,
                    'dob' => null,
                    'nga_no' => null
                ];

                try {
                    $issues = [];
                    $first_name = null;
                    $last_name = null;
                    $dob = null;
                    $gender = null;
                    $nga_no = null;
                    $active = null;

                    $nga_no = trim($row['NGANumber']);
                    $a = ['nga_no' => $nga_no];
                    try {
                        $vv = Validator::make($a, [
                            'nga_no' => Athlete::CREATE_RULES['nga_no']
                        ])->validate();
                    } catch (ValidationException $ve) {
                        $issues[] = 'Invalid NGA number value `' . $row['NGANumber'] . '`';
                    }

                    $nga_no_with_n = 'NGA'.$nga_no;
                    $nga_no_with_ns = 'Nga'.$nga_no;
                    $nga_no_with_nsn = substr($nga_no,1);
                    $coach = $this->coaches()->where('nga_no', $nga_no)
                    ->orWhere('nga_no', $nga_no_with_n)
                    ->orWhere('nga_no', $nga_no_with_ns)
                    ->orWhere('nga_no', $nga_no_with_nsn)
                    ->first();

                    if (($coach == null) || ($duplicates == 'overwrite') || ($duplicates == 'fail')) {
                        $first_name = trim($row['FirstName']);
                        $len = strlen($first_name);
                        if (($len < 1) || ($len > 255))
                            $issues[] = 'Invalid first name value `' . $row['FirstName'] . '`';

                        $last_name = trim($row['LastName']);
                        $len = strlen($last_name);
                        if (($len < 1) || ($len > 255))
                            $issues[] = 'Invalid last name value `' . $row['LastName'] . '`';
                        

                        $dob = \DateTime::createFromFormat('n/j/Y', $row['DOB']);
                        if (($dob === null) || ($dob === false)) {
                            $dob = new \DateTime();
                            $issues[] = 'Invalid date value `' . $row['DOB'] . '`';
                        } else {
                            $dob = $dob->setTime(0, 0);
                        }

                        
                        $gender = strtolower($row['Gender']);
                        if (!in_array($gender, ['m', 'f'])) {
                            $gender = 'N/A';
                            $issues[] = 'Invalid gender value `' . $row['Gender'] . '`';
                        }
                        else
                            $gender = $gender == 'm' ? "male" : "female";


                        $active =  true;
                        
                        $issues = count($issues) > 0 ? implode("\n", $issues) : null;
                        $create = ($coach == null);
                        $needs_to_fail = !$create && ($duplicates == 'fail');
                        if ($needs_to_fail)
                            throw new CustomBaseException($issues, FailedCoachImport::ERROR_CODE_DUPLICATE);
                        

                        if ($issues != null)
                            throw new CustomBaseException($issues , -1);
                            
                        $new = [
                            'first_name' => Helper::title($first_name),
                            'last_name' => Helper::title($last_name),
                            'gender' => $gender,
                            'dob' => $dob,
                            'nga_no' => $nga_no
                        ];

                        $old = [];

                        if (!$create) {
                            $old = [
                                'first_name' => $coach->first_name,
                                'last_name' => $coach->last_name,
                                'gender' => $coach->gender,
                                'dob' => $coach->dob,
                                'nga_no' => 'N'.filter_var($coach->nga_no, FILTER_SANITIZE_NUMBER_INT)
                            ];
                        }
                        $diff = AuditEvent::attributeDiff($old, $new);

                        if (count($diff) < 1) { // No change, skip.
                            $result['ignored']++;
                            continue;
                        }
                        
                        if ($create)
                        {
                            $coach = $this->coaches()->create($new);
                        }
                        else
                            $coach->update($new);

                        $coach->save();
                        AuditEvent::coachImportedCsv(
                            request()->_managed_account, auth()->user(), $coach, SanctioningBody::NGA,
                            $diff, !$create
                        );
                        $result[$create ? 'imported' : 'overwritten']++;
                    } else { // $duplicates == 'ignore'
                        $result['ignored']++;
                        continue;
                    }
                    
                } catch(\Throwable $e) {
                    $code = FailedCoachImport::ERROR_CODE_SERVER;

                    if ($e instanceof CustomBaseException) {
                        $code = (
                            $e->getCode() ==  FailedCoachImport::ERROR_CODE_DUPLICATE ?
                            FailedCoachImport::ERROR_CODE_DUPLICATE :
                            $code = FailedCoachImport::ERROR_CODE_VALIDATION
                        );
                    }

                    $raw = json_encode($row);
                    if ($raw === false)
                        throw new CustomBaseException('Server error', ErrorCodeCategory::getCategoryBase('General') + 1);

                    $this->failed_coach_imports()->create([
                        'first_name' => Helper::title($first_name),
                        'last_name' => Helper::title($last_name),
                        'gender' => $gender,
                        'dob' => $dob,
                        'nga_no' => $nga_no,
                        'method' => self::IMPORT_METHOD_API,
                        'sanctioning_body_id' => SanctioningBody::NGA,
                        'raw' =>  $raw,
                        'error_code' => $code,
                        'error_message' => $e->getMessage()
                    ]);

                    //throw $e;
                    $result['failed']++;
                }
            }

        } catch(\Throwable $e) {
            throw $e;
        }
    }

    private function _importCoachesCSV(array &$result, UploadedFile $csvFile, string $duplicates,
        string $delimiter) {
        $handle = false;
        try {

            if (!in_array($delimiter, [',', ';']))
                throw new CustomBaseException('Invalid delimiter.', -1);

            $content = file($csvFile->getRealPath(), FILE_IGNORE_NEW_LINES);
            if ($content === false)
                throw new CustomBaseException('A server error occured.', ErrorCodeCategory::getCategoryBase('General') + 4);

            $rows = array_map(function ($item) use ($delimiter) {
                return str_getcsv($item, $delimiter);
            }, $content);

            $headers = array_shift($rows);
            if ($headers === null)
                throw new CustomBaseException('This is not a valid coach file from USAG / USAIGC.', -1);

            // Dynamically store column name indexes so if the imported file spcification changes,
            // we don't have to change hardcoded indexes.
            $_i = [];
            foreach ($headers as $index => $value)
                $_i[$value] = $index;

            $body = $this->_detectCoachVFileSource($headers);
            switch ($body) {
                case SanctioningBody::USAG:
                    foreach ($rows as $row) {
                        if ($row[$_i['ProType']] != 'Professional')
                            continue;

                        $new = [
                            'first_name' => null,
                            'last_name' => null,
                            'gender' => null,
                            'dob' => null,
                            'usag_no' => null,
                            'usag_active' => null
                        ];

                        try {
                            $issues = [];
                            $first_name = null;
                            $last_name = null;
                            $dob = null;
                            $gender = null;
                            $usag_no = null;
                            $active = null;

                            $usag_expiry = null;
                            $usag_safety_expiry = null;
                            $usag_safesport_expiry = null;
                            $usag_background_expiry = null;
                            $usag_u100_certification = false;

                            $lacks_dob = !in_array('DOB', $headers);
                            $lacks_gender = !in_array('Gender', $headers);

                            $usag_no = trim($row[$_i['PersonID']]);
                            $a = ['usag_no' => $usag_no];
                            try {
                                $vv = Validator::make($a, [
                                    'usag_no' => Athlete::CREATE_RULES['usag_no']
                                ])->validate();
                            } catch (ValidationException $ve) {
                                $issues[] = 'Invalid USAG number value `' . $row[$_i['PersonID']] . '`';
                            }
                            $usag_no = $a['usag_no'];

                            $coach = $this->coaches()->where('usag_no', $usag_no)->first();
                            if (($coach == null) || ($duplicates == 'overwrite') || ($duplicates == 'fail')) {
                                $first_name = trim($row[$_i['FirstName']]);
                                $len = strlen($first_name);
                                if (($len < 1) || ($len > 255))
                                    $issues[] = 'Invalid first name value `' . $row[$_i['FirstName']] . '`';

                                $last_name = trim($row[$_i['LastName']]);
                                $len = strlen($last_name);
                                if (($len < 1) || ($len > 255))
                                    $issues[] = 'Invalid last name value `' . $row[$_i['LastName']] . '`';

                                if ($lacks_dob) {
                                    $dob = new \DateTime();
                                    $issues[] = 'No date row provided';
                                } else {
                                    $dob = \DateTime::createFromFormat('n/j/Y', $row[$_i['DOB']]);
                                    if (($dob === null) || ($dob === false)) {
                                        $dob = new \DateTime();
                                        $issues[] = 'Invalid date value `' . $row[$_i['DOB']] . '`';
                                    } else {
                                        $dob = $dob->setTime(0, 0);
                                    }
                                }

                                if ($lacks_gender) {
                                    $gender = 'N/A';
                                    $issues[] = 'No gender row provided';
                                } else {
                                    $gender = strtolower($row[$_i['Gender']]);
                                    if (!in_array($gender, ['male', 'female'])) {
                                        $gender = 'N/A';
                                        $issues[] = 'Invalid gender value `' . $row[$_i['Gender']] . '`';
                                    }
                                }

                                if (isset($row[$_i['ProExpiration']]) && ($row[$_i['ProExpiration']] != '')) {
                                    $usag_expiry = \DateTime::createFromFormat('n/j/Y', $row[$_i['ProExpiration']]);
                                    if ($usag_expiry === false)
                                        $issues[] = 'Invalid Professional No. expiry date value `' . $row[$_i['ProExpiration']] . '`';
                                    else
                                        $usag_expiry->setTime(0, 0);
                                }

                                if (isset($row[$_i['Safety']]) && ($row[$_i['Safety']] != '')) {
                                    $usag_safety_expiry = \DateTime::createFromFormat('n/j/Y', $row[$_i['Safety']]);
                                    if ($usag_safety_expiry === false)
                                        $issues[] = 'Invalid Safety Certification expiry date value `' . $row[$_i['Safety']] . '`';
                                    else
                                        $usag_safety_expiry->setTime(0, 0);
                                }

                                /*
                                if (isset($row[$_i['SafeSport']]) && ($row[$_i['SafeSport']] != '')) {
                                    $usag_safesport_expiry = \DateTime::createFromFormat('n/j/Y', $row[$_i['SafeSport']]);
                                    if ($usag_safesport_expiry === false)
                                        $issues[] = 'Invalid SafeSport expiry date value `' . $row[$_i['SafeSport']] . '`';
                                    else
                                        $usag_safesport_expiry->->setTime(0, 0);
                                }
                                */

                                if (isset($row[$_i['Background']]) && ($row[$_i['Background']] != '')) {
                                    $usag_background_expiry = \DateTime::createFromFormat('n/j/Y', $row[$_i['Background']]);
                                    if ($usag_background_expiry === false)
                                        $issues[] = 'Invalid Background expiry date value `' . $row[$_i['Background']] . '`';
                                    else
                                        $usag_background_expiry->setTime(0, 0);
                                }

                                $usag_u100_certification = $row[$_i['U100']] == 'Yes';

                                $active =  strtolower($row[$_i['ProStatus']]) == 'active';

                                $issues = count($issues) > 0 ? implode("\n", $issues) : null;

                                $create = ($coach == null);
                                $needs_to_fail = !$create && ($duplicates == 'fail');
                                if ($needs_to_fail)
                                    throw new CustomBaseException($issues, FailedCoachImport::ERROR_CODE_DUPLICATE);

                                if ($issues != null)
                                    throw new CustomBaseException($issues , -1);

                                $new = [
                                    'first_name' => Helper::title($first_name),
                                    'last_name' => Helper::title($last_name),
                                    'gender' => $gender,
                                    'dob' => $dob,
                                    'usag_no' => $usag_no,
                                    'usag_active' => $active,
                                    'usag_expiry' => $usag_expiry,
                                    'usag_safety_expiry' =>  $usag_safety_expiry,
                                    'usag_safesport_expiry' =>  $usag_safesport_expiry,
                                    'usag_background_expiry' => $usag_background_expiry,
                                    'usag_u100_certification' => $usag_u100_certification,
                                ];

                                $old = [];

                                if (!$create) {
                                    $old = [
                                        'first_name' => $coach->first_name,
                                        'last_name' => $coach->last_name,
                                        'gender' => $coach->gender,
                                        'dob' => $coach->dob,
                                        'usag_no' => $coach->usag_no,
                                        'usag_active' => $coach->usag_active,
                                        'usag_expiry' => $coach->usag_expiry,
                                        'usag_safety_expiry' =>  $coach->usag_safety_expiry,
                                        'usag_safesport_expiry' =>  $coach->usag_safesport_expiry,
                                        'usag_background_expiry' => $coach->usag_background_expiry,
                                        'usag_u100_certification' => $coach->usag_u100_certification,
                                    ];
                                }

                                $diff = AuditEvent::attributeDiff($old, $new);
                                if (count($diff) < 1) { // No change, skip.
                                    $result['ignored']++;
                                    continue;
                                }

                                if ($create)
                                    $coach = $this->coaches()->create($new);
                                else
                                    $coach->update($new);

                                $coach->save();
                                AuditEvent::coachImportedCsv(
                                    request()->_managed_account, auth()->user(), $coach, $body,
                                    $diff, !$create
                                );
                                $result[$create ? 'imported' : 'overwritten']++;
                            } else { // $duplicates == 'ignore'
                                $result['ignored']++;
                                continue;
                            }
                        } catch(\Throwable $e) {
                            $code = FailedCoachImport::ERROR_CODE_SERVER;

                            if ($e instanceof CustomBaseException) {
                                $code = (
                                    $e->getCode() ==  FailedCoachImport::ERROR_CODE_DUPLICATE ?
                                    FailedCoachImport::ERROR_CODE_DUPLICATE :
                                    $code = FailedCoachImport::ERROR_CODE_VALIDATION
                                );
                            }

                            $raw = json_encode($row);
                            if ($raw === false)
                                throw new CustomBaseException('Server error', ErrorCodeCategory::getCategoryBase('General') + 1);

                            $this->failed_coach_imports()->create([
                                'first_name' => Helper::title($first_name),
                                'last_name' => Helper::title($last_name),
                                'gender' => $gender,
                                'dob' => $dob,
                                'usag_no' => $usag_no,
                                'usag_active' => ($active != null) ? $active : false,
                                'method' => self::IMPORT_METHOD_CSV,
                                'sanctioning_body_id' => $body,
                                'raw' =>  $raw,
                                'error_code' => $code,
                                'error_message' => $e->getMessage()
                            ]);

                            //throw $e;
                            $result['failed']++;
                        }
                    }
                    break;

                case SanctioningBody::USAIGC:
                    throw new CustomBaseException('USAIGC import is disabled.', -1);
                    break;

                default:
                    throw new CustomBaseException('This is not a valid coach file from USAG / USAIGC.', -1);
            }

            return $result;
        } catch(\Throwable $e) {
            if ($handle !== false)
                fclose($handle);
            throw $e;
        }
    }

    private function _detectCoachVFileSource(array $headers)
    {
        $hasAll = true;
        foreach (self::_USAG_COACH_FIELDS as $field) {
            if (!in_array($field, $headers)) {
                $hasAll = false;
                break;
            }
        }
        if ($hasAll)
            return SanctioningBody::USAG;

        // none of the above
        return null;
    }

    public function removeFailedCoachImport(string $coach) {
        DB::beginTransaction();

        try {
            $coach = $this->failed_coach_imports()->find($coach);
            if ($coach == null)
                throw new CustomBaseException('No such entry.', '-1');

            //No need to register audit event.
            $coach->delete();
            DB::commit();
            return;
        } catch(\Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function removeFailedCoachBatch(string $coaches) {
        $coaches = explode(',', $coaches);
        if (($coaches === false) || (count($coaches) < 1))
            throw new CustomBaseException('No entries were selected.', '-1');

        DB::beginTransaction();
        try {
            foreach ($coaches as $coach) {
                $coach = $this->failed_coach_imports()->find($coach);
                if ($coach == null)
                    throw new CustomBaseException('No such entry.', '-1');

                //No need to register audit event.
                $coach->delete();
            }
            DB::commit();
            return true;
        } catch(\Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function createCoachFromFailedImport(array $attr, FailedCoachImport $failedImport) {
        DB::beginTransaction();

        try {
            $athlete = $this->createCoach($attr);
            $failedImport->delete();
            DB::commit();
            return $athlete;
        } catch(\Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function retrieveMeet(string $id, bool $archived = false) : Meet
    {
        $meet = $this->meets()->where('id', $id)->first();
        if ($meet == null)
            throw new CustomBaseException(
                'There is no such meet in ' . ($this->isCurrentUser() ? 'your' : $this->fullName() . '\'s') . ' account' ,
                -1
            );

        //if (!$archived && $meet->is_archived)
        //    throw new CustomBaseException('You cannot edit archived meets', -1);

        return $meet;
    }

    public function removeMeet(string $meet) {
        DB::beginTransaction();

        try {
            $meet = $this->meets()->find($meet); /** @var Meet $meet */
            if ($meet == null)
                throw new CustomBaseException('No such meet.', '-1');

            if (!$meet->canBeDeleted())
                throw new CustomBaseException('This meet cannot be deleted because it has registrations.', '-1');

            AuditEvent::meetRemoved(request()->_managed_account, auth()->user(), $meet);
            $meet->delete();
            DB::commit();
            return;
        } catch(\Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }
    public function getCoachesFromMeetRegistrations($meetID)
    {
        $query = 'select c.id,c.first_name,c.last_name,c.usag_no,c.aau_no,c.usaigc_no,c.nga_no 
        from meet_registrations as mr 
        join gyms on mr.gym_id = gyms.id
        join registration_coaches as c on c.meet_registration_id = mr.id
        where c.status = 1 and gyms.id = '.$this->id.' and meet_id = '.$meetID;
        $result = DB::select($query);
        return $result;
    }
    public function getUSAIGCCoachesFromMeetRegistrations($meetID)
    {
        $query = 'select c.id,c.first_name,c.last_name,c.usag_no,c.aau_no,c.usaigc_no,c.nga_no 
        from meet_registrations as mr 
        join gyms on mr.gym_id = gyms.id
        join registration_coaches as c on c.meet_registration_id = mr.id
        where c.status = 1 and c.usaigc_active = true and gyms.id = '.$this->id.' and meet_id = '.$meetID;
        $result = DB::select($query);
        return $result;
    }
    public function copyFromMeet(array $attr) {
        DB::beginTransaction();
        try {

            $meet = $this->meets()->find($attr['meet']);  /** @var Meet $meet */
            if ($meet == null)
                throw new CustomBaseException('No such meet.', -1);

            $attrCount = 0;
            $general = false;
            $venue = false;
            $registration = false;
            $payment = false;
            $categories = false;
            $contact = false;

            if (isset($attr['general'])) {
                $general = $attr['general'];
                $attrCount++;
            }

            if (isset($attr['venue'])) {
                $venue = $attr['venue'];
                $attrCount++;
            }

            if (isset($attr['registration'])) {
                $registration = $attr['registration'];
                $attrCount++;
            }

            if (isset($attr['payment'])) {
                $payment = $attr['payment'];
                $attrCount++;
            }

            if (isset($attr['categories'])) {
                $categories = $attr['categories'];
                $attrCount++;
            }

            if (isset($attr['contact'])) {
                $contact = $attr['contact'];
                $attrCount++;
            }

            if ($attrCount < 1)
                throw new CustomBaseException('Please select at least one attribute to copy.', -1);

            $tm = $this->temporary_meets()->create([]);
            if ($general) {
                $admissions = [];
                foreach ($meet->admissions as $admission) {
                    $admissions[] = [
                        'name' => $admission->name,
                        'type' => $admission->type,
                        'amount' => $admission->amount,
                    ];
                }

                $admissions = json_encode($admissions);
                if ($admissions === false)
                    throw new CustomBaseException('Server error', ErrorCodeCategory::getCategoryBase('General') + 1);

                $tm->update([
                    'name' => $meet->name,
                    'description' => $meet->description,
                    'start_date' => $meet->start_date,
                    'end_date' => $meet->end_date,
                    'website' => $meet->website,
                    'equipement' => $meet->equipement,
                    'notes' => $meet->notes,
                    'special_annoucements' => $meet->special_annoucements,
                    'tshirt_size_chart_id' => ($meet->tshirt_chart != null ? $meet->tshirt_chart->id : null),
                    'leo_size_chart_id' => ($meet->leo_chart != null ? $meet->leo_chart->id : null),
                    'mso_meet_id' => $meet->mso_meet_id,
                    'admissions' => $admissions,
                ]);
            }

            if ($venue) {
                $tm->update([
                    'venue_name' => $meet->venue_name,
                    'venue_addr_1' => $meet->venue_addr_1,
                    'venue_addr_2' => $meet->venue_addr_2,
                    'venue_city' => $meet->venue_city,
                    'venue_state_id' => $meet->venue_state->id,
                    'venue_zipcode' => $meet->venue_zipcode,
                    'venue_website' => $meet->venue_website,
                ]);
            }

            if ($registration) {
                $tm->update([
                    'registration_start_date' => $meet->registration_start_date,
                    'registration_end_date' => $meet->registration_end_date,
                    'registration_scratch_end_date' => $meet->registration_scratch_end_date,
                    'allow_late_registration' => $meet->allow_late_registration,
                    'late_registration_fee' => $meet->late_registration_fee,
                    'late_registration_start_date' => $meet->late_registration_start_date,
                    'late_registration_end_date' => $meet->late_registration_end_date,
                    'athlete_limit' => $meet->athlete_limit,
                ]);
            }

            if ($payment) {
                $tm->update([
                    'accept_paypal' => $meet->accept_paypal,
                    'accept_ach' => $meet->accept_ach,
                    'accept_mailed_check' => $meet->accept_mailed_check,
                    'mailed_check_instructions' => $meet->mailed_check_instructions,
                    'defer_handling_fees' => $meet->defer_handling_fees,
                    'defer_processor_fees' => $meet->defer_processor_fees,
                ]);
            }

            if ($categories) {
                $categories = [];
                foreach ($meet->categories as $category) { /** @var LevelCategory $category */
                    $categories[] = [
                        'id' => $category->id,
                        'body_id' => $category->pivot->sanctioning_body->id,
                        'sanction' => null,
                        'officially_sanctioned' => false,
                    ];
                }

                $categories = json_encode($categories);
                if ($categories === false)
                    throw new CustomBaseException('Server error', ErrorCodeCategory::getCategoryBase('General') + 1);

                $levels = [];
                foreach ($meet->activeLevels as $level) { /** @var AthleteLevel $level */

                    if (LevelCategory::requiresSanction($level->sanctioning_body_id))
                        continue;

                    $levels[] = [
                        'id' => $level->id,
                        'male' => $level->pivot->allow_men,
                        'female' => $level->pivot->allow_women,
                        'registration_fee' => $level->pivot->registration_fee,
                        'late_registration_fee' => $level->pivot->late_registration_fee,
                        'allow_specialist' => $level->pivot->allow_specialist,
                        'specialist_registration_fee' => $level->pivot->specialist_registration_fee,
                        'specialist_late_registration_fee' => $level->pivot->specialist_late_registration_fee,
                        'allow_team' => $level->pivot->allow_teams,
                        'team_registration_fee' => $level->pivot->team_registration_fee,
                        'team_late_registration_fee' => $level->pivot->team_late_registration_fee,
                        'enable_athlete_limit' => $level->pivot->enable_athlete_limit,
                        'athlete_limit' => $level->pivot->athlete_limit,
                    ];
                }

                $levels = json_encode($levels);
                if ($levels === false)
                    throw new CustomBaseException('Server error', ErrorCodeCategory::getCategoryBase('General') + 1);

                $tm->update([
                    'categories' => $categories,
                    'meet_competition_format_id' => $meet->competition_format->id,
                    'meet_competition_format_other' => $meet->meet_competition_format_other,
                    'team_format' => $meet->team_format,
                    'levels' => $levels,
                ]);
            }

            if ($contact) {
                $tm->update([
                    'primary_contact_first_name' => $meet->primary_contact_first_name,
                    'primary_contact_last_name' => $meet->primary_contact_last_name,
                    'primary_contact_email' => $meet->primary_contact_email,
                    'primary_contact_phone' => $meet->primary_contact_phone,
                    'primary_contact_fax' => $meet->primary_contact_fax,

                    'secondary_contact' => $meet->secondary_contact,
                    'secondary_contact_first_name' => $meet->secondary_contact_first_name,
                    'secondary_contact_last_name' => $meet->secondary_contact_last_name,
                    'secondary_contact_email' => $meet->secondary_contact_email,
                    'secondary_contact_job_title' => $meet->secondary_contact_job_title,
                    'secondary_contact_phone' => $meet->secondary_contact_phone,
                    'secondary_contact_fax' => $meet->secondary_contact_fax,
                    'secondary_cc' => $meet->secondary_cc,
                ]);
            }

            $tm->step = 1;
            $tm->save();
            DB::commit();
            return $tm;
        } catch(\Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }
}