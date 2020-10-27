<?php

namespace Tests\Feature\Traits;

use App\Exceptions\HttpRequestFailedException;
use App\Exceptions\IntegrationsDisabledException;
use App\Exceptions\InvalidUrlException;
use App\Interfaces\Integratable;
use App\Traits\IntegratesWebHooks;
use App\WebHookIntegration;
use function factory;
//use Http\Client\Exception\RequestException;
//use Http\Httplug\Facade\Httplug;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Mockery;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class IntegratesWebHooksTest extends TestCase
{
    use WithFaker, RefreshDatabase;

//    /** @var IntegratesWebHooks */
//    protected $integrator;
//
//    /** @var Integratable */
//    protected $integratable;
//
//    protected $mockRequest;
//
//    protected function setUp(): void
//    {
//        parent::setUp();
//
//        $this->integrator = new class {
//            use IntegratesWebHooks;
//        };
//
//        $this->integratable = Mockery::mock(Integratable::class);
//        $this->integratable->shouldReceive('getIntegratablePlaceholders')
//            ->andReturn([
//                'id'  => '42',
//                'foo' => 'bar'
//            ]);
//        $this->integratable->shouldReceive('getIntegratableClassName')
//            ->andReturn(Integratable::class);
//
//        $mockRequest = Mockery::mock();
//        $this->mockRequest = $mockRequest;
//
//        App::singleton('httplug.message_factory.default', function () use ($mockRequest) {
//            $mockFactory = Mockery::mock();
//            $mockFactory->shouldReceive('createRequest')
//                ->withArgs([
//                    'GET',
//                    'http://example.com/bar?id=42'
//                ])
//                ->andReturn($mockRequest);
//            return $mockFactory;
//        });
//    }
//
//    public function testDisabledIntegrations()
//    {
//        Config::set('integrations.enabled.generally', false);
//        Config::set('integrations.enabled.web_hooks', true);
//
//        $integration = factory(WebHookIntegration::class)->create([
//            'model_class' => $this->integratable->getIntegratableClassName(),
//            'value'       => 'http://localhost'
//        ]);
//
//        $this->expectException(IntegrationsDisabledException::class);
//
//        $this->integrator->callWebHook($integration);
//    }
//
//    public function testDisabledWebHooks()
//    {
//        Config::set('integrations.enabled.generally', true);
//        Config::set('integrations.enabled.web_hooks', false);
//
//        $integration = factory(WebHookIntegration::class)->create([
//            'model_class' => $this->integratable->getIntegratableClassName(),
//            'value'       => 'http://localhost'
//        ]);
//
//        $this->expectException(IntegrationsDisabledException::class);
//
//        $this->integrator->callWebHook($integration);
//    }
//
//    public function testCallWebHook()
//    {
//        Config::set('integrations.enabled.generally', true);
//        Config::set('integrations.enabled.web_hooks', true);
//
//        $integration = factory(WebHookIntegration::class)->create([
//            'model_class' => $this->integratable->getIntegratableClassName(),
//            'value'       => 'http://example.com/%{foo}?id=%{id}'
//        ]);
//
//        $mockResponse = Mockery::mock();
//        $mockResponse->shouldReceive('getStatusCode')
//            ->andReturn(200);
//
//        Httplug::shouldReceive('sendRequest')
//            ->with($this->mockRequest)
//            ->andReturn($mockResponse);
//
//        $response = $this->integrator->callWebHook($integration, $this->integratable->getIntegratablePlaceholders());
//        $this->assertEquals($mockResponse, $response);
//    }
//
//    public function testCallWebHookFailedNotFound()
//    {
//        Config::set('integrations.enabled.generally', true);
//        Config::set('integrations.enabled.web_hooks', true);
//
//        $integration = factory(WebHookIntegration::class)->create([
//            'model_class' => $this->integratable->getIntegratableClassName(),
//            'value'       => 'http://example.com/%{foo}?id=%{id}'
//        ]);
//
//        $mockResponse = Mockery::mock();
//        $mockResponse->shouldReceive('getStatusCode')
//            ->andReturn(404);
//
//        Httplug::shouldReceive('sendRequest')
//            ->with($this->mockRequest)
//            ->andReturn($mockResponse);
//
//        $this->expectException(HttpRequestFailedException::class);
//
//        $this->integrator->callWebHook($integration, $this->integratable->getIntegratablePlaceholders());
//    }
//
//    public function testCallWebHookInvalidUrl()
//    {
//        Config::set('integrations.enabled.generally', true);
//        Config::set('integrations.enabled.web_hooks', true);
//
//        DB::table('integrations')
//            ->insert([
//                'model_class' => $this->integratable->getIntegratableClassName(),
//                'value'       => 'INVALID URL',
//                'type'        => WebHookIntegration::class
//            ]);
//
//        $integration = WebHookIntegration::query()
//            ->firstOrFail();
//
//        $this->expectException(InvalidUrlException::class);
//
//        $this->integrator->callWebHook($integration, $this->integratable->getIntegratablePlaceholders());
//    }
//
//    public function testCallWebHookRequestException()
//    {
//        Config::set('integrations.enabled.generally', true);
//        Config::set('integrations.enabled.web_hooks', true);
//
//        $integration = factory(WebHookIntegration::class)->create([
//            'model_class' => $this->integratable->getIntegratableClassName(),
//            'value'       => 'http://example.com/%{foo}?id=%{id}'
//        ]);
//
//        $mockRequestException = Mockery::mock(RequestException::class);
//        $mockRequestException->shouldReceive('getRequest')
//            ->andReturn('fake mock response');
//
//        Httplug::shouldReceive('sendRequest')
//            ->with($this->mockRequest)
//            ->andThrow($mockRequestException);
//
//        $this->expectException(HttpRequestFailedException::class);
//
//        $this->integrator->callWebHook($integration, $this->integratable->getIntegratablePlaceholders());
//    }
}
