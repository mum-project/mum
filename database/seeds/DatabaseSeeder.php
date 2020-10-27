<?php

use App\Alias;
use App\AliasRequest;
use App\Domain;
use App\Mailbox;
use App\ServiceHealthCheck;
use App\SizeMeasurement;
use App\ShellCommandIntegration;
use App\SystemService;
use App\TlsPolicy;
use App\WebHookIntegration;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $exampleDomain = Domain::factory()->create(['domain' => 'example.com']);
        Mailbox::factory()->create([
            'local_part'     => 'admin',
            'domain_id'      => $exampleDomain->id,
            'homedir'        => getHomedirForMailbox('admin', $exampleDomain->domain),
            'maildir'        => getMaildirForMailbox('admin', $exampleDomain->domain),
            'is_super_admin' => true,
            'active'         => true
        ]);
        Domain::factory(5)->create();
        Mailbox::factory(50)->create();
        Alias::factory(50)
            ->create()
            ->each(function (Alias $alias) {
                $mailboxes = Mailbox::all()
                    ->random(2);
                foreach ($mailboxes as $mailbox) {
                    $alias->addRecipientMailbox($mailbox);
                }
                $alias->senderMailboxes()
                    ->saveMany(Mailbox::all()
                        ->random(2));
            });
        AliasRequest::factory(10)
            ->create()
            ->each(function (AliasRequest $aliasRequest) {
                $mailboxes = Mailbox::all()
                    ->random(2);
                foreach ($mailboxes as $mailbox) {
                    $aliasRequest->addRecipientMailbox($mailbox);
                }
                $aliasRequest->senderMailboxes()
                    ->saveMany(Mailbox::all()
                        ->random(2));
            });
        TlsPolicy::factory(3)->create();
        Domain::all()
            ->random(5)
            ->each(function (Domain $domain) {
                $domain->admins()
                    ->saveMany(Mailbox::all()
                        ->random(2));
            });
        Mailbox::all()
            ->random(20)
            ->each(function (Mailbox $mailbox) {
                $mailbox->admins()
                    ->saveMany(Mailbox::all()
                        ->random(2));
            });
        if (config('integrations.shell_commands.01')) {
            ShellCommandIntegration::factory(3)->create();
        }
        WebHookIntegration::factory(3)->create();
        Mailbox::factory()
            ->create([
                'local_part'     => 'domain.admin',
                'domain_id'      => $exampleDomain->id,
                'homedir'        => getHomedirForMailbox('domain.admin', $exampleDomain->domain),
                'maildir'        => getMaildirForMailbox('domain.admin', $exampleDomain->domain),
                'is_super_admin' => false,
                'active'         => true
            ])
            ->administratedDomains()
            ->attach($exampleDomain);
        $faker = Faker\Factory::create();
        Domain::all()
            ->each(function (Domain $domain) use ($faker) {
                $this->createSizeMeasurements($faker, $domain->id, Domain::class, 5 * 1024 * 1024, Carbon::now()
                    ->subMonths(2));
            });
        Mailbox::all()
            ->each(function (Mailbox $mailbox) use ($faker) {
                $this->createSizeMeasurements($faker, $mailbox->id, Mailbox::class, 5 * 1024, Carbon::now()
                    ->subMonths(2));
            });
        $this->createSizeMeasurements($faker, null, null, 10 * 1024 * 1024, Carbon::now()
            ->subMonths(2));
        SystemService::factory(3)
            ->create()
            ->each(function (SystemService $systemService) {
                $systemService->serviceHealthChecks()
                    ->saveMany(ServiceHealthCheck::factory(20)->create(['system_service_id' => $systemService->id]));
            });
    }

    /**
     * @param \Faker\Generator $faker
     * @param                  $measurableId
     * @param                  $measurableType
     * @param int $startSize
     * @param Carbon $startedAt
     */
    private function createSizeMeasurements(
        Faker\Generator $faker,
        $measurableId,
        $measurableType,
        int $startSize,
        Carbon $startedAt
    )
    {
        $size = $startSize;
        $createdAt = $startedAt;
        for ($i = 0; $i < 10; $i++) {
            $size = $size * $faker->randomFloat(4, 0.98, 1.1);
            $createdAt = $createdAt->addDay();
            SizeMeasurement::factory()->create([
                'measurable_id'   => $measurableId,
                'measurable_type' => $measurableType,
                'size'            => $size,
                'created_at'      => $createdAt
            ]);
        }
    }
}
