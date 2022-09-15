<?php

use Illuminate\Database\Seeder;
use App\Models\AuditEventCategory;
use App\Models\AuditEventType;

class AuditEventTypeTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $this->seedFundingSourceTypes();
        $this->seedMemberManagementTypes();
        $this->seedGymManagementTypes();
        $this->seedRosterManagementTypes();
        $this->seedMeetManagementTypes();
        $this->seedMeetRegistrationTypes();
        /*$this->seedEmailingTypes();*/
    }


    
    private function seedFundingSourceTypes() {
        $category = AuditEventCategory::find(AuditEventCategory::CATEGORY_FUNDING_SOURCES);
        
        $category->types()->createMany([
            [
                'id' => AuditEventType::TYPE_CARD_LINK,
                'name' => 'Card Linked'
            ],
            [
                'id' => AuditEventType::TYPE_CARD_UNLINK,
                'name' => 'Card Unlinked'
            ],
            [
                'id' => AuditEventType::TYPE_BANK_LINK,
                'name' => 'Bank Account Linked'
            ],
            [
                'id' => AuditEventType::TYPE_BANK_VERIFIED,
                'name' => 'Bank Account Verified (Micro Deposit)'
            ],
            [
                'id' => AuditEventType::TYPE_BANK_UNLINK,
                'name' => 'Bank Account Unlinked'
            ],
        ]);
    }

    private function seedMemberManagementTypes() {
        $category = AuditEventCategory::find(AuditEventCategory::CATEGORY_MEMBER_MANAGEMENT);
        
        $category->types()->createMany([
            [
                'id' => AuditEventType::TYPE_MEMBER_ADDED,
                'name' => 'Member Added'
            ],
            [
                'id' => AuditEventType::TYPE_MEMBER_PERMISSIONS_CHANGED,
                'name' => 'Member Permissions Changed'
            ],
            [
                'id' => AuditEventType::TYPE_MEMBER_REMOVED,
                'name' => 'Member Removed'
            ],
            [
                'id' => AuditEventType::TYPE_REMOVED_SELF_FROM_ACCOUNT,
                'name' => 'Removed Self From Managed Account'
            ],
        ]);
    }

    private function seedGymManagementTypes() {
        $category = AuditEventCategory::find(AuditEventCategory::CATEGORY_GYM_MANAGEMENT);
        
        $category->types()->createMany([
            [
                'id' => AuditEventType::TYPE_GYM_CREATED,
                'name' => 'Gym Created'
            ],
            [
                'id' => AuditEventType::TYPE_GYM_UPDATED,
                'name' => 'Gym Info Updated'
            ],
            [
                'id' => AuditEventType::TYPE_GYM_ARCHIVED,
                'name' => 'Gym Archived'
            ],
            [
                'id' => AuditEventType::TYPE_GYM_RESTORED,
                'name' => 'Gym Restored'
            ],
        ]);
    }

    private function seedRosterManagementTypes() {
        $category = AuditEventCategory::find(AuditEventCategory::CATEGORY_ROSTER_MANAGEMENT);
        
        $category->types()->createMany([
            [
                'id' => AuditEventType::TYPE_ATHLETE_CREATED,
                'name' => 'Athlete Created'
            ],
            [
                'id' => AuditEventType::TYPE_ATHLETE_UPDATED,
                'name' => 'Athlete Info Updated'
            ],
            [
                'id' => AuditEventType::TYPE_ATHLETE_DELETED,
                'name' => 'Athlete Removed'
            ],
            [
                'id' => AuditEventType::TYPE_ATHLETE_OVERWRITTEN,
                'name' => 'Athlete Overwritten With Imported Data'
            ],
            [
                'id' => AuditEventType::TYPE_ATHLETE_IMPORTED,
                'name' => 'Athlete Imported'
            ],
            [
                'id' => AuditEventType::TYPE_COACH_CREATED,
                'name' => 'Coach Created'
            ],
            [
                'id' => AuditEventType::TYPE_COACH_UPDATED,
                'name' => 'Coach Info Updated'
            ],
            [
                'id' => AuditEventType::TYPE_COACH_DELETED,
                'name' => 'Coach Removed'
            ],
            [
                'id' => AuditEventType::TYPE_COACH_IMPORTED,
                'name' => 'Coach Imported'
            ],
            [
                'id' => AuditEventType::TYPE_COACH_OVERWRITTEN,
                'name' => 'Coach Overwritten With Imported Data'
            ],
        ]);
    }

    private function seedMeetManagementTypes() {
        $category = AuditEventCategory::find(AuditEventCategory::CATEGORY_MEET_MANAGEMENT);
        
        $category->types()->createMany([
            [
                'id' => AuditEventType::TYPE_MEET_CREATED,
                'name' => 'Meet Created'
            ],
            [
                'id' => AuditEventType::TYPE_MEET_UPDATED,
                'name' => 'Meet Info Updated'
            ],
            [
                'id' => AuditEventType::TYPE_MEET_DELETED,
                'name' => 'Meet Deleted'
            ],
            [
                'id' => AuditEventType::TYPE_MEET_ARCHIVED,
                'name' => 'Meet Archived'
            ],
            [
                'id' => AuditEventType::TYPE_MEET_RESTORED,
                'name' => 'Meet Restored'
            ],
            [
                'id' => AuditEventType::TYPE_MEET_PUBLISHED,
                'name' => 'Meet Published'
            ],
            [
                'id' => AuditEventType::TYPE_MEET_UNPUBLISHED,
                'name' => 'Meet Unpublished'
            ],
            [
                'id' => AuditEventType::TYPE_MEET_USAG_SANCTION_RECEIVED,
                'name' => 'USAG Sanction Received'
            ],
            [
                'id' => AuditEventType::TYPE_MEET_USAG_SANCTION_PROCESSED,
                'name' => 'USAG Sanction Processed'
            ],
            [
                'id' => AuditEventType::TYPE_MEET_USAG_SANCTION_DISMISSED,
                'name' => 'USAG Sanction Dismissed'
            ],
        ]);
    }

    private function seedMeetRegistrationTypes() {
        $category = AuditEventCategory::find(AuditEventCategory::CATEGORY_MEET_REGISTRATION);
        
        $category->types()->createMany([
            [
                'id' => AuditEventType::TYPE_REGISTRATION_CREATED,
                'name' => 'Registration Created'
            ],
            [
                'id' => AuditEventType::TYPE_REGISTRATION_UPDATED,
                'name' => 'Registration Updated'
            ],
            [
                'id' => AuditEventType::TYPE_REGISTRATION_WAITLIST_ACCEPTED,
                'name' => 'Waitlist Registration Accepted'
            ],
            [
                'id' => AuditEventType::TYPE_REGISTRATION_WAITLIST_REJECTED,
                'name' => 'Waitlist Registration Rejected'
            ],
            [
                'id' => AuditEventType::TYPE_REGISTRATION_CHECK_ACCEPTED,
                'name' => 'Registration Check Accepted'
            ],
            [
                'id' => AuditEventType::TYPE_REGISTRATION_CHECK_REJECTED,
                'name' => 'Registration Check Rejected'
            ],
            [
                'id' => AuditEventType::TYPE_REGISTRATION_TRANSACTION_PAID,
                'name' => 'Registration Transaction Paid'
            ],
            [
                'id' => AuditEventType::TYPE_MEET_USAG_RESERVATION_RECEIVED,
                'name' => 'USAG Reservation Received'
            ],
            [
                'id' => AuditEventType::TYPE_MEET_USAG_RESERVATION_PROCESSED,
                'name' => 'USAG Reservation Processed'
            ],
            [
                'id' => AuditEventType::TYPE_MEET_USAG_RESERVATION_DISMISSED,
                'name' => 'USAG Reservation Dismissed'
            ],
        ]);
    }
}