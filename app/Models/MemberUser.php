<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

class MemberUser extends Pivot
{
    public const PIVOT_FIELDS_OF_INTEREST = [
        'can_manage_gyms' => 'Manage Gyms',
        'can_manage_rosters' => 'Manage Rosters',
        'can_create_meet' => 'Create Meets',
        'can_edit_meet' => 'Modify Meets',
        'can_register_in_meet' => 'Create and Modify Registrations',
        'can_email_participant' => 'Email Participants',
        'can_email_host' => 'Email Hosts',
        'can_access_reports' => 'Access Reports',
        'should_be_cced' => 'CC\'ed on Emails'
    ];

    public static function getPivotFieldsRules() {
        $result = [];
        foreach (self::PIVOT_FIELDS_OF_INTEREST as $fieldName => $description) {
            $result[$fieldName] = ['sometimes', 'accepted'];
        }
        return $result;
    }

    public static function getPivotFieldsOfInterest() {
        return array_keys(self::PIVOT_FIELDS_OF_INTEREST);
    }

    public function can(string $permisison) {
        return $this->attributes[$permisison];
    }

    public function set(string $permisison, bool $value) {
        return $this->attributes[$permisison] = $value;
    }

    public function permissions()
    {
        $result = [];
        $count = 0;
        foreach (self::PIVOT_FIELDS_OF_INTEREST as $fieldName => $description) {
            $value = $this->can($fieldName);
            $result[$fieldName] = [
                'description' => $description,
                'value' => $value
            ];
            
            if ($value)
                $count++;
        }

        return [
            'permissions' => $result,
            'active_count' => $count
        ];
    }

    public function shouldShowSidebarGymSection() {
        return $this->can_manage_gyms || $this->can_manage_rosters ||
            $this->can_create_meet || $this->can_edit_meet ||
            $this->can_email_participant || $this->can_email_host ||
            $this->can_access_reports;
    }
}
