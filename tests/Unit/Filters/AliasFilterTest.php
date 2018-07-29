<?php

namespace Tests\Unit\Filters;

use App\Alias;
use App\Domain;
use App\Http\Filters\AliasFilter;
use App\Mailbox;
use Carbon\Carbon;
use function factory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Mockery;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AliasFilterTest extends TestCase
{
    use WithFaker, RefreshDatabase;

    protected function setUp()
    {
        parent::setUp();
        factory(Domain::class)->create();
        factory(Mailbox::class)->create();
    }

    /**
     * Creates a QueryFilter and injects a mocked Request object
     * that supplies the provided request parameters to the filter.
     *
     * @param array $requestParams
     * @return AliasFilter
     */
    protected function createAliasFilter(array $requestParams)
    {
        $mockRequest = Mockery::mock(Request::class);
        $mockRequest->shouldReceive('all')
            ->andReturn($requestParams);
        return new AliasFilter($mockRequest);
    }

    protected function assertForOneAlias(AliasFilter $aliasFilter, Builder $builder, Alias $alias)
    {
        $query = $aliasFilter->apply($builder);
        $this->assertTrue($alias->is($query->firstOrFail()));
        $this->assertTrue($query->get()
                ->count() === 1);
    }

    protected function assertAllBoolPossibilitiesFor($filterName, Alias $trueAlias, Alias $falseAlias)
    {
        $aliasFilter = $this->createAliasFilter([$filterName => '1']);
        $this->assertForOneAlias($aliasFilter, Alias::query(), $trueAlias);

        $aliasFilter = $this->createAliasFilter([$filterName => 'true']);
        $this->assertForOneAlias($aliasFilter, Alias::query(), $trueAlias);

        $aliasFilter = $this->createAliasFilter([$filterName => true]);
        $this->assertForOneAlias($aliasFilter, Alias::query(), $trueAlias);

        $aliasFilter = $this->createAliasFilter([$filterName => '0']);
        $this->assertForOneAlias($aliasFilter, Alias::query(), $falseAlias);

        $aliasFilter = $this->createAliasFilter([$filterName => 'false']);
        $this->assertForOneAlias($aliasFilter, Alias::query(), $falseAlias);

        $aliasFilter = $this->createAliasFilter([$filterName => false]);
        $this->assertForOneAlias($aliasFilter, Alias::query(), $falseAlias);
    }

    public function testDomain()
    {
        $domain = factory(Domain::class)->create();
        $alias = factory(Alias::class)->create(['domain_id' => $domain->id]);
        $otherDomain = factory(Domain::class)->create();
        $otherAlias = factory(Alias::class)->create(['domain_id' => $otherDomain->id]);

        $aliasFilter = $this->createAliasFilter(['domain' => $domain->id]);
        $this->assertForOneAlias($aliasFilter, Alias::query(), $alias);

        $aliasFilter = $this->createAliasFilter(['domain' => $otherDomain->id]);
        $this->assertForOneAlias($aliasFilter, Alias::query(), $otherAlias);
    }

    public function testSenderMailboxes()
    {
        /** @var Alias $alias1 */
        $alias1 = factory(Alias::class)->create();
        $mailbox1 = factory(Mailbox::class)->create();
        $alias1->senderMailboxes()
            ->save($mailbox1);

        $alias2 = factory(Alias::class)->create();
        $mailbox2 = factory(Mailbox::class)->create();
        $alias2->senderMailboxes()
            ->save($mailbox2);

        $otherAlias = factory(Alias::class)->create();
        $otherMailbox = factory(Mailbox::class)->create();
        $otherAlias->senderMailboxes()
            ->save($otherMailbox);

        $aliasFilter = $this->createAliasFilter([
            'senderMailboxes' => [
                $mailbox1->id,
                $mailbox2->id
            ]
        ]);
        $results = $aliasFilter->apply(Alias::query())
            ->get();
        $this->assertTrue($results->contains($alias1));
        $this->assertTrue($results->contains($alias2));
        $this->assertFalse($results->contains($otherAlias));
        $this->assertTrue($results->count() === 2);
    }

    public function testRecipientMailboxes()
    {
        /** @var Alias $alias1 */
        $alias1 = factory(Alias::class)->create();
        $mailbox1 = factory(Mailbox::class)->create();
        $alias1->addRecipientMailbox($mailbox1);

        $alias2 = factory(Alias::class)->create();
        $mailbox2 = factory(Mailbox::class)->create();
        $alias2->addRecipientMailbox($mailbox2);

        $otherAlias = factory(Alias::class)->create();
        $otherMailbox = factory(Mailbox::class)->create();
        $otherAlias->addRecipientMailbox($otherMailbox);

        $aliasFilter = $this->createAliasFilter([
            'recipientMailboxes' => [
                $mailbox1->id,
                $mailbox2->id
            ]
        ]);
        $results = $aliasFilter->apply(Alias::query())
            ->get();
        $this->assertTrue($results->contains($alias1));
        $this->assertTrue($results->contains($alias2));
        $this->assertFalse($results->contains($otherAlias));
        $this->assertTrue($results->count() === 2);
    }

    public function testSenderOrRecipientMailboxes()
    {
        /** @var Alias $alias1 */
        $alias1 = factory(Alias::class)->create();
        $mailbox1 = factory(Mailbox::class)->create();
        $alias1->senderMailboxes()
            ->save($mailbox1);

        /** @var Alias $alias2 */
        $alias2 = factory(Alias::class)->create();
        $mailbox2 = factory(Mailbox::class)->create();
        $alias2->addRecipientMailbox($mailbox2);

        /** @var Alias $alias3 */
        $alias3 = factory(Alias::class)->create();
        $mailbox3 = factory(Mailbox::class)->create();
        $alias3->senderMailboxes()
            ->save($mailbox3);
        $alias3->addRecipientMailbox($mailbox3);

        /** @var Alias $otherAlias */
        $otherAlias = factory(Alias::class)->create();
        $otherMailbox = factory(Mailbox::class)->create();
        $otherAlias->senderMailboxes()
            ->save($otherMailbox);
        $otherAlias->addRecipientMailbox($otherMailbox);

        $aliasFilter = $this->createAliasFilter([
            'senderOrRecipientMailboxes' => [
                $mailbox1->id,
                $mailbox2->id,
                $mailbox3->id
            ]
        ]);
        $results = $aliasFilter->apply(Alias::query())
            ->get();
        $this->assertTrue($results->contains($alias1));
        $this->assertTrue($results->contains($alias2));
        $this->assertTrue($results->contains($alias3));
        $this->assertFalse($results->contains($otherAlias));
        $this->assertTrue($results->count() === 3);
    }

    public function testSenderAndRecipientMailboxes()
    {
        /** @var Alias $alias1 */
        $alias1 = factory(Alias::class)->create();
        $mailbox1 = factory(Mailbox::class)->create();
        $alias1->senderMailboxes()
            ->save($mailbox1);
        $alias1->addRecipientMailbox($mailbox1);

        /** @var Alias $alias1 */
        $alias2 = factory(Alias::class)->create();
        $mailbox2 = factory(Mailbox::class)->create();
        $alias2->senderMailboxes()
            ->save($mailbox2);
        $alias2->addRecipientMailbox($mailbox2);

        /** @var Alias $otherAlias */
        $otherAlias = factory(Alias::class)->create();
        $otherMailbox = factory(Mailbox::class)->create();
        $otherAlias->senderMailboxes()
            ->save($otherMailbox);
        $otherAlias->addRecipientMailbox($otherMailbox);

        $aliasFilter = $this->createAliasFilter([
            'senderAndRecipientMailboxes' => [
                $mailbox1->id,
                $mailbox2->id
            ]
        ]);
        $results = $aliasFilter->apply(Alias::query())
            ->get();
        $this->assertTrue($results->contains($alias1));
        $this->assertTrue($results->contains($alias2));
        $this->assertFalse($results->contains($otherAlias));
        $this->assertTrue($results->count() === 2);
    }

    public function testSenderMailbox()
    {
        $alias = factory(Alias::class)->create();
        $mailbox = factory(Mailbox::class)->create();
        $alias->senderMailboxes()
            ->save($mailbox);

        $otherAlias = factory(Alias::class)->create();
        $otherMailbox = factory(Mailbox::class)->create();
        $otherAlias->senderMailboxes()
            ->save($otherMailbox);

        $aliasFilter = $this->createAliasFilter(['senderMailbox' => $mailbox->id]);
        $results = $aliasFilter->apply(Alias::query())
            ->get();
        $this->assertTrue($results->contains($alias));
        $this->assertFalse($results->contains($otherAlias));
        $this->assertTrue($results->count() === 1);
    }

    public function testRecipientMailbox()
    {
        $alias = factory(Alias::class)->create();
        $mailbox = factory(Mailbox::class)->create();
        $alias->addRecipientMailbox($mailbox);

        $otherAlias = factory(Alias::class)->create();
        $otherMailbox = factory(Mailbox::class)->create();
        $otherAlias->addRecipientMailbox($otherMailbox);


        $aliasFilter = $this->createAliasFilter(['recipientMailbox' => $mailbox->id]);
        $results = $aliasFilter->apply(Alias::query())
            ->get();
        $this->assertTrue($results->contains($alias));
        $this->assertFalse($results->contains($otherAlias));
        $this->assertTrue($results->count() === 1);
    }

    public function testSenderOrRecipientMailbox()
    {
        /** @var Alias $otherAlias */
        $otherAlias = factory(Alias::class)->create();
        $otherMailbox = factory(Mailbox::class)->create();
        $otherAlias->senderMailboxes()
            ->save($otherMailbox);
        $otherAlias->addRecipientMailbox($otherMailbox);

        /** @var Alias $alias1 */
        $alias1 = factory(Alias::class)->create();
        $mailbox1 = factory(Mailbox::class)->create();
        $alias1->senderMailboxes()
            ->save($mailbox1);

        /** @var Alias $alias2 */
        $alias2 = factory(Alias::class)->create();
        $mailbox2 = factory(Mailbox::class)->create();
        $alias2->addRecipientMailbox($mailbox2);

        /** @var Alias $alias3 */
        $alias3 = factory(Alias::class)->create();
        $mailbox3 = factory(Mailbox::class)->create();
        $alias3->senderMailboxes()
            ->save($mailbox3);
        $alias3->addRecipientMailbox($mailbox3);

        $aliasFilter = $this->createAliasFilter(['senderOrRecipientMailbox' => $mailbox1->id]);
        $results = $aliasFilter->apply(Alias::query())
            ->get();
        $this->assertTrue($results->contains($alias1));
        $this->assertFalse($results->contains($alias2));
        $this->assertFalse($results->contains($alias3));
        $this->assertFalse($results->contains($otherAlias));
        $this->assertTrue($results->count() === 1);

        $aliasFilter = $this->createAliasFilter(['senderOrRecipientMailbox' => $mailbox2->id]);
        $results = $aliasFilter->apply(Alias::query())
            ->get();
        $this->assertFalse($results->contains($alias1));
        $this->assertTrue($results->contains($alias2));
        $this->assertFalse($results->contains($alias3));
        $this->assertFalse($results->contains($otherAlias));
        $this->assertTrue($results->count() === 1);

        $aliasFilter = $this->createAliasFilter(['senderOrRecipientMailbox' => $mailbox3->id]);
        $results = $aliasFilter->apply(Alias::query())
            ->get();
        $this->assertFalse($results->contains($alias1));
        $this->assertFalse($results->contains($alias2));
        $this->assertTrue($results->contains($alias3));
        $this->assertFalse($results->contains($otherAlias));
        $this->assertTrue($results->count() === 1);
    }

    public function testSenderAndRecipientMailbox()
    {
        /** @var Alias $alias */
        $alias = factory(Alias::class)->create();
        $mailbox = factory(Mailbox::class)->create();
        $alias->senderMailboxes()
            ->save($mailbox);
        $alias->addRecipientMailbox($mailbox);

        /** @var Alias $otherAlias */
        $otherAlias = factory(Alias::class)->create();
        $otherMailbox = factory(Mailbox::class)->create();
        $otherAlias->senderMailboxes()
            ->save($otherMailbox);
        $otherAlias->addRecipientMailbox($otherMailbox);

        $aliasFilter = $this->createAliasFilter(['senderAndRecipientMailbox' => $mailbox->id]);
        $results = $aliasFilter->apply(Alias::query())
            ->get();
        $this->assertTrue($results->contains($alias));
        $this->assertFalse($results->contains($otherAlias));
        $this->assertTrue($results->count() === 1);
    }

    public function testRecipientAddresses()
    {
        /** @var Alias $alias1 */
        $alias1 = factory(Alias::class)->create();
        /** @var Mailbox $mailbox1 */
        $mailbox1 = factory(Mailbox::class)->create();
        $alias1->addRecipientMailbox($mailbox1);

        /** @var Alias $alias2 */
        $alias2 = factory(Alias::class)->create();
        $externalAddress = 'foobar@some.other.domain';
        $alias2->addExternalRecipient($externalAddress);

        /** @var Alias $otherAlias */
        $otherAlias = factory(Alias::class)->create();
        $otherMailbox = factory(Mailbox::class)->create();
        $otherAlias->addRecipientMailbox($otherMailbox);

        $aliasFilter = $this->createAliasFilter([
            'recipientAddresses' => [
                $mailbox1->address(),
                $externalAddress
            ]
        ]);
        $results = $aliasFilter->apply(Alias::query())
            ->get();
        $this->assertTrue($results->contains($alias1));
        $this->assertTrue($results->contains($alias2));
        $this->assertFalse($results->contains($otherAlias));
        $this->assertTrue($results->count() === 2);
    }

    public function testRecipientAddress()
    {
        /** @var Alias $alias */
        $alias = factory(Alias::class)->create();
        $externalAddress = 'foobar@some.other.domain';
        $alias->addExternalRecipient($externalAddress);

        /** @var Alias $otherAlias */
        $otherAlias = factory(Alias::class)->create();
        $otherMailbox = factory(Mailbox::class)->create();
        $otherAlias->addRecipientMailbox($otherMailbox);

        $aliasFilter = $this->createAliasFilter(['recipientAddress' => $externalAddress]);
        $results = $aliasFilter->apply(Alias::query())
            ->get();
        $this->assertTrue($results->contains($alias));
        $this->assertFalse($results->contains($otherAlias));
        $this->assertTrue($results->count() === 1);
    }

    public function testActive()
    {
        $aliasWith = factory(Alias::class)->create(['active' => true]);
        $aliasWithout = factory(Alias::class)->create(['active' => false]);

        $this->assertAllBoolPossibilitiesFor('active', $aliasWith, $aliasWithout);
    }

    public function testHasDescription()
    {
        $aliasWith = factory(Alias::class)->create(['description' => 'foobar']);
        $aliasWithout = factory(Alias::class)->create(['description' => null]);

        $this->assertAllBoolPossibilitiesFor('hasDescription', $aliasWith, $aliasWithout);
    }

    public function testOrderById()
    {
        $alias1 = factory(Alias::class)->create(['local_part' => 'b']);
        $alias2 = factory(Alias::class)->create(['local_part' => 'c']);
        $alias3 = factory(Alias::class)->create(['local_part' => 'a']);

        $aliasFilter = $this->createAliasFilter(['orderById' => 'asc']);
        $query = $aliasFilter->apply(Alias::query());
        $results = $query->get();
        $this->assertTrue($alias1->is($results->get(0)));
        $this->assertTrue($alias2->is($results->get(1)));
        $this->assertTrue($alias3->is($results->get(2)));

        $aliasFilter = $this->createAliasFilter(['orderById' => 'desc']);
        $query = $aliasFilter->apply(Alias::query());
        $results = $query->get();
        $this->assertTrue($alias1->is($results->get(2)));
        $this->assertTrue($alias2->is($results->get(1)));
        $this->assertTrue($alias3->is($results->get(0)));
    }

    public function testOrderByLocalPart()
    {
        $aliasB = factory(Alias::class)->create(['local_part' => 'b']);
        $aliasC = factory(Alias::class)->create(['local_part' => 'c']);
        $aliasA = factory(Alias::class)->create(['local_part' => 'a']);

        $aliasFilter = $this->createAliasFilter(['orderByLocalPart' => 'asc']);
        $query = $aliasFilter->apply(Alias::query());
        $results = $query->get();
        $this->assertTrue($aliasA->is($results->get(0)));
        $this->assertTrue($aliasB->is($results->get(1)));
        $this->assertTrue($aliasC->is($results->get(2)));

        $aliasFilter = $this->createAliasFilter(['orderByLocalPart' => 'desc']);
        $query = $aliasFilter->apply(Alias::query());
        $results = $query->get();
        $this->assertTrue($aliasA->is($results->get(2)));
        $this->assertTrue($aliasB->is($results->get(1)));
        $this->assertTrue($aliasC->is($results->get(0)));
    }

    public function testSearch()
    {
        $otherAlias = factory(Alias::class)->create();
        $foobarDomain = factory(Domain::class)->create(['domain' => 'foobar.com']);
        $aliasLocalPart = factory(Alias::class)->create(['local_part' => 'foobarABC']);
        $aliasDomain = factory(Alias::class)->create(['domain_id' => $foobarDomain]);

        $aliasFilter = $this->createAliasFilter(['search' => 'foobar']);
        $query = $aliasFilter->apply(Alias::query());
        $results = $query->get();
        $this->assertTrue($results->contains($aliasLocalPart));
        $this->assertTrue($results->contains($aliasDomain));
        $this->assertFalse($results->contains($otherAlias));
        $this->assertTrue($results->count() === 2);
    }

    public function testSearchWithoutKeyword()
    {
        $alias1 = factory(Alias::class)->create();
        $alias2 = factory(Alias::class)->create();

        $aliasFilter = $this->createAliasFilter(['search' => '']);
        $query = $aliasFilter->apply(Alias::query());
        $results = $query->get();
        $this->assertTrue($results->contains($alias1));
        $this->assertTrue($results->contains($alias2));
        $this->assertTrue($results->count() === 2);
    }

    public function testAutomaticallyDeactivated()
    {
        $aliasWith = factory(Alias::class)->create([
            'deactivate_at' => Carbon::now()
                ->subMinute(),
            'active'        => false
        ]);
        $aliasWithout = factory(Alias::class)->create([
            'deactivate_at' => null,
            'active'        => true
        ]);

        $aliasFilter = $this->createAliasFilter(['automaticallyDeactivated' => null]);
        $query = $aliasFilter->apply(Alias::query());
        $results = $query->get();
        $this->assertTrue($results->contains($aliasWith));
        $this->assertFalse($results->contains($aliasWithout));
        $this->assertTrue($results->count() === 1);
    }
}
