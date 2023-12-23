<?php

namespace App\Http\Livewire;

use App\Models\User;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Livewire\Component;
use Livewire\WithPagination;
use function foo\func;

/**
 * Class AddConversionUser
 */
class AddConversionUser extends Component
{
    use WithPagination;

    /**
     * @var string
     */
    public $users, $searchUser = '';

    protected $listeners = ['refresh' => '$refresh', 'searchMembers', 'clearMembers'];

    public function searchMembers($searchMembers)
    {
        $this->searchUser = $searchMembers;
        $this->users = $this->searchUsers();
    }

    public function clearMembers()
    {
        $this->searchUser = '';
        $this->users = $this->searchUsers();
    }

    /**
     * @return Application|Factory|View
     */
    public function render()
    {
        $this->users = $this->searchUsers();

        return view('livewire.add-conversion-user');
    }

    /**
     * @return mixed
     */
    public function searchUsers()
    {
        /** @var User $currentUser */
        $currentUser = User::with(['gyms.meets.registrations'])->where('id', Auth::id())->get();
        $meetRegistrationGymIds = $currentUser->pluck('gyms')->collapse()
            ->pluck('meets')->collapse()
            ->pluck('registrations')->collapse()->unique('gym_id')->pluck('gym_id')->toArray();
        /** @var User $users */
        $query = User::whereHas('gyms', function ($query) use ($meetRegistrationGymIds) {
            $query->whereIn('id', $meetRegistrationGymIds);
        });

        $query->when(isset($this->searchUser) && $this->searchUser != '', function (Builder $query) {
            $query->where(function (Builder $query) {
                $query->orWhere('first_name', 'like', '%'.strtolower($this->searchUser).'%')
                    ->orWhere('last_name', 'like', '%'.strtolower($this->searchUser).'%')
                    ->orWhere('email', 'like', '%'.strtolower($this->searchUser).'%');
            });
        });

        return $query->get();
    }
}
