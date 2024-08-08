<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use App\Exceptions\CustomBaseException;
use App\Helper;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class TemporaryMeet extends Model
{
    protected $guarded = ['id'];

    protected $dates = [
        'start_date', 'end_date', 'registration_start_date', 'registration_end_date',
        'registration_scratch_end_date', 'late_registration_start_date',
        'late_registration_end_date'
    ];

    public const CREATE_STEP_1_RULES = [
        'name' => ['required', 'string', 'max:255'],
        'description' => ['required', 'string'],
        'start_date' => ['required', 'date_format:m/d/Y', 'after:today'],
        'end_date' => ['required', 'date_format:m/d/Y', 'after_or_equal:start_date'],
        'website' => ['required', 'url', 'max:255'],
        'equipement' => ['required', 'string'],
        'notes' => ['nullable', 'string'],
        'special_annoucements' => ['nullable', 'string'],

        'tshirt_size_chart_id' => ['sometimes', 'nullable', 'integer'],
        'leo_size_chart_id' => ['sometimes', 'nullable', 'integer'],

        'mso_meet_id' => ['nullable', 'numeric', 'digits_between:1,19'],

        'admissions' => ['required', 'json'],

        'venue_name' => ['required', 'string', 'max:255'],
        'venue_website' => ['required', 'url', 'max:255'],
        'venue_addr_1' => ['required', 'string', 'max:255'],
        'venue_addr_2' => ['nullable', 'string', 'max:255'],
        'venue_city' => ['required', 'string', 'max:255'],
        'venue_state_id' => ['required', 'string', 'size:2'],
        'venue_zipcode' => ['required', 'string', 'max:255'],
        'show_participate' => ['nullable'],
        'is_featured' => ['nullable'],
    ];
    public const CREATE_STEP_6_RULES = [
        'accept_paypal' => ['sometimes'],
        'accept_ach' => ['sometimes'],
        'accept_mailed_check' => ['sometimes'],
        'accept_deposit' => ['sometimes'],
        'deposit_ratio' => ['nullable', 'integer', 'gt:0'],

        'mailed_check_instructions' => ['required_with:accept_mailed_check', 'string'],

        'defer_handling_fees' => ['sometimes'],
        'defer_processor_fees' => ['sometimes'],

        'process_refunds' => ['sometimes'],
    ];
    public const CREATE_STEP_2_RULES = [
        'registration_start_date' => ['required', 'date_format:m/d/Y', 'after_or_equal:today'],
        'registration_end_date' => ['required', 'date_format:m/d/Y', 'after_or_equal:registration_start_date'],
        'registration_scratch_end_date' => ['required', 'date_format:m/d/Y', 'after:today'],

        'allow_late_registration' => ['sometimes'],
        'late_registration_fee' => ['required_with:allow_late_registration', 'numeric'],
        'late_registration_start_date' => ['required_with:allow_late_registration', 'date_format:m/d/Y', 'after:registration_end_date'],
        'late_registration_end_date' => ['required_with:allow_late_registration', 'date_format:m/d/Y', 'after_or_equal:late_registration_start_date'],

        'athlete_limit' => ['nullable', 'integer', 'gt:0'],

        'registration_first_discount_end_date' => ['sometimes', 'date_format:m/d/Y', 'after_or_equal:registration_start_date'],
        'registration_second_discount_end_date' => ['sometimes', 'date_format:m/d/Y', 'after:registration_first_discount_end_date'],
        'registration_third_discount_end_date' => ['sometimes', 'date_format:m/d/Y', 'after:registration_second_discount_end_date'],

        'registration_first_discount_amount' => ['required_with:registration_first_discount_end_date', 'string'],
        'registration_second_discount_amount' => ['required_with:registration_second_discount_amount', 'string'],
        'registration_third_discount_amount' => ['required_with:registration_third_discount_amount', 'string'],

        'registration_first_discount_is_enable' => ['sometimes'],
        'registration_second_discount_is_enable' => ['sometimes'],
        'registration_third_discount_is_enable' => ['sometimes'],
    ];

    public const CREATE_STEP_3_RULES = [
        'categories' => ['required', 'json'],
        'meet_competition_format_id' => ['required', 'integer'],
        'meet_competition_format_other' => [
            'required_if:meet_competition_format_id,' . MeetCompetitionFormat::OTHER,
            'string', 'max:255'
        ],
        'team_format' => ['nullable', 'string'],
        'levels' => ['required', 'json'],
        'sanction_body_no' => ['sometimes'],
    ];

    public const CREATE_STEP_4_RULES = [
        'schedule' => ['nullable', 'file', 'mimes:jpeg,png,jpg,pdf'],
        'keep_schedule' => ['nullable', 'boolean'],
        'files' => ['nullable', 'array'],
        'description' => ['nullable', 'array'],
        'uploaded_files' => ['nullable', 'array'],
    ];

    public const CREATE_STEP_5_RULES = [
        'primary_contact_first_name' => ['required', 'string', 'max:255'],
        'primary_contact_last_name' => ['required', 'string', 'max:255'],
        'primary_contact_email' => ['required', 'string', 'email', 'max:255'],
        'primary_contact_phone' => ['required', 'phone:AUTO,US'],
        'primary_contact_fax' => ['nullable', 'phone:AUTO,US'],
        'get_mail_primary' => ['sometimes'],
        'get_mail_secondary' => ['sometimes'],

        'secondary_contact' => ['sometimes'],
        'secondary_contact_first_name' => ['required_with:has_secondary_contact', 'string', 'max:255'],
        'secondary_contact_last_name' => ['required_with:has_secondary_contact', 'string', 'max:255'],
        'secondary_contact_email' => ['required_with:has_secondary_contact', 'string', 'email', 'max:255'],
        'secondary_contact_job_title' => ['required_with:has_secondary_contact', 'string', 'max:255'],
        'secondary_contact_phone' => ['required_with:has_secondary_contact', 'phone:AUTO,US'],
        'secondary_contact_fax' => ['nullable', 'phone:AUTO,US'],
        'secondary_cc' => ['sometimes'],
    ];

    public function gym()
    {
        return $this->belongsTo(Gym::class);
    }

    public function tshirt_chart()
    {
        return $this->belongsTo(ClothingSizeChart::class, 'tshirt_size_chart_id');
    }

    public function leo_chart()
    {
        return $this->belongsTo(ClothingSizeChart::class, 'leo_size_chart_id');
    }

    public function venue_state()
    {
        return $this->belongsTo(State::class, 'venue_state_id');
    }

    public function oldOrValue(string $attr)
    {
        $result = null;

        $old = old($attr);
        if ($old !== null)
            $result = $old;
        else {
            $result = $this[$attr];
        }

        return $result;
    }

    public static function getSingleFileRules() {
        return ['bail', 'file', 'mimes:jpeg,png,jpg,pdf', 'max:' . Setting::meetFileMaxSize()];
    }

    public static function getStepFourRules()
    {
        $rules = self::CREATE_STEP_4_RULES;
        $rules['schedule'][] = 'max:' . Setting::meetFileMaxSize();
        $rules['files'][] = 'max:' . Setting::meetFileMaxCount();
        $rules['description'][] = 'max:' . Setting::meetFileMaxCount();
        $rules['uploaded_files'][] = 'max:' . Setting::meetFileMaxCount();
        return $rules;
    }

    public function storeStepOne(array $attr)
    {
        DB::beginTransaction();

        try {

            $start_date = (new \DateTime($attr['start_date']))->setTime(0, 0);
            $end_date = (new \DateTime($attr['end_date']))->setTime(0, 0);
            $tshirtChart = null;
            $leoChart = null;
            $state = null;

            $state = State::where('code', $attr['venue_state_id'])->first();
            if (($state == null) || ($state->code == 'WW'))
                throw new CustomBaseException('No such state.', '-1');
            $state = $state->id;

            if (isset($attr['tshirt_size_chart_id'])) {
                $tshirtChart = ClothingSizeChart::find($attr['tshirt_size_chart_id']);
                if (($tshirtChart == null) || $tshirtChart->is_leo)
                    throw new CustomBaseException('No such T-Shirt chart.', '-1');
                $tshirtChart= $tshirtChart->id;
            }

            if (isset($attr['leo_size_chart_id'])) {
                $leoChart = ClothingSizeChart::find($attr['leo_size_chart_id']);
                if (($leoChart == null) || !$leoChart->is_leo)
                    throw new CustomBaseException('No such Leotard chart.', '-1');
                $leoChart = $leoChart->id;
            }

            $admissions = json_decode($attr['admissions']);
            if ($admissions === null)
                throw new CustomBaseException('Server error', ErrorCodeCategory::getCategoryBase('General') + 1);

            if (!(is_array($admissions) && (count($admissions) > 0)))
                throw new CustomBaseException('At least one admissions should be specified.', '-1');

            foreach ($admissions as $admission) {
                $nameLen = strlen($admission->name);

                if (!(isset($admission->name) && ($nameLen > 0) && ($nameLen < 256)))
                    throw new CustomBaseException('Invalid admission name `' . $admission->name . '`.', '-1');
                $admission->name = Helper::title($admission->name);

                if (!(isset($admission->type) && Helper::isInteger($admission->type)))
                    throw new CustomBaseException('Invalid admission type `' . $admission->type . '`.', '-1');
                $admission->type = (int) $admission->type;

                if ($admission->type == MeetAdmission::TYPE_PAID) {
                    if (!(isset($admission->amount) && Helper::isFloat($admission->amount)))
                        throw new CustomBaseException('Invalid admission amount `' . $admission->amount .'`.', '-1');
                    $admission->amount = (float) $admission->amount;
                }

                if (isset($admission->id))
                    unset($admission->id);
            }

            $admissions = json_encode($admissions);
            if ($admissions === false)
                throw new CustomBaseException('Server error', ErrorCodeCategory::getCategoryBase('General') + 1);

            $this->update([
                'name' => $attr['name'],
                'description' => $attr['description'],
                'start_date' => $start_date,
                'end_date' => $end_date,
                'website' => $attr['website'],
                'equipement' => $attr['equipement'],
                'notes' => $attr['notes'],
                'special_annoucements' => $attr['special_annoucements'],
                'tshirt_size_chart_id' => $tshirtChart,
                'leo_size_chart_id' => $leoChart,
                'mso_meet_id' => (isset($attr['mso_meet_id']) ? $attr['mso_meet_id'] : null),
                'admissions' => $admissions,
                'venue_name' => $attr['venue_name'],
                'venue_addr_1' => $attr['venue_addr_1'],
                'venue_addr_2' => $attr['venue_addr_2'],
                'venue_city' => $attr['venue_city'],
                'venue_state_id' => $state,
                'venue_zipcode' => $attr['venue_zipcode'],
                'venue_website' => $attr['venue_website'],
                'step' => 2,
                'is_featured' => isset($attr['is_featured']) ? true : false,
                'show_participate_clubs' => isset($attr['show_participate']) ? true : false,
            ]);
            $this->save();
            DB::commit();
            return true;
        } catch(\Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }
    public function storeStepSix(array $attr)
    {
        DB::beginTransaction();

        try {

           
            $deposit_ratio = null;

            $accept_paypal = isset($attr['accept_paypal']);
            $accept_ach = true; //cc and ach default payment decision on 15-3-21, so here ach value set true.
            $accept_mailed_check = isset($attr['accept_mailed_check']);
            $accept_deposit = isset($attr['accept_deposit']);
            $mailed_check_instructions = null;

            // all the fees are now paid by the registring gym as per request from bill.
            $defer_handling_fees = true; //isset($attr['defer_handling_fees']);
            $defer_processor_fees = true; //isset($attr['defer_processor_fees']);

            if ($accept_mailed_check) {
                if (!isset($attr['mailed_check_instructions']))
                    throw new CustomBaseException('Please provide instructions for mailed checks', '-1');
                $mailed_check_instructions = $attr['mailed_check_instructions'];
            }

            if ($accept_deposit) {
                if (!isset($attr['deposit_ratio']))
                    throw new CustomBaseException('Please provide deposit ratio', '-1');
                $deposit_ratio = $attr['deposit_ratio'];
                if (!Helper::isInteger($deposit_ratio))
                    throw new CustomBaseException('Invalid deposit ratio value.', '-1');
                $deposit_ratio = (int) $deposit_ratio;

                if ($deposit_ratio < 1)
                    throw new CustomBaseException('Deposit ratio must be greater than 0.', '-1');
            }

            $this->update([
                
                'deposit_ratio' => ($deposit_ratio != null ? $deposit_ratio : 0),
                'accept_paypal' => $accept_paypal,
                'accept_ach' => $accept_ach,
                'accept_mailed_check' => $accept_mailed_check,
                'accept_deposit' => $accept_deposit,
                'mailed_check_instructions' => $mailed_check_instructions,
                'defer_handling_fees' => $defer_handling_fees,
                'defer_processor_fees' => $defer_processor_fees,
                'step' => 3
            ]);
            $this->save();
            DB::commit();
            return true;
        } catch(\Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }
    public function storeStepTwo(array $attr)
    {
        DB::beginTransaction();

        try {

            $registration_start_date = (new \DateTime($attr['registration_start_date']))->setTime(0, 0);
            $registration_end_date = (new \DateTime($attr['registration_end_date']))->setTime(0, 0);
            $scratch_date = (new \DateTime($attr['registration_scratch_end_date']))->setTime(0, 0);

            if ($registration_end_date >= $this->start_date)
                throw new CustomBaseException('The meet start date should be after the registration end date.');

            if ($scratch_date > $this->start_date)
                throw new CustomBaseException('The scratch date should be before or equal to the meet start date.');

            $allow_late = isset($attr['allow_late_registration']);
            $late_fee = null;
            $late_registration_start_date = null;
            $late_registration_end_date = null;

            $athlete_limit = null;
            $deposit_ratio = null;

            if ($allow_late) {
                if (!Helper::isFloat($attr['late_registration_fee']))
                    throw new CustomBaseException('Invalid late registration fee value.', '-1');
                $late_fee = (float) $attr['late_registration_fee'];

                $late_registration_start_date = (new \DateTime($attr['late_registration_start_date']))->setTime(0, 0);
                $late_registration_end_date = (new \DateTime($attr['late_registration_end_date']))->setTime(0, 0);

                if ($late_registration_end_date >= $this->start_date)
                    throw new CustomBaseException('The meet start date should be after the late registration end date.');
            }

            $registration_first_discount_is_enable = isset($attr['registration_first_discount_is_enable']);
            $registration_second_discount_is_enable = isset($attr['registration_second_discount_is_enable']);
            $registration_third_discount_is_enable = isset($attr['registration_third_discount_is_enable']);
            
            $first_end_date = null;
            $first_discount = null;
            $second_end_date = null;
            $second_discount = null;
            $third_end_date = null;
            $third_discount = null;

            if($registration_first_discount_is_enable)
            {
                $first_end_date = (new \DateTime($attr['registration_first_discount_end_date']))->setTime(0, 0);
                $first_discount = $attr['registration_first_discount_amount'];
                if (!Helper::isFloat($first_discount) && $first_discount <= 0)
                    throw new CustomBaseException('Invalid Discount value ', '-1');
                $first_discount = (float) $first_discount;

                if ($first_end_date < $registration_start_date)
                    throw new CustomBaseException('The first discount end date should be after or equal the registration start date.');

                if($registration_second_discount_is_enable)
                {
                    $second_end_date = (new \DateTime($attr['registration_second_discount_end_date']))->setTime(0, 0);
                    $second_discount = $attr['registration_second_discount_amount'];
                    if (!Helper::isFloat($second_discount) && $second_discount <= 0)
                        throw new CustomBaseException('Invalid Discount value ', '-1');
                    $second_discount = (float) $second_discount;
                    
                    if ($second_end_date <= $first_end_date)
                        throw new CustomBaseException('The second discount end date should be after the first discount end date.');

                    if($registration_third_discount_is_enable)
                    {
                        $third_end_date = (new \DateTime($attr['registration_third_discount_end_date']))->setTime(0, 0);
                        $third_discount = $attr['registration_third_discount_amount'];
                        if (!Helper::isFloat($third_discount) && $third_discount <= 0)
                            throw new CustomBaseException('Invalid Discount value ', '-1');
                        $third_discount = (float) $third_discount;
                        
                        if ($third_end_date > $late_registration_start_date && $allow_late)
                            throw new CustomBaseException('The third discount end date should be before the late registration start date. ');
                    }
                }
            }

            if (isset($attr['athlete_limit'])) {
                if (!Helper::isInteger($attr['athlete_limit']))
                    throw new CustomBaseException('Invalid athlete limit value.', '-1');
                $athlete_limit = (int) $attr['athlete_limit'];

                if ($athlete_limit < 1)
                    throw new CustomBaseException('Invalid athlete limit value.', '-1');
            }

            $this->update([
                'registration_start_date' => $registration_start_date,
                'registration_end_date' => $registration_end_date,
                'registration_scratch_end_date' => $scratch_date,
                'allow_late_registration' => $allow_late,
                'late_registration_fee' => ($late_fee != null ? $late_fee : 0),
                'late_registration_start_date' => $late_registration_start_date,
                'late_registration_end_date' => $late_registration_end_date,
                'athlete_limit' => $athlete_limit,

                'registration_first_discount_end_date' => $first_end_date,
                'registration_second_discount_end_date' => $second_end_date,
                'registration_third_discount_end_date' => $third_end_date,
        
                'registration_first_discount_amount' => $first_discount,
                'registration_second_discount_amount' => $second_discount,
                'registration_third_discount_amount' => $third_discount,

                'registration_first_discount_is_enable' => $registration_first_discount_is_enable,
                'registration_second_discount_is_enable' => $registration_second_discount_is_enable,
                'registration_third_discount_is_enable' => $registration_third_discount_is_enable,

                'step' => 6
            ]);
            $this->save();
            DB::commit();
            return true;
        } catch(\Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function storeStepThree(array $attr)
    {
        DB::beginTransaction();

        try {
            $sb = json_decode($attr['sanction_body_no']);
            $sb_ar = [];
            foreach ($sb as $key => $value) {
                    $v = trim($value[2]);
                    if($v != "" || $v != null)
                        $sb_ar[$value[0]][$value[1]] = $v;
            }
            $competition_format = MeetCompetitionFormat::find($attr['meet_competition_format_id']);
            if ($competition_format === null)
                throw new CustomBaseException('No such competition format', -1);

            $categories = json_decode($attr['categories']);
            if ($categories === null)
                throw new CustomBaseException('Server error', ErrorCodeCategory::getCategoryBase('General') + 1);

            if (!(is_array($categories) && (count($categories) > 0)))
                throw new CustomBaseException('At least one category should be specified.', '-1');


            $selected_categories = [];
            $levels_required = false;
            foreach ($categories as $categoryData) {
                if (!isset($categoryData->id))
                    throw new CustomBaseException('Missing category data (id).', '-1');
                $category = LevelCategory::find($categoryData->id); /** @var LevelCategory $category */
                if ($category == null)
                    throw new CustomBaseException('Wrong category data (id).', '-1');

                if (!isset($categoryData->body_id))
                    throw new CustomBaseException('Missing category data (body id).', '-1');
                $body = SanctioningBody::find($categoryData->body_id);
                if ($body == null)
                    throw new CustomBaseException('Wrong category data (body id).', '-1');

                $categoryData->id = $category->id;
                $categoryData->body_id = $body->id;
                // $categoryData->sanction = null;
                $categoryData->sanction = (isset($sb_ar[$body->id]) && isset($sb_ar[$body->id][$category->id])) ? $sb_ar[$body->id][$category->id] : null;
                $categoryData->officially_sanctioned = false;

                $key = 'b' . $categoryData->body_id . '-c' . $category->id;
                $selected_categories[$key] = $categoryData;
                $selected_categories[$key]->requires_sanction =  LevelCategory::requiresSanction($body->id);

                if (!$selected_categories[$key]->requires_sanction)
                    $levels_required = true;
            }

            $categories = json_encode($categories);
            if ($categories === false)
                throw new CustomBaseException('Server error', ErrorCodeCategory::getCategoryBase('General') + 1);

            $levelFields = [
                'male', 'female', 'registration_fee', 'late_registration_fee', 'allow_specialist',
                'specialist_registration_fee', 'specialist_late_registration_fee', 'allow_team',
                'team_registration_fee', 'team_late_registration_fee', 'enable_athlete_limit',
                'athlete_limit'
            ];
            $levelGenderMatrix = [];
            $levels = json_decode($attr['levels']);
            if ($levels === null)
                throw new CustomBaseException('Server error', ErrorCodeCategory::getCategoryBase('General') + 1);

            if (!is_array($levels))
                throw new CustomBaseException('Invalid levels data format.', '-1');

            if ($levels_required && !(count($levels) > 0))
                throw new CustomBaseException('At least one level should be specified.', '-1');

            foreach ($levels as $levelData) {
                if (!isset($levelData->id))
                    throw new CustomBaseException('Missing level data (id).', '-1');
                $level = AthleteLevel::find($levelData->id);
                if ($level == null)
                    throw new CustomBaseException('Wrong level data (id).', '-1');

                $key = 'b' . $level->sanctioning_body_id . '-c' . $level->level_category_id;
                // if (!property_exists($key, $selected_categories))
                if (!isset($selected_categories[$key]))
                    throw new CustomBaseException('The category for this level was not selected', '-1');

                $categoryData = $selected_categories[$key];
                if ($categoryData->requires_sanction)
                    throw new CustomBaseException('You cannot add levels in this category', '-1');

                foreach ($levelFields as $field) {
                    if (!isset($levelData->$field))
                        throw new CustomBaseException('Missing level data (' . $field . ').', '-1');
                }

                if ($level->level_category->male && $level->level_category->female) {
                    if (!($levelData->male || $levelData->female))
                        throw new CustomBaseException('Wrong level data : at least one gender should be allowed', -1);
                } else {
                    $levelData->male = $level->level_category->male;
                    $levelData->female = $level->level_category->female;
                }

                if (key_exists($level->id, $levelGenderMatrix)) {
                    $duplicate = $levelGenderMatrix[$level->id];
                    if (($levelData->male && $duplicate['male']) ||
                        ($levelData->female && $duplicate['female']))
                        throw new CustomBaseException('Wrong level data : duplicate levels', -1);
                };

                if(!Helper::isFloat($levelData->registration_fee))
                    throw new CustomBaseException('Wrong level data : invalid registration fee', -1);
                $levelData->registration_fee = (float) $levelData->registration_fee;

                if ($this->allow_late_registration) {
                    if(!Helper::isFloat($levelData->late_registration_fee))
                        throw new CustomBaseException('Wrong level data : invalid late registration fee', -1);
                    $levelData->late_registration_fee = (float) $levelData->late_registration_fee;
                } else {
                    $levelData->late_registration_fee = 0;
                }

                if ($level->hasSpecialist() && $levelData->allow_specialist) {
                    if(!Helper::isFloat($levelData->specialist_registration_fee))
                        throw new CustomBaseException('Wrong level data : invalid specialist registration fee', -1);
                    $levelData->specialist_registration_fee = (float) $levelData->specialist_registration_fee;

                    if ($this->allow_late_registration) {
                        if (!Helper::isFloat($levelData->specialist_late_registration_fee))
                            throw new CustomBaseException('Wrong level data : invalid specialist late registration fee', -1);
                        $levelData->specialist_late_registration_fee = (float) $levelData->specialist_late_registration_fee;
                    } else {
                        $levelData->specialist_late_registration_fee = 0;
                    }
                } else {
                    $levelData->allow_specialist = false;
                    $levelData->specialist_registration_fee = 0;
                    $levelData->specialist_late_registration_fee = 0;
                }

                if ($levelData->allow_team) {
                    if(!Helper::isFloat($levelData->team_registration_fee))
                        throw new CustomBaseException('Wrong level data : invalid team registration fee', -1);
                    $levelData->team_registration_fee = (float) $levelData->team_registration_fee;

                    if ($this->allow_late_registration) {
                        if(!Helper::isFloat($levelData->team_late_registration_fee))
                            throw new CustomBaseException('Wrong level data : invalid team late registration fee', -1);
                        $levelData->team_late_registration_fee = (float) $levelData->team_late_registration_fee;
                    } else {
                        $levelData->team_late_registration_fee = 0;
                    }
                } else {
                    $levelData->allow_team = false;
                    $levelData->team_registration_fee = 0;
                    $levelData->team_late_registration_fee = 0;
                }

                if ($levelData->enable_athlete_limit) {
                    if(!(Helper::isInteger($levelData->athlete_limit) && ((int) $levelData->athlete_limit > 0)))
                        throw new CustomBaseException('Wrong level data : invalid athlete limit', -1);
                    $levelData->athlete_limit = (int) $levelData->athlete_limit;
                } else {
                    $levelData->enable_athlete_limit = false;
                    $levelData->athlete_limit = 0;
                }

                $levelGenderMatrix[$level->id] = [
                    'male' => $levelData->male,
                    'female' => $levelData->female,
                ];
            }

            $levels = json_encode($levels);
            if ($levels === false)
                throw new CustomBaseException('Server error', ErrorCodeCategory::getCategoryBase('General') + 1);

            $this->update([
                'categories' => $categories,
                'meet_competition_format_id' => $competition_format->id,
                'meet_competition_format_other' => isset($attr['meet_competition_format_other']) ? $attr['meet_competition_format_other'] : null,
                'team_format' => isset($attr['team_format']) ? $attr['team_format'] : null,
                'levels' => $levels,
                'step' => 4
            ]);
            $this->save();
            DB::commit();
            return true;
        } catch(\Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function storeStepFour(array $attr)
    {
        DB::beginTransaction();

        try {
            $storedFiles = [];
            $filesToDelete = [];

            $schedule = null;
            $files = null;

            $oldSchedule = null;
            if ($this->schedule !== null) {
                $oldSchedule = json_decode($this->schedule);
                if ($oldSchedule === null)
                    throw new CustomBaseException('Server error', ErrorCodeCategory::getCategoryBase('General') + 1);

                $keepOldSchedule = isset($attr['keep_schedule']) && $attr['keep_schedule'];
                if ($keepOldSchedule)
                    $schedule = $this->schedule;
                else
                    $filesToDelete[] = $oldSchedule->path;
            }

            if (isset($attr['schedule'])) {
                $schedule = Storage::url(Storage::putFile(
                    'public/files/' . $this->gym->id . '/meet', $attr['schedule']
                ));
                $storedFiles[] = $schedule;

                $schedule = [
                    'name' => $attr['schedule']->getClientOriginalName(),
                    'path' => $schedule
                ];

                $schedule = json_encode($schedule);
                if ($schedule === false)
                    throw new CustomBaseException('Server error', ErrorCodeCategory::getCategoryBase('General') + 1);

                if ($oldSchedule !== null)
                    $filesToDelete[] = $oldSchedule->path;
            }

            $oldFiles = [];
            if ($this->files !== null) {
                $oldFiles = json_decode($this->files);
                if ($oldFiles === null)
                    throw new CustomBaseException('Server error', ErrorCodeCategory::getCategoryBase('General') + 1);
            }

            $filesToKeep = (isset($attr['uploaded_files']) ? $attr['uploaded_files'] : []);

            $keep = [];
            $deleted = [];
            foreach ($oldFiles as $old) {
                if (in_array($old->path, $filesToKeep))
                    $keep[] = $old;
                else
                    $deleted[] = $old;
            }

            $files = $keep;

            $limit = Setting::meetFileMaxCount();

            if (isset($attr['files'])) {
                $fileCount = count($attr['files']);

                if (($fileCount != count($attr['description'])))
                    throw new CustomBaseException('File / Description array mismatch', -1);

                if (($fileCount + count($filesToKeep)) > $limit)
                    throw new CustomBaseException('Only ' . $limit . ' files can be uploaded.', -1);

                $singleFileRules = self::getSingleFileRules();

                for ($i = 0; $i < $fileCount; $i++) {
                    if (strlen($attr['description'][$i]) > 255)
                        throw new CustomBaseException('Descriptions should be less than 255 characters long.', -1);

                    $validator = Validator::make(['file' => $attr['files'][$i]], ['file' => $singleFileRules]);
                    if ($validator->fails()) {
                        throw new CustomBaseException('Wrong file type,or file bigger than ' .
                            Helper::formatByteSize(Setting::meetFileMaxSize() * 1024) , -1);
                    }

                    $file = $attr['files'][$i];

                    $file = Storage::url(Storage::putFile(
                        'public/files/' . $this->gym->id . '/meet', $file
                    ));

                    $storedFiles[] = $file;

                    $files[] = [
                        'name' => $attr['files'][$i]->getClientOriginalName(),
                        'path' => $file,
                        'description' => $attr['description'][$i]
                    ];
                }
            }

            $files = json_encode($files);
            if ($files === false)
                throw new CustomBaseException('Server error', ErrorCodeCategory::getCategoryBase('General') + 1);

            $this->update([
                'schedule' => $schedule,
                'files' => $files,
                'step' => 5
            ]);
            $this->save();

            foreach ($deleted as $file)
                $filesToDelete[]= $file->path;

            DB::commit();
            return true;
        } catch(\Throwable $e) {
            DB::rollBack();

            foreach ($storedFiles as $file)
                Helper::removeOldFile($file, null);

            throw $e;
        } finally {
            foreach ($filesToDelete as $file)
                Helper::removeOldFile($file, null);
        }
    }

    public function storeStepFive(array $attr)
    {
        DB::beginTransaction();

        try {

            $hasSecondary = isset($attr['secondary_contact']);

            $this->update([
                'primary_contact_first_name' => $attr['primary_contact_first_name'],
                'primary_contact_last_name' => $attr['primary_contact_last_name'],
                'primary_contact_email' => $attr['primary_contact_email'],
                'primary_contact_phone' => $attr['primary_contact_phone'],
                'primary_contact_fax' => $attr['primary_contact_fax'],
                'get_mail_primary' => isset($attr['get_mail_primary']) ??  false,

                'secondary_contact' => $hasSecondary,
                'secondary_contact_first_name' => ($hasSecondary ? $attr['secondary_contact_first_name'] : null),
                'secondary_contact_last_name' => ($hasSecondary ? $attr['secondary_contact_last_name'] : null),
                'secondary_contact_email' => ($hasSecondary ? $attr['secondary_contact_email'] : null),
                'secondary_contact_job_title' => ($hasSecondary ? $attr['secondary_contact_job_title'] : null),
                'secondary_contact_phone' => ($hasSecondary ? $attr['secondary_contact_phone'] : null),
                'secondary_contact_fax' => ($hasSecondary ? $attr['secondary_contact_fax'] : null),
                'secondary_cc' => ($hasSecondary ? isset($attr['secondary_cc']) : false),
                'get_mail_secondary' => ($hasSecondary ? isset($attr['get_mail_secondary']) : false),
            ]);
            $this->save();

            $meet = $this->gym->meets()->create([
                'profile_picture' => config('app.default_meet_picture'),
                'name' => $this->name,
                'description' => $this->description,
                'start_date' => $this->start_date,
                'end_date' => $this->end_date,
                'website' => $this->website,
                'equipement' => $this->equipement,
                'notes' => $this->notes,
                'special_annoucements' => $this->special_annoucements,

                'tshirt_size_chart_id' => $this->tshirt_size_chart_id,
                'leo_size_chart_id' => $this->leo_size_chart_id,

                'mso_meet_id' => $this->mso_meet_id,

                'venue_name' => $this->venue_name,
                'venue_addr_1' => $this->venue_addr_1,
                'venue_addr_2' => $this->venue_addr_2,
                'venue_city' => $this->venue_city,
                'venue_state_id' => $this->venue_state_id,
                'venue_zipcode' => $this->venue_zipcode,
                'venue_website' => $this->venue_website,

                'registration_start_date' => $this->registration_start_date,
                'registration_end_date' => $this->registration_end_date,
                'registration_scratch_end_date' => $this->registration_scratch_end_date,

                'allow_late_registration' => $this->allow_late_registration,
                'late_registration_fee' => $this->late_registration_fee,
                'late_registration_start_date' => $this->late_registration_start_date,
                'late_registration_end_date' => $this->late_registration_end_date,

                'athlete_limit' => $this->athlete_limit,

                'accept_paypal' => $this->accept_paypal,
                'accept_ach' => $this->accept_ach,
                'accept_mailed_check' => $this->accept_mailed_check,

                'accept_deposit' => $this->accept_deposit,
                'deposit_ratio' => $this->deposit_ratio,

                'mailed_check_instructions' => $this->mailed_check_instructions,

                'defer_handling_fees' => $this->defer_handling_fees,
                'defer_processor_fees' => $this->defer_processor_fees,

                'meet_competition_format_id' => $this->meet_competition_format_id,
                'meet_competition_format_other' => $this->meet_competition_format_other,
                'team_format' => $this->team_format,

                'schedule' => $this->schedule,

                'primary_contact_first_name' => $this->primary_contact_first_name,
                'primary_contact_last_name' => $this->primary_contact_last_name,
                'primary_contact_email' => $this->primary_contact_email,
                'primary_contact_phone' => $this->primary_contact_phone,
                'primary_contact_fax' => $this->primary_contact_fax,
                'get_mail_primary' => $this->get_mail_primary,

                'secondary_contact' => $this->secondary_contact,
                'secondary_contact_first_name' => $this->secondary_contact_first_name,
                'secondary_contact_last_name' => $this->secondary_contact_last_name,
                'secondary_contact_email' => $this->secondary_contact_email,
                'secondary_contact_job_title' => $this->secondary_contact_job_title,
                'secondary_contact_phone' => $this->secondary_contact_phone,
                'secondary_contact_fax' => $this->secondary_contact_fax,
                'secondary_cc' => $this->secondary_cc,
                'get_mail_secondary' => $this->get_mail_secondary,
                'is_featured' => $this->is_featured,
                'show_participate_clubs' => $this->show_participate_clubs,

                'registration_first_discount_end_date' => $this->registration_first_discount_end_date,
                'registration_second_discount_end_date' => $this->registration_second_discount_end_date,
                'registration_third_discount_end_date' => $this->registration_third_discount_end_date,
        
                'registration_first_discount_amount' => $this->registration_first_discount_amount,
                'registration_second_discount_amount' => $this->registration_second_discount_amount,
                'registration_third_discount_amount' => $this->registration_third_discount_amount,

                'registration_first_discount_is_enable' => $this->registration_first_discount_is_enable,
                'registration_second_discount_is_enable' => $this->registration_second_discount_is_enable,
                'registration_third_discount_is_enable' => $this->registration_third_discount_is_enable,
            ]); /** @var Meet $meet */

            $admissions = json_decode($this->admissions);
            if ($admissions === null)
                throw new CustomBaseException('Server error', ErrorCodeCategory::getCategoryBase('General') + 1);

            foreach ($admissions as $admission) {
                $meet->admissions()->create([
                    'name' => $admission->name,
                    'type' => $admission->type,
                    'amount' => $admission->amount == null ? 0 : $admission->amount,
                ]);
            }

            $categories = json_decode($this->categories);
            if ($categories === null)
                throw new CustomBaseException('Server error', ErrorCodeCategory::getCategoryBase('General') + 1);

            foreach ($categories as $category) {
                CategoryMeet::create([
                    'sanctioning_body_id' => $category->body_id,
                    'level_category_id' => $category->id,
                    'meet_id' => $meet->id,
                    'sanction_no' => $category->sanction,
                ]);
            }

            $levels = json_decode($this->levels);
            if ($levels === null)
                throw new CustomBaseException('Server error', ErrorCodeCategory::getCategoryBase('General') + 1);

            foreach ($levels as $level) {
                LevelMeet::create([
                    'athlete_level_id' => $level->id,
                    'meet_id' => $meet->id,
                    'allow_men' => $level->male,
                    'allow_women' => $level->female,
                    'registration_fee' => $level->registration_fee,
                    'late_registration_fee' => $level->late_registration_fee,
                    'allow_specialist' => $level->allow_specialist,
                    'specialist_registration_fee' => $level->specialist_registration_fee,
                    'specialist_late_registration_fee' => $level->specialist_late_registration_fee,
                    'allow_teams' => $level->allow_team,
                    'team_registration_fee' => $level->team_registration_fee,
                    'team_late_registration_fee' => $level->team_late_registration_fee,
                    'enable_athlete_limit' => $level->enable_athlete_limit,
                    'athlete_limit' => $level->athlete_limit,

                    'registration_fee_first' => $level->registration_fee_first,
                    // 'registration_fee_second' => $level->registration_fee_second,
                    // 'registration_fee_third' => $level->registration_fee_third,
                ]);
            }

            $files = json_decode($this->files);
            if ($files === null)
                throw new CustomBaseException('Server error', ErrorCodeCategory::getCategoryBase('General') + 1);

            foreach ($files as $file) {
                $meet->files()->create([
                    'name' => $file->name,
                    'path' => $file->path,
                    'description' => $file->description
                ]);
            }

            $this->delete();

            AuditEvent::meetCreated(
                request()->_managed_account, auth()->user(), $meet
            );

            DB::commit();
            return $meet;
        } catch(\Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }
}
