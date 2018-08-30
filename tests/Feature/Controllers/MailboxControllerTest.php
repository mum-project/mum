<?php

namespace Tests\Feature\Controllers;

use App\Domain;
use App\Mailbox;
use function array_except;
use function array_merge;
use function compact;
use function csrf_token;
use function factory;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Session;
use function now;
use function route;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class MailboxControllerTest extends TestCase
{
    use WithFaker, RefreshDatabase;

    protected function setUp()
    {
        parent::setUp();
        factory(Domain::class)->create();
    }

    public function testIndex()
    {
        $admin = factory(Mailbox::class)->create([
            'is_super_admin' => true,
            'active'         => true
        ]);
        $user = factory(Mailbox::class)->create([
            'is_super_admin' => false,
            'active'         => true
        ]);
        $perPage = $user->getPerPage();
        factory(Mailbox::class, $perPage - 2)->create();

        $mailboxes1 = Mailbox::whereAuthorized()
            ->take($perPage)
            ->get();
        $mailboxes2 = Mailbox::whereAuthorized()
            ->skip($perPage)
            ->take($perPage)
            ->get();

        $this->get(route('mailboxes.index'))
            ->assertStatus(302);

        $this->actingAs($user)
            ->get(route('mailboxes.index'))
            ->assertSuccessful()
            ->assertSeeText($user->address())
            ->assertDontSeeText($admin->address());

        $responsePage1 = $this->actingAs($admin)
            ->get(route('mailboxes.index'))
            ->assertSuccessful();

        $responsePage2 = $this->actingAs($admin)
            ->get(route('mailboxes.index', ['page' => 2]))
            ->assertSuccessful();

        $mailboxes1->each(function (Mailbox $mailbox) use ($responsePage1, $responsePage2) {
            $responsePage1->assertSee("href=\"" . route('mailboxes.show', compact('mailbox')) . "\"");
            $responsePage2->assertDontSee("href=\"" . route('mailboxes.show', compact('mailbox')) . "\"");
        });
        $mailboxes2->each(function (Mailbox $mailbox) use ($responsePage1, $responsePage2) {
            $responsePage1->assertDontSee("href=\"" . route('mailboxes.show', compact('mailbox')) . "\"");
            $responsePage2->assertSee("href=\"" . route('mailboxes.show', compact('mailbox')) . "\"");
        });
    }

    public function testCreate()
    {
        $admin = factory(Mailbox::class)->create([
            'is_super_admin' => true,
            'active'         => true
        ]);
        $user = factory(Mailbox::class)->create([
            'is_super_admin' => false,
            'active'         => true
        ]);

        $this->get(route('mailboxes.create'))
            ->assertStatus(302);

        $this->actingAs($user)
            ->get(route('mailboxes.create'))
            ->assertStatus(403);

        $this->actingAs($admin)
            ->get(route('mailboxes.create'))
            ->assertSuccessful()
            ->assertSee(route('mailboxes.store'));
    }

    public function testStore()
    {
        $admin = factory(Mailbox::class)->create([
            'is_super_admin' => true,
            'active'         => true
        ]);
        $user = factory(Mailbox::class)->create([
            'is_super_admin' => false,
            'active'         => true
        ]);
        $domain = factory(Domain::class)->create();
        $password = (string)now();

        $data = [
            'local_part'            => $this->faker->unique()->userName,
            'password'              => $password,
            'password_confirmation' => $password,
            'name'                  => $this->faker->name,
            'domain_id'             => $domain->id,
            'alternative_email'     => $this->faker->safeEmail,
            'quota'                 => $this->faker->numberBetween(1, 200),
            'is_super_admin'        => $this->faker->boolean,
            'send_only'             => $this->faker->boolean,
            'active'                => $this->faker->boolean
        ];
        $databaseNeedle = array_except($data, [
            'password',
            'password_confirmation'
        ]);

        $this->assertDatabaseMissing('mailboxes', $databaseNeedle);

        Session::start();

        $this->post(route('mailboxes.store'), array_merge($data, ['_token' => csrf_token()]))
            ->assertStatus(302);

        $this->actingAs($user)
            ->post(route('mailboxes.store'), array_merge($data, ['_token' => csrf_token()]))
            ->assertStatus(403);

        $this->actingAs($admin)
            ->followingRedirects()
            ->post(route('mailboxes.store'), array_merge($data, ['_token' => csrf_token()]))
            ->assertSuccessful()
            ->assertSeeText($data['local_part']);

        $this->assertDatabaseHas('mailboxes', $databaseNeedle);
    }

    public function testStoreFirstName()
    {
        Config::set('mum.mailboxes.first_name_activated', false);

        $admin = factory(Mailbox::class)->create([
            'is_super_admin' => true,
            'active'         => true
        ]);
        $user = factory(Mailbox::class)->create([
            'is_super_admin' => false,
            'active'         => true
        ]);
        $domain = factory(Domain::class)->create();
        $password = (string)now();

        $data = [
            'local_part'            => $this->faker->unique()->userName,
            'password'              => $password,
            'password_confirmation' => $password,
            'name'                  => $this->faker->name,
            'first_name'            => $this->faker->name,
            'domain_id'             => $domain->id,
            'alternative_email'     => $this->faker->safeEmail,
            'quota'                 => $this->faker->numberBetween(1, 200),
            'is_super_admin'        => $this->faker->boolean,
            'send_only'             => $this->faker->boolean,
            'active'                => $this->faker->boolean
        ];
        $databaseNeedle = array_except($data, [
            'password',
            'password_confirmation'
        ]);

        $this->assertDatabaseMissing('mailboxes', $databaseNeedle);

        Session::start();

        $this->post(route('mailboxes.store'), array_merge($data, ['_token' => csrf_token()]))
            ->assertStatus(302);

        $this->actingAs($user)
            ->post(route('mailboxes.store'), array_merge($data, ['_token' => csrf_token()]))
            ->assertStatus(403);

        $this->actingAs($admin)
            ->post(route('mailboxes.store'), array_merge($data, ['_token' => csrf_token()]))
            ->assertStatus(302);

        Config::set('mum.mailboxes.first_name_activated', true);

        $this->actingAs($admin)
            ->followingRedirects()
            ->post(route('mailboxes.store'), array_merge($data, ['_token' => csrf_token()]))
            ->assertStatus(200)
            ->assertSeeText($data['local_part']);

        $this->assertDatabaseHas('mailboxes', $databaseNeedle);
    }

    public function testEdit()
    {
        $admin = factory(Mailbox::class)->create([
            'is_super_admin' => true,
            'active'         => true
        ]);
        $user = factory(Mailbox::class)->create([
            'is_super_admin' => false,
            'active'         => true
        ]);
        $mailbox = factory(Mailbox::class)->create();

        $this->get(route('mailboxes.edit', compact('mailbox')))
            ->assertStatus(302);

        $this->actingAs($user)
            ->get(route('mailboxes.edit', compact('mailbox')))
            ->assertStatus(403);

        $this->actingAs($admin)
            ->get(route('mailboxes.edit', compact('mailbox')))
            ->assertSuccessful()
            ->assertSee(route('mailboxes.update', compact('mailbox')));
    }

    public function testUpdate()
    {
        $admin = factory(Mailbox::class)->create([
            'is_super_admin' => true,
            'active'         => true
        ]);
        $user = factory(Mailbox::class)->create([
            'is_super_admin' => false,
            'active'         => true
        ]);
        $mailbox = factory(Mailbox::class)->create();
        $password = (string)now();

        $data = [
            'password'              => $password,
            'password_confirmation' => $password,
            'name'                  => $this->faker->name,
            'alternative_email'     => $this->faker->safeEmail,
            'quota'                 => $this->faker->numberBetween(1, 200),
            'is_super_admin'        => $this->faker->boolean,
            'send_only'             => $this->faker->boolean,
            'active'                => $this->faker->boolean
        ];

        $databaseNeedle = array_merge(['id' => $mailbox->id], array_except($data, [
            'password',
            'password_confirmation'
        ]));

        $this->assertDatabaseMissing('mailboxes', $databaseNeedle);

        Session::start();

        $this->patch(route('mailboxes.update', compact('mailbox')), array_merge($data, ['_token' => csrf_token()]))
            ->assertStatus(302);

        $this->actingAs($user)
            ->patch(route('mailboxes.update', compact('mailbox')), array_merge($data, ['_token' => csrf_token()]))
            ->assertStatus(403);

        $this->actingAs($admin)
            ->followingRedirects()
            ->patch(route('mailboxes.update', compact('mailbox')), array_merge($data, ['_token' => csrf_token()]))
            ->assertSuccessful()
            ->assertSeeText($data['name']);

        $this->assertDatabaseHas('mailboxes', $databaseNeedle);
    }

    public function testUpdateFirstName()
    {
        Config::set('mum.mailboxes.first_name_activated', false);

        $admin = factory(Mailbox::class)->create([
            'is_super_admin' => true,
            'active'         => true
        ]);
        $user = factory(Mailbox::class)->create([
            'is_super_admin' => false,
            'active'         => true
        ]);
        $mailbox = factory(Mailbox::class)->create();
        $password = (string)now();

        $data = [
            'password'              => $password,
            'password_confirmation' => $password,
            'name'                  => $this->faker->name,
            'first_name'                  => $this->faker->name,
            'alternative_email'     => $this->faker->safeEmail,
            'quota'                 => $this->faker->numberBetween(1, 200),
            'is_super_admin'        => $this->faker->boolean,
            'send_only'             => $this->faker->boolean,
            'active'                => $this->faker->boolean
        ];

        $databaseNeedle = array_merge(['id' => $mailbox->id], array_except($data, [
            'password',
            'password_confirmation'
        ]));

        $this->assertDatabaseMissing('mailboxes', $databaseNeedle);

        Session::start();

        $this->patch(route('mailboxes.update', compact('mailbox')), array_merge($data, ['_token' => csrf_token()]))
            ->assertStatus(302);

        $this->actingAs($user)
            ->patch(route('mailboxes.update', compact('mailbox')), array_merge($data, ['_token' => csrf_token()]))
            ->assertStatus(403);

        $this->actingAs($admin)
            ->patch(route('mailboxes.update', compact('mailbox')), array_merge($data, ['_token' => csrf_token()]))
            ->assertStatus(302);

        Config::set('mum.mailboxes.first_name_activated', true);

        $this->actingAs($admin)
            ->followingRedirects()
            ->patch(route('mailboxes.update', compact('mailbox')), array_merge($data, ['_token' => csrf_token()]))
            ->assertSuccessful()
            ->assertSeeText($data['name']);

        $this->assertDatabaseHas('mailboxes', $databaseNeedle);
    }

    public function testDestroy()
    {
        $admin = factory(Mailbox::class)->create([
            'is_super_admin' => true,
            'active'         => true
        ]);
        $user = factory(Mailbox::class)->create([
            'is_super_admin' => false,
            'active'         => true
        ]);
        $mailbox = factory(Mailbox::class)->create();

        Session::start();

        $this->delete(route('mailboxes.destroy', compact('mailbox')), ['_token' => csrf_token()])
            ->assertStatus(302);
        $this->assertNotNull($mailbox->fresh());

        $this->actingAs($user)
            ->delete(route('mailboxes.destroy', compact('mailbox')), ['_token' => csrf_token()])
            ->assertStatus(403);
        $this->assertNotNull($mailbox->fresh());

        $this->actingAs($admin)
            ->followingRedirects()
            ->delete(route('mailboxes.destroy', compact('mailbox')), ['_token' => csrf_token()])
            ->assertSuccessful();
        $this->assertNull($mailbox->fresh());
    }
}
