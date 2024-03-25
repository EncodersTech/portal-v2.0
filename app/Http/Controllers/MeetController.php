<?php

namespace App\Http\Controllers;
use App\Exceptions\CustomStripeException;
use App\Mail\MassMailerNotification;
use App\Models\MassMailer;
use App\Mail\Registrant\MeetScheduleUploadedMailable;
use App\Mail\PastMeetsGymsNotification;
use App\Repositories\MeetRepository;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Gym;
use App\Models\Meet;
use App\Exceptions\CustomBaseException;
use App\Helper;
use App\Models\CategoryMeet;
use Illuminate\Support\Facades\DB;
use App\Models\ClothingSizeChart;
use App\Models\ErrorCodeCategory;
use App\Models\LevelCategory;
use App\Models\State;
use App\Models\TemporaryMeet;
use Illuminate\Support\Facades\Mail as Email;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use App\Models\MeetCompetitionFormat;
use App\Models\MeetRegistration;
use App\Models\MeetReport;
use App\Models\MeetTransaction;
use App\Models\SanctioningBody;
use App\Models\Setting;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use PDF;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\CsvExport;
use App\Models\RegistrationAthlete;
use App\Models\RegistrationSpecialist;
use Illuminate\Support\Str;
use Spatie\MediaLibrary\Models\Media;
use Barryvdh\Snappy\PdfWrapper;
use App\Jobs\SendEmailJob;
class MeetController extends AppBaseController
{
    /**
     * @var MeetRepository
     */
    private $meetRepo;

    public function __construct(MeetRepository $meetRepo)
    {
        $this->meetRepo = $meetRepo;
    }

    private static function _get_page_name(Gym $gym)
    {
        return 'gym-' . $gym->id;
    }

    public function index(Request $request, string $gym)
    {
        $gym = $request->_managed_account->retrieveGym($gym); /** @var Gym $gym */

        return view('meet.list', [
            'current_page' => self::_get_page_name($gym),
            'gym' => $gym
        ]);
    }

    public function details(Request $request, string $meet)
    {
        try {
            $meet = Meet::retrieveMeet($meet, true); /** @var Meet $meet */
            $gym = $meet->gym; /** @var Gym $gym */
            $registrations = null;
            $owner = $gym->user; /** @var User $owner */
            $is_own = ($request->_managed_account->id == $owner->id);

            $registrations = $meet->getUserRegistrations($request->_managed_account);

            if (!($meet->is_published || $is_own))
                throw new CustomBaseException("No such meet", -1);

            $levels = Helper::getStructuredLevelList($meet->activeLevels, $meet);

            $allCategories = [];
            foreach ($meet->categories as $c) { /** @var LevelCategory $c */
                $pivot = $c->pivot; /** @var CategoryMeet $pivot */
                $klc = "";
                if($pivot->sanction_no != null)
                {
                    $klc = " ; Sanction No: ".$pivot->sanction_no;
                }
                $body = $pivot->sanctioning_body->initialism ;
                if (!isset($allCategories[$body]))
                    $allCategories[$body] = [];

                $allCategories[$body][] = $c->name . $klc;
                // $allCategories[$body]["sanc"][] = $c->name;
            }

            if ($meet->schedule != null) {
                $meet->schedule = json_decode($meet->schedule);
                if ($meet->schedule === null)
                    throw new CustomBaseException('Server error', ErrorCodeCategory::getCategoryBase('General') + 1);
            }
        } catch (CustomBaseException $e) {
            return redirect(route('dashboard'))->with('error', $e->getMessage());
        }
        return view('meet.details', [
            'current_page' => ($is_own ? self::_get_page_name($gym) : 'browse-meets'),
            'owner' => $owner,
            'gym' => $gym,
            'meet' => $meet,
            'registrations' => $registrations,
            'allCategories' => $allCategories,
            'bodies' => $levels,
            'mini_level' => $this->getMiniLevel($levels),
            'is_own' => $is_own,
            'today' => now()->setTime(0, 0) 
        ]);
    }
    public function calendar()
    {
        return view('meet.calendar', [
            'current_page' => 'calendar'
        ]);
    }
    public function getMiniLevel($levels)
    {
        foreach ($levels as $k => $lbl) {
            
            foreach ($lbl["categories"] as $key => $levels) {
                $m_levels[$k][$key]["fee"] = 0;
                $m_levels[$k][$key]["has_change"] = false;
                foreach ($levels["levels"] as $keys => $value) {
                    if($m_levels[$k][$key]["fee"] == 0)
                    {
                        $m_levels[$k][$key]["fee"] = $value["pivot"]["registration_fee"];
                        $m_levels[$k][$key]["registration_fee_first"] = $value["pivot"]["registration_fee_first"];
                        // $m_levels[$k][$key]["registration_fee_second"] = $value["pivot"]["registration_fee_second"];
                        // $m_levels[$k][$key]["registration_fee_third"] = $value["pivot"]["registration_fee_third"];
                    }
                    else if($m_levels[$k][$key]["fee"] !=  $value["pivot"]["registration_fee"])
                    {
                        $m_levels[$k][$key]["has_change"] = true;
                        $m_levels[$k][$key]["fee"] = min($m_levels[$k][$key]["fee"], $value["pivot"]["registration_fee"]);
                        $m_levels[$k][$key]["registration_fee_first"] = min($value["pivot"]["registration_fee_first"], $value["pivot"]["registration_fee_first"]);
                        // $m_levels[$k][$key]["registration_fee_second"] = min($value["pivot"]["registration_fee_second"],  $value["pivot"]["registration_fee_second"]);
                        // $m_levels[$k][$key]["registration_fee_third"] = min($value["pivot"]["registration_fee_third"],$value["pivot"]["registration_fee_third"] );
                    }
                    # code...
                }
            }
        }
        return $m_levels;
    }
    public function create(Request $request, string $gym)
    {
        $gym = $request->_managed_account->retrieveGym($gym); /** @var Gym $gym */

        if (($gym->meets()->count() < 1) && ($gym->temporary_meets()->where('step', '>', '1')->count() < 1))
            return $this->createFromScratch($request, $gym->id);

        $activeMeets = $gym->meets()->where('is_archived', false)->orderBy('name', 'ASC')->get();
        $archivedMeets = $gym->meets()->where('is_archived', true)->orderBy('name', 'ASC')->get();

        return view('meet.create-copy', [
            'current_page' => self::_get_page_name($gym),
            'gym' => $gym,
            'activeMeets' => $activeMeets,
            'archivedMeets' => $archivedMeets
        ]);
    }

    public function createFromScratch(Request $request, string $gym)
    {
        DB::beginTransaction();
        try {
            $gym = $request->_managed_account->retrieveGym($gym); /** @var Gym $gym */

            $tm = $gym->temporary_meets()->create([]); // Create empty entity. Keep [].

            DB::commit();
            return redirect(route('gyms.meets.create.step.view', [
                'gym' => $gym,
                'tm' => $tm,
                'step' => 1
            ]));
        } catch(CustomBaseException $e) {
            DB::rollBack();
            throw $e;
        } catch(\Throwable $e) {
            DB::rollBack();
            throw $e;
            throw new CustomBaseException('Something went wrong while loading the meet creation view.', -1, $e);
        }
    }

    public function createFromCopy(Request $request, string $gym)
    {
        $attr = $request->validate(Meet::MEET_COPY_RULES);
        $gym = $request->_managed_account->retrieveGym($gym); /** @var Gym $gym */

        $tm = $gym->copyFromMeet($attr);

        return redirect(route('gyms.meets.create.step.view', [
            'gym' => $gym,
            'tm' => $tm,
            'step' => 1
        ]));
    }

    public function stepView(Request $request, string $gym, string $step, string $temporary)
    {
        if (!Helper::isInteger($step))
            return new CustomBaseException('Invalid step.');

        $step = (int) $step;
        if (($step < 1) || ($step > 6))
            return new CustomBaseException('Invalid step.');

        $gym = $request->_managed_account->retrieveGym($gym); /** @var Gym $gym */

        $tm = $gym->temporary_meets->find($temporary);
        if ($tm->step < 2){
            $tm->name = '';
        }
        if (($tm == null) || (($tm->step < $step) && $step != 6))
            return redirect(route('gyms.meets.create', ['$gym' => $gym]))
                ->with('error','Something went wrong while displaying your meet.');

        $required_sanctions = [];
        foreach (SanctioningBody::all() as $body) /** @var SanctioningBody $body */
            $required_sanctions[$body->id] = LevelCategory::requiresSanction($body->id);

        $required_sanctions = json_encode($required_sanctions);
        if ($required_sanctions === false)
                throw new CustomBaseException('Server error', ErrorCodeCategory::getCategoryBase('General') + 1);

        $data = [
            'current_page' => self::_get_page_name($gym),
            'gym' => $gym,
            'tm' => $tm,
            'step' => $step,
            'required_sanctions' => $required_sanctions
        ];
        
        switch($step) {
            case 1:
                $data['tshirt_charts'] = ClothingSizeChart::where('is_leo', false)->get();
                $data['leo_charts'] = ClothingSizeChart::where('is_leo', true)->get();
                $data['states'] = State::where('code', '!=', 'WW')->get();
                break;

            case 2:
                $card_exist = 1;
                $card = null;
                $user = auth()->user();
                try {
                    $card = $user->getCards(true);
                    if($card == null)
                        $card_exist = 0;
                } catch (CustomStripeException $e) {
                    $card_exist = 0;
                }
                $data['card_exist'] = $card_exist;
                break;
            case 6:
                
                $card_exist = 1;
                $card = null;
                $user = auth()->user();
                try {
                    $card = $user->getCards(true);
                    if($card == null)
                        $card_exist = 0;
                } catch (CustomStripeException $e) {
                    $card_exist = 0;
                }
                $data['card_exist'] = $card_exist;
                break;
            case 3:
                $data['competition_formats'] = MeetCompetitionFormat::all();
                break;

            case 4:
                $data['meet_max_file_size'] = Helper::formatByteSize(Setting::meetFileMaxSize() * 1024);
                $data['meet_max_file_count'] = Setting::meetFileMaxCount();
                break;

            default:
                $data['secondaries'] = $request->_managed_account->members;
        }
        return view('meet.create', $data);
    }

    public function storeStepOne(Request $request, string $gym, string $temporary)
    {
        $attr = $request->all();
        if (isset($attr['website']))
            $attr['website'] = Helper::dummyProofUrl($attr['website']);

        if (isset($attr['venue_website']))
            $attr['venue_website'] = Helper::dummyProofUrl($attr['venue_website']);

        $attr = Validator::make($attr, TemporaryMeet::CREATE_STEP_1_RULES)->validate();

        $gym = $request->_managed_account->retrieveGym($gym); /** @var Gym $gym */

        $tm = $gym->temporary_meets->find($temporary);
        if ($tm == null)
            throw new CustomBaseException('Something went wrong while saving your meet.', -1);

        $tm->storeStepOne($attr);
        return redirect(route('gyms.meets.create.step.view', [
            'gym' => $gym,
            'tm' => $tm,
            'step' => $tm->step
        ]));
    }
    public function storeStepSix(Request $request, string $gym, string $temporary)
    {
        $attr = $request->validate(TemporaryMeet::CREATE_STEP_6_RULES);

        $gym = $request->_managed_account->retrieveGym($gym); /** @var Gym $gym */

        $tm = $gym->temporary_meets->find($temporary);
        if ($tm == null)
            throw new CustomBaseException('Something went wrong while saving your meet.', -1);

        $tm->storeStepSix($attr);
        
        return redirect(route('gyms.meets.create.step.view', [
            'gym' => $gym,
            'tm' => $tm,
            'step' => $tm->step
        ]));
    }
    public function storeStepTwo(Request $request, string $gym, string $temporary)
    {
        $attr = $request->validate(TemporaryMeet::CREATE_STEP_2_RULES);

        $gym = $request->_managed_account->retrieveGym($gym); /** @var Gym $gym */

        $tm = $gym->temporary_meets->find($temporary);
        if ($tm == null)
            throw new CustomBaseException('Something went wrong while saving your meet.', -1);

        $tm->storeStepTwo($attr);
        
        return redirect(route('gyms.meets.create.step.view', [
            'gym' => $gym,
            'tm' => $tm,
            'step' => $tm->step
        ]));
    }

    public function storeStepThree(Request $request, string $gym, string $temporary)
    {
        $attr = $request->validate(TemporaryMeet::CREATE_STEP_3_RULES);
        $gym = $request->_managed_account->retrieveGym($gym); /** @var Gym $gym */

        $tm = $gym->temporary_meets->find($temporary); /** @var TemporaryMeet $tm */
        if ($tm == null)
            throw new CustomBaseException('Something went wrong while saving your meet.', -1);
        $tm->storeStepThree($attr);

        return redirect(route('gyms.meets.create.step.view', [
            'gym' => $gym,
            'tm' => $tm,
            'step' => $tm->step
        ]));
    }

    public function storeStepFour(Request $request, string $gym, string $temporary)
    {
        $attr = $request->validate(TemporaryMeet::getStepFourRules());

        $gym = $request->_managed_account->retrieveGym($gym); /** @var Gym $gym */

        $tm = $gym->temporary_meets->find($temporary); /** @var TemporaryMeet $tm */
        if ($tm == null)
            throw new CustomBaseException('Something went wrong while saving your meet.', -1);

        $tm->storeStepFour($attr);

        return redirect(route('gyms.meets.create.step.view', [
            'gym' => $gym,
            'tm' => $tm,
            'step' => $tm->step
        ]));
    }

    public function storeStepFive(Request $request, string $gym, string $temporary)
    {
        $attr = $request->validate(TemporaryMeet::CREATE_STEP_5_RULES);

        $gym = $request->_managed_account->retrieveGym($gym); /** @var Gym $gym */

        $tm = $gym->temporary_meets->find($temporary);
        if ($tm == null)
            throw new CustomBaseException('Something went wrong while saving your meet.', -1);

        $meet = $tm->storeStepFive($attr);
        return view('meet.create.success', [
            'current_page' => 'success',
            'redirect_url' => route('gyms.meets.index', ['gym' => $gym])
        ]);
        // $this->afterMeetSuccessPage(route('gyms.meets.index', ['gym' => $gym]));
        // return redirect(route('gyms.meets.index', ['gym' => $gym]));
    }
    // public function afterMeetSuccessPage($url)
    // {
    //     return view('meet.create.success', [
    //         'current_page' => 'success',
    //         'redirect_url' => $url
    //     ]);
    // }

    public function edit(Request $request, string $gym, string $meet, string $step = null)
    {
        $gym = $request->_managed_account->retrieveGym($gym); /** @var Gym $gym */
        $meet = $gym->retrieveMeet($meet); /** @var Meet $meet */

        if (!Helper::isInteger($step) || ($step < 1) || ($step > 6)) {
            return redirect(route('gyms.meets.edit', [
                'gym' => $gym,
                'meet' => $meet,
                'step' => 1
            ]));
        }

        if (!$meet->canBeEdited())
            throw new CustomBaseException('You cannot edit this meet.', -1);

        $categories = [];
        foreach ($meet->categories as $category) {
            $categories[] = [
                'id' => $category->id,
                'body_id' => $category->pivot->sanctioning_body_id,
                'sanction' => $category->pivot->sanction_no,
                'officially_sanctioned' => $category->pivot->officially_sanctioned,
                'requires_sanction' => $category->pivot->requiresSanction(),
            ];
        }

        $required_sanctions = [];
        foreach (SanctioningBody::all() as $body) /** @var SanctioningBody $body */
            $required_sanctions[$body->id] = LevelCategory::requiresSanction($body->id);

        $required_sanctions = json_encode($required_sanctions);
        if ($required_sanctions === false)
                throw new CustomBaseException('Server error', ErrorCodeCategory::getCategoryBase('General') + 1);

        $categories = json_encode($categories);
        if ($categories === false)
            throw new CustomBaseException('Server error', ErrorCodeCategory::getCategoryBase('General') + 1);

        $meet->categories = $categories;

        $levels = [];
        foreach ($meet->activeLevels as $level) {
            $levels[] = [
                'id' => $level->pivot->athlete_level_id,
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
                
                'registration_fee_first' => $level->pivot->registration_fee_first, 
                // 'registration_fee_second' => $level->pivot->registration_fee_second,
                // 'registration_fee_third' => $level->pivot->registration_fee_third
            ];
        }

        $levels = json_encode($levels);
        if ($levels === false)
            throw new CustomBaseException('Server error', ErrorCodeCategory::getCategoryBase('General') + 1);
        $meet->jsonLevels = $levels;

        $files = [];
        foreach ($meet->files as $file) {
            $files[] = [
                'name' => $file->name,
                'description' => $file->description,
                'type' => $file->type,
                'amount' => $file->amount,
                'path' => $file->path,
            ];
        }
        $files = json_encode($files);
        if ($files === false)
            throw new CustomBaseException('Server error', ErrorCodeCategory::getCategoryBase('General') + 1);
        $meet->files = $files;

        $restricted_bodies = json_encode($meet->hasActiveBodyRegistrations());
        if ($restricted_bodies === false)
            throw new CustomBaseException('Server error', ErrorCodeCategory::getCategoryBase('General') + 1);

        $pastMeets = $gym->meets()->where('is_published','=',true)->where('is_archived','=',false)->where('end_date','<',Carbon::now())->get();

        $card_exist = 1;
        $card = null;
        $user = auth()->user();
        try {
            $card = $user->getCards(true);
            if($card == null)
		        $card_exist = 0;
        } catch (CustomStripeException $e) {
            $card_exist = 0;
        }

        return view('meet.edit', [
            'card_exist' => $card_exist,
            'current_page' => self::_get_page_name($gym),
            'step' => $step,
            'gym' => $gym,
            'meet' => $meet,
            'tshirt_charts' => ClothingSizeChart::where('is_leo', false)->get(),
            'leo_charts' => ClothingSizeChart::where('is_leo', true)->get(),
            'states' => State::where('code', '!=', 'WW')->get(),
            'competition_formats' => MeetCompetitionFormat::all(),
            'meet_max_file_size' => Helper::formatByteSize(Setting::meetFileMaxSize() * 1024),
            'meet_max_file_count' => Setting::meetFileMaxCount(),
            'secondaries' => $request->_managed_account->members,
            'restricted_edit' => $meet->isEditRestricted(),
            'restricted_bodies' => $restricted_bodies,
            'required_sanctions' => $required_sanctions,
            'profile_picture_max_size' => Helper::formatByteSize(Setting::profilePictureMaxSize() * 1024),
            'past_meets' => $pastMeets,
        ]);
    }

    public function updateStepOne(Request $request, string $gym, string $meet)
    {
        $attr = $request->all();
        if (isset($attr['website']))
            $attr['website'] = Helper::dummyProofUrl($attr['website']);

        if (isset($attr['venue_website']))
            $attr['venue_website'] = Helper::dummyProofUrl($attr['venue_website']);

        $attr = Validator::make($attr, Meet::UPDATE_STEP_1_RULES)->validate();

        $gym = $request->_managed_account->retrieveGym($gym); /** @var Gym $gym */
        $meet = $gym->retrieveMeet($meet); /** @var Meet $meet */

        if (!$meet->canBeEdited())
            throw new CustomBaseException('You cannot edit this meet.', -1);

        $meet->updateStepOne($attr);

        return redirect(route('gyms.meets.edit', [
            'gym' => $gym,
            'meet' => $meet,
            'step' => 1
        ]))->with('success', 'Changes saved.');
    }
    public function updateStepSix(Request $request, string $gym, string $meet)
    {
        $redirect = route('gyms.meets.edit', [
            'gym' => $gym,
            'meet' => $meet,
            'step' => 6
        ]);

        $attr = $request->all();
        $validator = Validator::make($attr, Meet::UPDATE_STEP_6_RULES);

        if ($validator->fails())
            return redirect($redirect)->withInput()->withErrors($validator);

        $gym = $request->_managed_account->retrieveGym($gym); /** @var Gym $gym */
        $meet = $gym->retrieveMeet($meet); /** @var Meet $meet */
        if (!$meet->canBeEdited())
            throw new CustomBaseException('You cannot edit this meet.', -1);

        $meet->updateStepSix($attr);

        return redirect($redirect)->with('success', 'Changes saved.') ;
    }
    public function updateStepTwo(Request $request, string $gym, string $meet)
    {
        $redirect = route('gyms.meets.edit', [
            'gym' => $gym,
            'meet' => $meet,
            'step' => 2
        ]);

        $attr = $request->all();
        $validator = Validator::make($attr, Meet::UPDATE_STEP_2_RULES);

        if ($validator->fails())
            return redirect($redirect)->withInput()->withErrors($validator);

        $gym = $request->_managed_account->retrieveGym($gym); /** @var Gym $gym */
        $meet = $gym->retrieveMeet($meet); /** @var Meet $meet */
        if (!$meet->canBeEdited())
            throw new CustomBaseException('You cannot edit this meet.', -1);

        $meet->updateStepTwo($attr);

        return redirect($redirect)->with('success', 'Changes saved.') ;
    }

    public function updateStepThree(Request $request, string $gym, string $meet)
    {
        $request->sanction_body_no = json_encode($request->sanction_body_no);
        $redirect = route('gyms.meets.edit', [
            'gym' => $gym,
            'meet' => $meet,
            'step' => 3
        ]);

        $attr = $request->all();
        $validator = Validator::make($attr, Meet::UPDATE_STEP_3_RULES);

        if ($validator->fails())
            return redirect($redirect)->withInput()->withErrors($validator);

        $gym = $request->_managed_account->retrieveGym($gym); /** @var Gym $gym */

        $meet = $gym->retrieveMeet($meet); /** @var Meet $meet */
        if (!$meet->canBeEdited())
            throw new CustomBaseException('You cannot edit this meet.', -1);

        $meet->updateStepThree($attr);

        return redirect($redirect)->with('success', 'Changes saved.') ;
    }

    public function updateStepFour(Request $request, string $gym, string $meet)
    {
        $redirect = route('gyms.meets.edit', [
            'gym' => $gym,
            'meet' => $meet,
            'step' => 4
        ]);
        
        $attr = $request->all();
        $validator = Validator::make($attr, Meet::getStepFourRules());
        
        if ($validator->fails())
        return redirect($redirect)->withInput()->withErrors($validator);
        

        $gym = $request->_managed_account->retrieveGym($gym); /** @var Gym $gym */

        $meet = $gym->retrieveMeet($meet); /** @var Meet $meet */
        if (!$meet->canBeEdited())
            throw new CustomBaseException('You cannot edit this meet.', -1);
        
        $meet->updateStepFour($attr); 

            
        if($meet->is_published == true){
            if (!empty($meet->schedule) && $meet->registrations()->count()){
                $schedule = json_decode($meet->schedule)->path;
                $registers = $meet->registrations()->get();
                $data = [];
                foreach ($registers as $register) {
                    $data['subject'] = 'Meet Schedule/Attachment Uploaded';
                    $data['gymName'] = $register->gym->name;
                    $data['meetName'] = $meet->name;
                    $data['meetStart'] = Carbon::parse($meet->start_date)->format('jS M Y');
                    $data['meetHost'] = $gym->name;
                    $data['attachments'] = $schedule;
                    Email::to($register->gym->user->email)
                    ->send(new MeetScheduleUploadedMailable('emails.registration.meet_schedule_uploaded',$data['subject'], $data));
                }
            }
        }

        return redirect($redirect)->with('success', 'Changes saved.') ;
    }

    public function updateStepFive(Request $request, string $gym, string $meet)
    {
        $redirect = route('gyms.meets.edit', [
            'gym' => $gym,
            'meet' => $meet,
            'step' => 5
        ]);

        $attr = $request->all();
        $validator = Validator::make($attr, Meet::UPDATE_STEP_5_RULES);

        if ($validator->fails())
            return redirect($redirect)->withInput()->withErrors($validator);

        $gym = $request->_managed_account->retrieveGym($gym); /** @var Gym $gym */

        $meet = $gym->retrieveMeet($meet); /** @var Meet $meet */
        if (!$meet->canBeEdited())
            throw new CustomBaseException('You cannot edit this meet.', -1);

        $meet->updateStepFive($attr);

        return redirect($redirect)->with('success', 'Changes saved.') ;
    }

    public function archive(Request $request, string $gym, string $meet)
    {
        $gym = $request->_managed_account->retrieveGym($gym); /** @var Gym $gym */
        $meet = $gym->retrieveMeet($meet); /** @var Meet $meet */
        if ($meet->toggleArchived(true))
            return back()->with('success', 'Your meet was archived.');
        else
            return back()->with('error', 'There was an error while archiving your meet.');
    }


    public function restore(Request $request, string $gym, string $meet)
    {
        $gym = $request->_managed_account->retrieveGym($gym); /** @var Gym $gym */
        $meet = $gym->retrieveMeet($meet, true); /** @var Meet $meet */
        if ($meet->toggleArchived(false))
            return back()->with('success', 'Your meet was restored.');
        else
            return back()->with('error', 'There was an error while restoring your meet.');
    }

    public function clearProfilePicture(Request $request, string $gym, string $meet)
    {
        $gym = $request->_managed_account->retrieveGym($gym); /** @var Gym $gym */
        $meet = $gym->retrieveMeet($meet); /** @var Meet $meet */
        if ($meet->clearProfilePicture())
            return back()->with('success', 'This Meet\'s picture was removed.');
        else
            return back()->with('error', 'There was an error while removing this Meet\'s  picture');
    }

    public function changeProfilePicture(Request $request, string $gym, string $meet)
    {
        $attr = request()->validate(Meet::getProfilePictureRules());
        $gym = $request->_managed_account->retrieveGym($gym); /** @var Gym $gym */
        $meet = $gym->retrieveMeet($meet); /** @var Meet $meet */
        if (!isset($attr['meet_picture']))
            return back();
        elseif ($meet->storeProfilePicture($attr['meet_picture']))
            return back()->with('success', 'This Meet\'s picture was updated.');
        else
            return back()->with('error', 'There was an error while updating this Meet\'s picture');
    }

    public function publish(Request $request, string $gym, string $meet)
    {
        $gym = $request->_managed_account->retrieveGym($gym); /** @var Gym $gym */
        $meet = $gym->retrieveMeet($meet); /** @var Meet $meet */
        if ($meet->togglePublished(true))
            return back()->with('success', 'Your meet was published.');
        else
            return back()->with('error', 'There was an error while publishing your meet.');
    }

    public function unpublish(Request $request, string $gym, string $meet)
    {
        $gym = $request->_managed_account->retrieveGym($gym); /** @var Gym $gym */
        $meet = $gym->retrieveMeet($meet); /** @var Meet $meet */
        if ($meet->togglePublished(false))
            return back()->with('success', 'Your meet was unpublished.');
        else
            return back()->with('error', 'There was an error while unpublishing your meet.');
    }

    public function hostMeetDashboard(Request $request, string $gym, string $meet)
    {
        try {
            $gym = $request->_managed_account->retrieveGym($gym); /** @var Gym $gym */
            $meet = $gym->retrieveMeet($meet); /** @var Meet $meet */

            $registerGyms = [];
            $registerGymsPending = [];

            foreach ($meet->registrations as  $registration) {
                $registerGyms[$registration->gym->id] = $registration->gym->name;
            }
            foreach ($meet->usag_reservations as  $usagReservation) {
                if($usagReservation->status == 1)
                {
                    $payload = $usagReservation->payload;
                    $registerGymsPending[$usagReservation->gym->id] = $usagReservation->gym->name;
                }
                // dd(json_encode($usagReservation->gym));
            }

            if (!$meet->is_published)
                throw new CustomBaseException("This meet is not published yet.", -1);

        } catch (CustomBaseException $e) {
            return redirect(route('dashboard'))->with('error', $e->getMessage());
        }

        $usagSanctions = $this->meetRepo->getMeetUsagSanction($meet->usag_meet_sanctions, $gym, $meet);
        $summaryData = $this->meetRepo->getSummaryData($meet);
        $massMailers = MassMailer::where('meet_id', $meet->id)->where('host', $gym->id)->get();
        foreach ($massMailers as $massMailer) {
            $massMailer->registered_gyms = json_decode($massMailer->registered_gyms);
            $massMailer->registered_gym_names = Gym::whereIn('id', $massMailer->registered_gyms)->pluck('name')->toArray();
            $massMailer->registered_gym_names = implode(', ', $massMailer->registered_gym_names);
        }
        // dd($massMailers);
        return view('host.meet.details', [
            'current_page' => self::_get_page_name($gym),
            'gym' => $gym,
            'meet' => $meet,
            'cc_fees' => $meet->cc_fee(),
            'registerGyms' =>  $registerGyms,
            'registerGymsPending' =>  $registerGymsPending,
            'usagSanctions' => $usagSanctions,
            'summaryData' => $summaryData,
            'massMailers' => $massMailers
        ]);
    }

    public function hostReportCreate(Request $request, string $hostingGym, string $meet,
        string $reportType, string $gym = null)
    {
        try {
            // throw new CustomBaseException('WiP');
            $host = $request->_managed_account; /** @var User $user */
            $hostingGym = $host->retrieveGym($hostingGym); /** @var Gym $gym */
            $meet = $hostingGym->retrieveMeet($meet); /** @var Meet $meet */

            if ($gym !== null) {
                $registration = $meet->registrations()->where('gym_id', $gym)->first(); /** @var MeetRegistration $registration */
                if ($registration == null)
                    throw new CustomBaseException('No such gym registered in this meet.');

                $gym = $registration->gym;
            }

            $pdf = null;
            $name = Str::slug($meet->name, '_') . '_' . $reportType . '.pdf';

            switch ($reportType) {
                case Meet::REPORT_TYPE_SUMMARY:
                    $pdf = $meet->generateSummaryReport()->setPaper('a4', 'landscape')
                        ->setOption('margin-top', '40mm')
                        ->setOption('margin-bottom', '10mm')
                        ->setOption('header-html', view('PDF.host.meet.reports.header_footer.meet_summery_header',['meet' => $meet])->render())
                        ->setOption('footer-html', view('PDF.host.meet.reports.header_footer.common_footer')->render());

                    return $pdf->stream($name);
                    break;

                case Meet::REPORT_TYPE_ENTRY_NOT_ATHLETE:
                    $notAthlete = true;
                    $pdf = $meet->generateEntryReport($gym)->setPaper('a4', 'landscape')
                        ->setOption('margin-top', '40mm')
                        ->setOption('margin-bottom', '10mm')
                        ->setOption('header-html', view('PDF.host.meet.reports.header_footer.team_summary_header',['meet' => $meet])->render())
                        ->setOption('footer-html', view('PDF.host.meet.reports.header_footer.common_footer')->render());

                    return $pdf->stream($name);
                    break;

                case Meet::REPORT_TYPE_COACHES:
                    $pdf = $meet->generateGymRegistrationReport($gym)->setPaper('a4')
                        ->setOption('margin-top', '10mm')
                        ->setOption('margin-bottom', '10mm')
                        ->setOption('footer-html', view('PDF.host.meet.reports.header_footer.common_footer')->render());

                    return $pdf->stream($name);
                    break;

                case Meet::REPORT_TYPE_SPECIALISTS:
                    // written by Palash
                    $pdf = $meet->generateEventSpecialistReport($gym)->setPaper('a4')
                        ->setOption('margin-top', '10mm')
                        ->setOption('margin-bottom', '10mm')
                        ->setOption('footer-html', view('PDF.host.meet.reports.header_footer.common_footer')->render());

                    return $pdf->stream($name);
                    break;
                case Meet::REPORT_TYPE_SPECIALISTS_BY_LEVEL:
                    $pdf = $meet->generateEventSpecialistReport($gym, 1)->setPaper('a4')
                        ->setOption('margin-top', '10mm')
                        ->setOption('margin-bottom', '10mm')
                        ->setOption('footer-html', view('PDF.host.meet.reports.header_footer.common_footer')->render());

                    return $pdf->stream($name);
                    break;
                case Meet::REPORT_TYPE_REFUNDS:
                    // Commented previous function and execute function written by Palash
                     $pdf = $meet->generateRefundReport($gym)->setPaper('a4')
                         ->setOption('margin-top', '10mm')
                         ->setOption('margin-bottom', '10mm')
                         ->setOption('footer-html', view('PDF.host.meet.reports.header_footer.common_footer')->render());
//                    $pdf = $meet->generateScratchReport($gym)->setPaper('a4');

                    return $pdf->stream($name);
                    break;

                case Meet::REPORT_TYPE_PROSCOREEXPORT:
                    $registrationAthlete = RegistrationAthlete::athlete_meet($meet->id, '')->get();
                    $registrationSpecialist = RegistrationSpecialist::athlete_meet($meet->id, '')->get();
                    $data['data'] =  RegistrationAthlete::athlete_meet_data_for_csv($registrationAthlete, $registrationSpecialist);
                    $data['headings'] = ["First_Name", "Last_Name", "Gym", "Event Category", "Specialist Events", "Level", "Birthday", "USAG", "Session", "Flight", "Squad","Team1","Team2", "Team3", "TSize", "USCitizen", "Scratched", "AltID" ];
                    $name = 'ProScoreExport_'.Str::slug($meet->name, '_') . '.csv';
                    return Excel::download(new CsvExport($data), $name);
                    break;

                case Meet::REPORT_TYPE_MEETENTRY:
                    $pdf = $meet->generateMeetEntryReport($gym, false)->setPaper('a4')
                    ->setOption('margin-top', '10mm')
                    ->setOption('margin-bottom', '10mm')
                    ->setOption('footer-html', view('PDF.host.meet.reports.header_footer.common_footer')->render());

                    return $pdf->stream($name);
                    break;

                case Meet::REPORT_TYPE_SCRATCH:
                    // Priviously exists that why new case written by Palash
                    $pdf = $meet->generateScratchReport($gym)->setPaper('a4')
                        ->setOption('margin-top', '10mm')
                        ->setOption('margin-bottom', '10mm')
                        ->setOption('footer-html', view('PDF.host.meet.reports.header_footer.common_footer')->render());

                    return $pdf->stream($name);
                    break;

                case Meet::REPORT_TYPE_REGISTRATION_DETAIL:
                    $pdf = $meet->generateRegistrationDetailReport($gym)->setPaper('a4')
                        ->setOption('margin-top', '10mm')
                        ->setOption('margin-bottom', '10mm')
                        ->setOption('footer-html', view('PDF.host.meet.reports.header_footer.common_footer')->render());

                    return $pdf->stream($name);
                    break;
                case Meet::REPORT_TYPE_USAIGC_COACHES_SIGN_IN:
                    $pdf = $meet->generateUSAIGCCoachSignInReport($gym)->setPaper('a4')
                        ->setOption('margin-top', '10mm')
                        ->setOption('margin-bottom', '10mm')
                        ->setOption('footer-html', view('PDF.host.meet.reports.header_footer.common_footer')->render());

                    return $pdf->stream($name);
                    break;
                case Meet::REPORT_TYPE_NGA_COACHES_SIGN_IN:
                    $pdf = $meet->generateNGACoachSignInReport($gym)->setPaper('a4')
                        ->setOption('margin-top', '10mm')
                        ->setOption('margin-bottom', '10mm')
                        ->setOption('footer-html', view('PDF.host.meet.reports.header_footer.common_footer')->render());

                    return $pdf->stream($name);
                    break;
                case Meet::REPORT_TYPE_EVENT_SPECIALIST:
                    // Priviously exists that why new case written by Palash
                    $pdf = $meet->generateEventSpecialistReport($gym)->setPaper('a4')
                        ->setOption('margin-top', '10mm')
                        ->setOption('margin-bottom', '10mm')
                        ->setOption('footer-html', view('PDF.host.meet.reports.header_footer.common_footer')->render());

                    return $pdf->stream($name);
                    break;
                case Meet::REPORT_TYPE_GYM_NAME_LABEL:
                    $pdf = $meet->generateGymNameLabelReport($gym)->setPaper('a4')
                        ->setOption('margin-top', '20mm')
                        ->setOption('margin-bottom', '20mm');

                    return $pdf->stream($name);
                    break;
                case Meet::REPORT_TYPE_LEO_T_SHIRT:
                    $pdf = $meet->generateLeoTShirtReport($gym)->setPaper('a4')
                        ->setOption('margin-top', '10mm')
                        ->setOption('margin-bottom', '10mm')
                        ->setOption('footer-html', view('PDF.host.meet.reports.header_footer.common_footer')->render());

                    return $pdf->stream($name);
                    break;

                case Meet::REPORT_TYPE_LEO_T_SHIRT_GYM:
                    $pdf = $meet->generateLeoTShirtGymReport($gym)->setPaper('a4')
                        ->setOption('margin-top', '10mm')
                        ->setOption('margin-bottom', '10mm')
                        ->setOption('footer-html', view('PDF.host.meet.reports.header_footer.common_footer')->render());

                    return $pdf->stream($name);
                    break;
                case Meet::REPORT_TYPE_COACHES_NAME_LABEL:
                    $pdf = $meet->generateCoachesNameLabelReport($gym)->setPaper('a4')
                        ->setOption('margin-top', '40mm')
                        ->setOption('margin-bottom', '40mm');
                    return $pdf->stream($name);
                    break;
                case Meet::REPORT_TYPE_GYM_MAILING_LABEL:
                    $pdf = $meet->generateGymMailingLabelReport()->setPaper('a4')
                        ->setOption('margin-top', '20mm')
                        ->setOption('margin-bottom', '10mm');
                    return $pdf->stream($name);
                    break;
                case Meet::REPORT_TYPE_REGISTRATION_QR:
                    $pdf = $meet->generateRegistrationQR()->setPaper('a4')
                        ->setOption('margin-top', '10mm')
                        ->setOption('margin-bottom', '10mm')
                        ->setOption('footer-html', view('PDF.host.meet.reports.header_footer.common_footer')->render());
                    return $pdf->stream($name);
                    break;
                default:
                    throw new CustomBaseException("Invalid report type.", 1);
            }

            //return view('PDF.host.meet.reports.refund', $data);
            // throw new CustomBaseException('WiP');
            $repsonse = $pdf->download($name); /** @var Response $response */

            return $repsonse;
        } catch(CustomBaseException $e) {
            throw $e;
        } catch(\Throwable $e) {
            Log::warning(self::class . '@hostReportCreate : ' . $e->getMessage(), [
                'Throwable' => $e
            ]);
            throw new CustomBaseException('Something went wrong while generating your report.', Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function sendMassNotification(Request $request)
    {
        $input = $request->all();
        $meet = Meet::find($input['meet_id']);
        $gymEmails = Gym::with('user')->whereIn('id',$input['registerGym'])->get()->pluck('user.email');
        if (isset($input['attachments']) && ! empty($input['attachments'])) {
            $input['attachments'] = Storage::url(Storage::putFile('public/'.Meet::MEET_MASS_MAILER,$input['attachments']));
        }
        $input['attachments'] = (isset($input['attachments'])) ? $input['attachments'] : null; # correction needed
        // $input['attachments'] = str_replace('/storage', 'storage', $input['attachments']);
        if(!$meet){
            throw new CustomBaseException("Meet not found.");
        }
        if(count($meet['registrations']) < 0){
            throw new CustomBaseException("No gym is registered for this meet.");
        }

        MassMailer::create([
            'host' => $meet->gym_id,
            'meet_id' => $meet->id,
            'registered_gyms' => json_encode($input['registerGym']),
            'subject' => $input['subject'],
            'message' => $input['message'],
            'attachments' => $input['attachments'],
        ]);

        // dd($input); die();
        try {
            foreach ($gymEmails as $gymEmail) {
                dispatch(new SendEmailJob($gymEmail,'emails.mass_mailer_notification',$input['subject'], $input));
                // Email::to($gymEmail)
                //     ->send(new MassMailerNotification('emails.mass_mailer_notification',$input['subject'], $input));
            }

            return redirect(url()->previous())->with('success','Mail notification send successfully.');

        } catch (CustomBaseException $e){
            throw $e;
        }

    }

    public function getLineChartData(Meet $meet)
    {
        $data = $this->meetRepo->getMeetLineChartData($meet);

        return $this->sendResponse($data, "Retrieved Meet Line chart Data.");
    }

    public function getBarChartData(Meet $meet)
    {
        $data = $this->meetRepo->getMeetBarChartData($meet);

        return $this->sendResponse($data, "Retrieved Meet Bar chart Data.");
    }

    public function getPieChartData(Meet $meet)
    {
        $data = $this->meetRepo->getMeetPieChartData($meet);

        return $this->sendResponse($data, "Retrieved Meet Pie chart Data.");
    }

    public function printCheckSendingDetails($meetId, $gymId)
    {
        try {
            $pdf = $this->meetRepo->printCheckSendingDetails($meetId, $gymId)->setPaper('a4')
                ->setOption('margin-top', '10mm')
                ->setOption('margin-bottom', '10mm')
                ->setOption('footer-html', view('PDF.host.meet.reports.header_footer.common_footer')->render());
        } catch(\Exception $e) {
            return $this->error([
                'message' => $e->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        return $pdf->stream('Check sending details.'.time().'.pdf');
    }

    public function getAthleteSummaryReport(Meet $meet)
    {
        $registrations = $meet->registrations()->with(['athletes'])->get();
        $athArr = [];
        $finalSortingArray = [];
        $genderArray = [];
        foreach ($registrations as $registration) {
            //athlete summary
            foreach ($registration->athletes as $athlete) {
                $lName = Str::after($athlete->registration_level->level->name, 'Level');
                $gender = ($athlete->registration_level->level->level_category->female) ? 'Female' : 'Male';
                $levelName = $athlete->registration_level->level->sanctioning_body->initialism . '(Gymnastics) : ' . $athlete->registration_level->level->name . ' - ' . $gender;
                $indexArray[$levelName] = isset($indexArray[$levelName]) ? $indexArray[$levelName] + 1 : 1;
                $athArr[$levelName] = $indexArray[$levelName];
                $genderArray[$levelName] = $gender;

                $finalSortingArray[$levelName] = $lName;
            }
        }

        $levels = [];
        $sortedArray = collect($genderArray)->sortBy(function ($key, $record) use (&$levels) {
            if (Str::contains($record, 'Level')) {
                $levels[$key]['levels'][] = $record;
            } else {
                $levels[$key]['other'][] = $record;
            }
            if ($key == 'Female') {
                return -1;
            }

            return 0;
        });

        //prepare female data
        $femaleData = [];
        $final1 = [];
        $array1 = [];
        if(isset($levels['Female'])) {
            if(isset($levels['Female']['levels'])) {
                foreach ($levels['Female']['levels'] as $level) {
                    $femaleData['Female']['levels'][$finalSortingArray[$level]] = $level;
                }
                ksort($femaleData['Female']['levels']);
                $array1 = $femaleData['Female']['levels'];
            }

            $finalArray1 = array_merge($array1, isset($levels['Female']['other']) ? $levels['Female']['other'] : []);
            $index = 1;
            foreach ($finalArray1 as $item) {
                $final1[$index] = $item;
                $index++;
            }
        }

        //prepare male data
        $maleData = [];
        $final2 = [];
        $array2 = [];
        if(isset($levels['Male'])){
            if(isset($levels['Male']['levels'])) {
                foreach ($levels['Male']['levels'] as $level) {
                    $maleData['Male']['levels'][$finalSortingArray[$level]] = $level;
                }
                ksort($maleData['Male']['levels']);
                $array2 = $maleData['Male']['levels'];
            }
            $finalArray2 = array_merge($array2, isset($levels['Male']['other']) ? $levels['Male']['other'] : []);

            foreach ($finalArray2 as $item) {
                $final2[$index] = $item;
                $index++;
            }
        }

        $finalArray = array_merge($final1, $final2);

        $result = [];
        foreach ($finalArray as $key => $value) {
            $result[$value] = $athArr[$value];
        }

        $data['athleteLevelArr'] = $result;
        $data['meet'] = $meet;

        /** @var PdfWrapper $pdfView */
        $pdfView = \PDF::loadView('host.meet.details.summary_PDF.athlete_summary', $data);

        $pdf = $pdfView->setPaper('a4')
            ->setOption('margin-top', '10mm')
            ->setOption('margin-bottom', '10mm')
            ->setOption('footer-html', view('PDF.host.meet.reports.header_footer.common_footer')->render());

        return $pdf->stream('Meet Athletes Summary_' . time() . '.pdf');
    }

    public function getCoachSummaryReport(Meet $meet)
    {
        $registrations = $meet->registrations()->with(['athletes'])->get();
        $coachArr = [];
        foreach ($registrations as $key => $registration) {
            //coach summary
            $coachArr[$key]['coach'] = [];
            foreach ($registration->coaches as $coach) {
                $gymName = $registration['gym']->name;
                $indexArray[$gymName] = isset($indexArray[$gymName]) ? $indexArray[$gymName] + 1 : 1;
                $coachArr[$key]['gym'][$registration['gym']->name] = $indexArray[$gymName];

                if (isset($coachArr[$key]['coach']) && (!in_array($coach->first_name . ' '.$coach->last_name, $coachArr[$key]['coach']))) {
                    $coachArr[$key]['coach'][] = $coach->first_name . ' ' . $coach->last_name;
                }
            }
        }

        $data['coachSummaryArr'] = $coachArr;
        $data['meet'] = $meet;

        /** @var PdfWrapper $pdfView */
        $pdfView = \PDF::loadView('host.meet.details.summary_PDF.coach_summary', $data);

        $pdf = $pdfView->setPaper('a4')
            ->setOption('margin-top', '10mm')
            ->setOption('margin-bottom', '10mm')
            ->setOption('footer-html', view('PDF.host.meet.reports.header_footer.common_footer')->render());

        return $pdf->stream('Meet Coaches Summary_' . time() . '.pdf');
    }

    public function getGymSummaryReport(Meet $meet)
    {
        $registrations = $meet->registrations()->with(['athletes'])->get();
        $gymArr = [];

        foreach ($registrations as $key => $registration) {
            $gymArr[$key]['gym'] = $registration['gym'];

            $gymArr[$key]['coach'] = [];
            foreach ($registration->coaches as $coach) {
                if (isset($gymArr[$key]['coach']) && (!in_array($coach->first_name . ' '.$coach->last_name, $gymArr[$key]['coach']))) {
                    $gymArr[$key]['coach'][] = $coach->first_name . ' ' . $coach->last_name;
                }
            }
        }

        $data['gymSummaryArr'] = $gymArr;
        $data['meet'] = $meet;

        /** @var PdfWrapper $pdfView */
        $pdfView = \PDF::loadView('host.meet.details.summary_PDF.gym_summary', $data);

        $pdf = $pdfView->setPaper('a4')
            ->setOption('margin-top', '10mm')
            ->setOption('margin-bottom', '10mm')
            ->setOption('footer-html', view('PDF.host.meet.reports.header_footer.common_footer')->render());

        return $pdf->stream('Meet Gym Summary_' . time() . '.pdf');
    }

    /**
     * @param  Request  $request
     *
     * @return JsonResponse
     */
    public function sendMailToPastMeets(Request $request):JsonResponse
    {
        $input = $request->all();
        $current_meet = Meet::with('gym')->find($input['meet_id']);
        $meets = Meet::with('gym.user', 'registrations.gym.user')->whereIn('id',$input['meets'])->get();
        $data = [];
        foreach ($meets as $meet) {
            $data['host_club'] = $current_meet->gym->name;
            $data['meet_name'] = $current_meet->name;
            $data['details_link'] = route('gyms.meets.details', ['meet' => $input['meet_id']]);
            $data['subject'] = $current_meet->name.' is now open for Registration';
            foreach ($meet->registrations as $meetRegistration) {
                $data['meet_registration_gym'] = $meetRegistration->gym->name;
                Email::to($meetRegistration->gym->user->email)
                    ->send(new PastMeetsGymsNotification('emails.past_meets_gym_notification',$data['subject'], $data));
            }
        }

        return $this->sendSuccess('Successfully invite past registrants');
    }
}