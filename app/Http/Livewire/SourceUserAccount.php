<?php

namespace App\Http\Livewire;

use App\Exceptions\CustomDwollaException;
use App\Models\User;
use App\Services\DwollaService;
use Illuminate\Support\Facades\Log;
use Livewire\Component;

class SourceUserAccount extends Component
{
    public $userId = '';
    protected $listeners = ['filterSource'];

    public function filterSource($userId)
    {
        $this->userId = $userId;
    }

    /**
     * @throws CustomDwollaException
     */
    public function render(bool $throw = true, bool $removed = false)
    {
       $sourceBankAccounts = $this->getUserBankAccounts();

        return view('livewire.source-user-account', compact('sourceBankAccounts'));
    }

    public function getUserBankAccounts(bool $throw = true, bool $removed = false)
    {
        try {
            if (!empty($this->userId)) {
                $user = User::find($this->userId);
                $sourceBankAccounts = resolve(DwollaService::class)->listFundingSources($user->dwolla_customer_id);
                if (count($sourceBankAccounts) < 1)
                    $sourceBankAccounts = [];
            } else {
                $sourceBankAccounts = [];
            }

            return $sourceBankAccounts;
        } catch (CustomDwollaException $e) {
            Log::error($e->getMessage());
            $result = new CustomDwollaException(
                'You cannot make changes to your linked bank accounts for the time being.' .
                ' Please contact us as soon as possible.', $e->getCode(), $e);

            $this->dispatchBrowserEvent('source-error', ['error' => $result]);
        }
    }
}
