<?php

namespace App\Http\Controllers;

use App\Models\Gym;
use Illuminate\Http\Request;
use App\Exceptions\CustomBaseException;
use App\Models\State;
use App\Models\Country;
use App\Helper;
use App\Models\Setting;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Validator;

class GymController extends Controller
{
    public const PAGE_NAME = 'my-gyms';

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $activeGyms =  $request->_managed_account->gyms()
                            ->where('is_archived', false)
                            ->orderBy('name', 'ASC')->get();

        $archivedGyms =  $request->_managed_account->gyms()
                            ->where('is_archived', true)
                            ->orderBy('name', 'ASC')->get();
        return view('gym.list', [
            'current_page' => self::PAGE_NAME,
            'active_gyms' => $activeGyms,
            'archived_gyms' => $archivedGyms
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $states = State::all();
        if ($states->count() < 1)
            throw new CustomBaseException('Failed to fetch states list. Pleace contact us.');

        $countries = Country::all();
        if ($countries->count() < 1)
            throw new CustomBaseException('Failed to fetch countries list. Pleace contact us.');

        return view('gym.create', [
            'current_page' => self::PAGE_NAME,
            'states' => $states,
            'countries' => $countries
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $attr = $request->all();
        if (isset($attr['website']))
            $attr['website'] = Helper::dummyProofUrl($attr['website']);

        $attr = Validator::make($attr, Gym::CREATE_RULES)->validate();

        $gym = $request->_managed_account->createGym($attr);

        return redirect(route('gyms.index'))->with('success', 'Gym "' . $gym->name . '" successfully created.');
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Gym  $gym
     * @return \Illuminate\Http\Response
     */
    public function show(string $gym, Request $request)
    {
        $gym = $request->_managed_account->retrieveGym($gym, true);
        return view('gym.view', [
            'current_page' => 'gym-' . $gym->id,
            'gym' => $gym,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Gym  $gym
     * @return \Illuminate\Http\Response
     */
    public function edit(string $gym, Request $request)
    {
        $gym = $request->_managed_account->retrieveGym($gym);

        $states = State::all();
        if ($states->count() < 1)
            throw new CustomBaseException('Failed to fetch states list. Pleace contact us.');

        $countries = Country::all();
        if ($countries->count() < 1)
            throw new CustomBaseException('Failed to fetch countries list. Pleace contact us.');

        return view('gym.edit', [
            'current_page' => 'gym-' . $gym->id,
            'gym' => $gym,
            'states' => $states,
            'countries' => $countries,
            'profile_picture_max_size' => Helper::formatByteSize(Setting::profilePictureMaxSize() * 1024),
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Gym  $gym
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, string $gym)
    {
        $attr = $request->all();
        if (isset($attr['website']))
            $attr['website'] = Helper::dummyProofUrl($attr['website']);

        $attr = Validator::make($attr, Gym::UPDATE_RULES)->validate();

        if ($request->_managed_account->retrieveGym($gym)->updateProfile($attr))
            return back()->with('success', 'Your gym\'s details were updated.');
        else
            return back()->with('error', 'There was an error while updating gym\'s details.');
    }

    /**
     * Archive the specified resource.
     *
     * @param  \App\Models\Gym  $gym
     * @return \Illuminate\Http\Response
     */
    public function destroy(string $gym, Request $request)
    {
        if ($request->_managed_account->retrieveGym($gym)->toggleArchived(true))
            return back()->with('success', 'Your gym was archived.');
        else
            return back()->with('error', 'There was an error while archiving your gym.');
    }

    /**
     * Restore the specified resource from the archive.
     *
     * @param  \App\Models\Gym  $gym
     * @return \Illuminate\Http\Response
     */
    public function restore(string $gym, Request $request)
    {
        if ($request->_managed_account->retrieveGym($gym, true)->toggleArchived(false))
            return back()->with('success', 'Your gym was restored.');
        else
            return back()->with('error', 'There was an error while restoring your gym.');
    }

    public function clearProfilePicture(string $gym, Request $request)
    {
        if ($request->_managed_account->retrieveGym($gym)->clearProfilePicture())
            return back()->with('success', 'This Gym\'s picture was removed.');
        else
            return back()->with('error', 'There was an error while removing this Gym\'s  picture');
    }

    public function changeProfilePicture(string $gym, Request $request)
    {
        $attr = request()->validate(Gym::getProfilePictureRules());
        if (!isset($attr['gym_picture']))
            return back();
        elseif ($request->_managed_account->retrieveGym($gym)->storeProfilePicture($attr['gym_picture']))
            return back()->with('success', 'This Gym\'s picture was updated.');
        else
            return back()->with('error', 'There was an error while updating this Gym\'s picture');
    }

    public function joinedMeets(Request $request, string $gym)
    {
        $gym = $request->_managed_account->retrieveGym($gym); /** @var Gym $gym */

        return view('gym.joined', [
            'current_page' => 'gym-' . $gym->id,
            'gym' => $gym
        ]);
    }
}
