<?php

use Illuminate\Database\Seeder;
use App\Models\ErrorCodeCategory;

class ErrorCodeTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $this->seedGeneralErrors();
        $this->seedStripeErrors();
        $this->seedDwollaErrors();
        $this->seedPayPalErrors();

    }

    private function seedGeneralErrors() {
        $category = ErrorCodeCategory::where('name', 'General')->first();
        $baseCode = ErrorCodeCategory::getCategoryBase('General');

        $category->errorCodes()->createMany([
            [
                'code' => $baseCode,
                'description' => 'Generic / Unknown error.'
            ],
            [
                'code' => $baseCode + 1,
                'description' => 'JSON Decode failure.'
            ],
            [
                'code' => $baseCode + 2,
                'description' => 'Audit entry failure.'
            ],
            [
                'code' => $baseCode + 3,
                'description' => 'The user who sent this invitation could not be retreived.'
            ],
            [
                'code' => $baseCode + 4,
                'description' => 'File I/O failed.'
            ],
        ]);
    }

    private function seedStripeErrors() {
        $category = ErrorCodeCategory::where('name', 'Stripe')->first();
        $baseCode = ErrorCodeCategory::getCategoryBase('Stripe');
        $category->errorCodes()->createMany([
            [
                'code' => $baseCode,
                'description' => 'Generic / Unknown error.'
            ],
            [
                'code' => $baseCode + 1,
                'description' => 'The requested ressource is missing. Depending on context, ' . 
                'this might be the customer itself, his cards, his charges, and so on.'
            ],
        ]);
    }

    private function seedDwollaErrors() {
        $category = ErrorCodeCategory::where('name', 'Dwolla')->first();
        $baseCode = ErrorCodeCategory::getCategoryBase('Dwolla');
        $category->errorCodes()->createMany([
            [
                'code' => $baseCode,
                'description' => 'Generic / Unknown error.'
            ],
            [
                'code' => $baseCode + 1,
                'description' => 'API OAuth Authentication failed.'
            ],
            [
                'code' => $baseCode + 2,
                'description' => 'An account with this email already exists.'
            ],
            [
                'code' => $baseCode + 3,
                'description' => 'The linked customer account could not be found.'
            ],
            [
                'code' => $baseCode + 4,
                'description' => 'The supplied API keys are not authorized to access the ressource.'
            ],
            [
                'code' => $baseCode + 5,
                'description' => 'This account is not allowed to modify the ressource.'
            ],
            [
                'code' => $baseCode + 6,
                'description' => 'Data validation error.'
            ],
            [
                'code' => $baseCode + 7,
                'description' => 'The request body contains bad syntax or is incomplete'
            ],
            [
                'code' => $baseCode + 8,
                'description' => 'The requested ressource could not be found'
            ],
            [
                'code' => $baseCode + 9,
                'description' => 'Micro-deposits have not have not settled to destination bank. A Customer can verify these amounts after micro-deposits have processed to their bank'
            ],
            [
                'code' => $baseCode + 10,
                'description' => 'Invalid or wrong micro-deposit amounts provided.'
            ],
            [
                'code' => $baseCode + 11,
                'description' => 'Too many attempts to verify micro-deposits, or ths bank account is already verified.'
            ],
            [
                'code' => $baseCode + 999,
                'description' => 'A Dwolla server error occured.'
            ],
        ]);
       
    }

    private function seedPayPalErrors() {
        $category = ErrorCodeCategory::where('name', 'PayPal')->first();
        $baseCode = ErrorCodeCategory::getCategoryBase('PayPal');
        $category->errorCodes()->create([
            'code' => $baseCode,
            'description' => 'Generic / Unknown error.'
        ]);
    }
}
