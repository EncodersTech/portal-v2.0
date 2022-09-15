<?php

namespace App\Http\Livewire;

use App\Exceptions\CustomDwollaException;
use App\Models\User;
use App\Services\DwollaService;
use Illuminate\Support\Facades\Log;
use Livewire\Component;

class DestinationUserAccount extends Component
{
    public $userId = '';
    protected $listeners = ['filterDestination'];

    public function filterDestination($userId)
    {
        $this->userId = $userId;
    }

    public function render(bool $throw = true, bool $removed = false)
    {
        $destinationBankAccounts = $this->getUserBankAccounts();

        return view('livewire.destination-user-account', compact('destinationBankAccounts'));
    }

    public function getUserBankAccounts(bool $throw = true, bool $removed = false)
    {
        try {
            if (!empty($this->userId)) {
                $user = User::find($this->userId);
                $destinationBankAccounts = resolve(DwollaService::class)->listFundingSources($user->dwolla_customer_id);
                if (count($destinationBankAccounts) < 1)
                    $destinationBankAccounts = [];
            } else {
                $destinationBankAccounts = [];
            }
//dd($destinationBankAccounts);
            return $destinationBankAccounts;
        } catch (CustomDwollaException $e) {
            Log::error($e->getMessage());
            $result = new CustomDwollaException(
                'You cannot make changes to your linked bank accounts for the time being.' .
                ' Please contact us as soon as possible.', $e->getCode(), $e);

            $this->dispatchBrowserEvent('destination-error', ['error' => $result]);
        }
    }
}
