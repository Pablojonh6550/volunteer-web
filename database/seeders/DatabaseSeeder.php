<?php

namespace Database\Seeders;

use App\Enums\OrganizationStatus;
use App\Enums\RequestStatus;
use App\Enums\RequestVolunteerStatus;
use App\Models\Category;
use App\Models\HelpRequest;
use App\Models\Organization;
use App\Models\RequestVolunteer;
use App\Models\User;
use App\Models\Volunteer;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Seeder;
use Illuminate\Support\Collection as BaseCollection;

/**
 * Dados de demonstração para desenvolvimento local.
 *
 * Contas criadas (senha: "password"):
 *  - super.ong@example.com         → ONG com solicitações em todos os status e muitos candidatos
 *  - super.voluntario@example.com  → voluntário com participações em todos os status
 *  - ong@example.com               → ONG simples
 *  - voluntario@example.com        → voluntário simples
 *
 * Além delas, gera um volume de ONGs, voluntários, solicitações e
 * participações espalhados por São Paulo (ver constantes abaixo).
 */
class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    private const ORGANIZATIONS = 40;

    private const INACTIVE_ORGANIZATIONS = 5;

    private const VOLUNTEERS = 200;

    private const REQUESTS_PER_ORGANIZATION = [4, 10];

    private const PARTICIPANTS_PER_REQUEST = [0, 8];

    /** Centro de referência das contas demo (Praça da Sé). */
    private const CENTER = [-23.5505, -46.6333];

    /** @var Collection<int, Category> */
    private Collection $categories;

    public function run(): void
    {
        $this->call(CategorySeeder::class);
        $this->categories = Category::all();

        $volunteers = $this->seedVolunteers();
        $organizations = $this->seedOrganizations();

        $superVolunteer = $this->seedSuperVolunteer();
        $this->seedSuperOrganization($volunteers, $superVolunteer);
        $this->seedDemoAccounts();

        // Solicitações das demais ONGs, com candidatos do pool de voluntários.
        $organizations->each(function (Organization $organization) use ($volunteers) {
            foreach (range(1, fake()->numberBetween(...self::REQUESTS_PER_ORGANIZATION)) as $_) {
                $request = $this->createRequest($organization, $this->randomRequestStatus());
                $this->attachParticipants($request, $volunteers->random(fake()->numberBetween(...self::PARTICIPANTS_PER_REQUEST)));
            }
        });

        // O super voluntário também participa de solicitações de outras ONGs.
        HelpRequest::query()
            ->whereIn('organization_id', $organizations->filter(fn (Organization $o) => $o->status === OrganizationStatus::Active)->pluck('id'))
            ->whereDoesntHave('participations', fn ($q) => $q->where('volunteer_id', $superVolunteer->id))
            ->inRandomOrder()
            ->limit(20)
            ->get()
            ->each(fn (HelpRequest $request) => $this->attachParticipants($request, collect([$superVolunteer])));
    }

    /** @return Collection<int, Volunteer> */
    private function seedVolunteers(): Collection
    {
        return User::factory()->volunteer()->count(self::VOLUNTEERS)->create()
            ->map(function (User $user) {
                $volunteer = $user->volunteer;

                // Uma parte sem localização e outra indisponível, para testar filtros.
                $roll = fake()->numberBetween(1, 100);
                if ($roll <= 10) {
                    $volunteer->update(['latitude' => null, 'longitude' => null, 'location_consent_at' => null]);
                } elseif ($roll <= 20) {
                    $volunteer->update(['available' => false]);
                }

                $volunteer->categories()->sync($this->categories->random(fake()->numberBetween(1, 5))->pluck('id'));

                return $volunteer;
            });
    }

    /** @return Collection<int, Organization> */
    private function seedOrganizations(): Collection
    {
        $organizations = User::factory()->organization()->count(self::ORGANIZATIONS)->create()
            ->map(fn (User $user) => $user->organization);

        $organizations->random(self::INACTIVE_ORGANIZATIONS)
            ->each(fn (Organization $organization) => $organization->update(['status' => OrganizationStatus::Inactive]));

        return $organizations;
    }

    private function seedSuperVolunteer(): Volunteer
    {
        $user = User::factory()->volunteer()->create([
            'name' => 'Super Voluntário',
            'email' => 'super.voluntario@example.com',
        ]);

        $volunteer = $user->volunteer;
        $volunteer->update([
            'latitude' => -23.5614,
            'longitude' => -46.6559,
            'bio' => 'Conta de testes com participações em todos os status.',
            'available' => true,
        ]);
        $volunteer->categories()->sync($this->categories->pluck('id'));

        return $volunteer;
    }

    /**
     * ONG com solicitações em todos os status e vários candidatos em cada uma.
     * O super voluntário participa de parte delas, cobrindo todos os status de participação.
     *
     * @param  Collection<int, Volunteer>  $volunteers
     */
    private function seedSuperOrganization(Collection $volunteers, Volunteer $superVolunteer): void
    {
        $user = User::factory()->organization()->create([
            'name' => 'Super ONG',
            'email' => 'super.ong@example.com',
        ]);

        $organization = $user->organization;
        $organization->update([
            'name' => 'Super ONG',
            'description' => 'Conta de testes com solicitações em todos os status.',
            'latitude' => self::CENTER[0],
            'longitude' => self::CENTER[1],
        ]);

        $plan = [
            RequestStatus::Open->value => 12,
            RequestStatus::InProgress->value => 6,
            RequestStatus::Completed->value => 8,
            RequestStatus::Cancelled->value => 4,
        ];

        foreach ($plan as $status => $count) {
            foreach (range(1, $count) as $_) {
                $request = $this->createRequest($organization, RequestStatus::from($status), spread: true);
                $this->attachParticipants($request, $volunteers->random(fake()->numberBetween(3, 10)));
            }
        }

        // Duas participações do super voluntário em cada status, em solicitações coerentes.
        $requests = $organization->requests()->get()->groupBy(fn (HelpRequest $r) => $r->status->value);
        $placement = [
            RequestVolunteerStatus::Interested->value => RequestStatus::Open,
            RequestVolunteerStatus::Approved->value => RequestStatus::InProgress,
            RequestVolunteerStatus::Rejected->value => RequestStatus::Open,
            RequestVolunteerStatus::Cancelled->value => RequestStatus::Cancelled,
            RequestVolunteerStatus::Completed->value => RequestStatus::Completed,
        ];

        foreach ($placement as $participationStatus => $requestStatus) {
            $requests[$requestStatus->value]->shift(2)->each(fn (HelpRequest $request) => RequestVolunteer::factory()
                ->status(RequestVolunteerStatus::from($participationStatus))
                ->create(['request_id' => $request->id, 'volunteer_id' => $superVolunteer->id]));
        }
    }

    /** Contas simples, mantidas por compatibilidade com quem já as usa. */
    private function seedDemoAccounts(): void
    {
        $ong = User::factory()->organization()->create([
            'name' => 'Instituto Mãos Dadas',
            'email' => 'ong@example.com',
        ]);
        $ong->organization->update(['name' => 'Instituto Mãos Dadas', 'latitude' => self::CENTER[0], 'longitude' => self::CENTER[1]]);

        $volunteer = User::factory()->volunteer()->create([
            'name' => 'Maria Voluntária',
            'email' => 'voluntario@example.com',
        ]);
        $volunteer->volunteer->update(['latitude' => -23.5614, 'longitude' => -46.6559]);
        $volunteer->volunteer->categories()->sync($this->categories->random(3)->pluck('id'));

        $requests = collect(range(1, 6))->map(fn () => $this->createRequest($ong->organization, RequestStatus::Open, spread: true));

        RequestVolunteer::factory()->create([
            'request_id' => $requests[0]->id,
            'volunteer_id' => $volunteer->volunteer->id,
        ]);
        RequestVolunteer::factory()->status(RequestVolunteerStatus::Approved)->create([
            'request_id' => $requests[1]->id,
            'volunteer_id' => $volunteer->volunteer->id,
        ]);
    }

    private function createRequest(Organization $organization, RequestStatus $status, bool $spread = false): HelpRequest
    {
        $factory = HelpRequest::factory()
            ->forCategory($this->categories->random())
            ->status($status);

        if ($status->isFinal()) {
            $factory = $factory->past();
        }

        // Atividades próximas à sede da ONG (ou espalhadas ao redor do centro, para as contas demo).
        $jitter = $spread ? 0.02 : 0.005;

        return $factory->create([
            'organization_id' => $organization->id,
            'latitude' => $organization->latitude + fake()->randomFloat(4, -$jitter, $jitter),
            'longitude' => $organization->longitude + fake()->randomFloat(4, -$jitter, $jitter),
        ]);
    }

    /** @param  BaseCollection<int, Volunteer>  $volunteers */
    private function attachParticipants(HelpRequest $request, BaseCollection $volunteers): void
    {
        foreach ($volunteers as $volunteer) {
            RequestVolunteer::factory()
                ->status($this->participationStatusFor($request->status))
                ->create(['request_id' => $request->id, 'volunteer_id' => $volunteer->id]);
        }
    }

    /** Distribuição de status mais comum na prática: maioria aberta. */
    private function randomRequestStatus(): RequestStatus
    {
        return fake()->randomElement([
            ...array_fill(0, 6, RequestStatus::Open),
            ...array_fill(0, 2, RequestStatus::InProgress),
            RequestStatus::Completed,
            RequestStatus::Cancelled,
        ]);
    }

    /** Status de participação coerente com o status da solicitação. */
    private function participationStatusFor(RequestStatus $status): RequestVolunteerStatus
    {
        return fake()->randomElement(match ($status) {
            RequestStatus::Open => [
                RequestVolunteerStatus::Interested, RequestVolunteerStatus::Interested,
                RequestVolunteerStatus::Approved, RequestVolunteerStatus::Rejected, RequestVolunteerStatus::Cancelled,
            ],
            RequestStatus::InProgress => [
                RequestVolunteerStatus::Approved, RequestVolunteerStatus::Approved,
                RequestVolunteerStatus::Interested, RequestVolunteerStatus::Rejected,
            ],
            RequestStatus::Completed => [
                RequestVolunteerStatus::Completed, RequestVolunteerStatus::Completed, RequestVolunteerStatus::Rejected,
            ],
            RequestStatus::Cancelled => [RequestVolunteerStatus::Cancelled, RequestVolunteerStatus::Rejected],
        });
    }
}
