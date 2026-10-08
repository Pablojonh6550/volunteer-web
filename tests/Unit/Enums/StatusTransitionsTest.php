<?php

namespace Tests\Unit\Enums;

use App\Enums\RequestStatus;
use App\Enums\RequestVolunteerStatus;
use PHPUnit\Framework\TestCase;

class StatusTransitionsTest extends TestCase
{
    public function test_final_request_statuses_do_not_transition(): void
    {
        $this->assertTrue(RequestStatus::Completed->isFinal());
        $this->assertTrue(RequestStatus::Cancelled->isFinal());
        $this->assertFalse(RequestStatus::Cancelled->canTransitionTo(RequestStatus::Open));
    }

    public function test_open_request_can_progress(): void
    {
        $this->assertTrue(RequestStatus::Open->canTransitionTo(RequestStatus::InProgress));
        $this->assertTrue(RequestStatus::InProgress->canTransitionTo(RequestStatus::Completed));
        $this->assertTrue(RequestStatus::Open->acceptsInterest());
        $this->assertFalse(RequestStatus::Completed->acceptsInterest());
    }

    public function test_participation_transitions(): void
    {
        $this->assertSame(
            [RequestVolunteerStatus::Approved, RequestVolunteerStatus::Rejected],
            RequestVolunteerStatus::Interested->organizationTransitions(),
        );
        $this->assertSame([], RequestVolunteerStatus::Rejected->organizationTransitions());
        $this->assertTrue(RequestVolunteerStatus::Approved->cancellableByVolunteer());
        $this->assertFalse(RequestVolunteerStatus::Completed->cancellableByVolunteer());
    }
}
