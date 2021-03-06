<?php

use LaravelEnso\Core\app\Models\User;
use Faker\Factory;
use Tests\TestCase;
use LaravelEnso\TestHelper\app\Traits\SignIn;
use Illuminate\Foundation\Testing\RefreshDatabase;

class LogManagerTest extends TestCase
{
    use RefreshDatabase, SignIn;

    protected function setUp()
    {
        parent::setUp();

        // $this->withoutExceptionHandling();

        $this->faker = Factory::create();
        $this->log = 'laravel.log';

        $this->seed()
            ->signIn(User::first());
    }

    /** @test */
    public function index()
    {
        $this->addLogEntry();

        $this->get(route('system.logs.index', [], false))
            ->assertStatus(200);

        $this->cleanUp();
    }

    /** @test */
    public function show()
    {
        $this->addLogEntry();

        $this->get(route('system.logs.show', $this->log, false))
            ->assertStatus(200);

        $this->cleanUp();
    }

    /** @test */
    public function cant_view_if_file_exceeds_05_mb()
    {
        \Log::info($this->faker->words(30000));

        $this->get(route('system.logs.show', $this->log, false))
            ->assertJsonStructure(['message'])
            ->assertStatus(555);

        $this->cleanUp();
    }

    /** @test */
    public function download()
    {
        $this->addLogEntry();

        $response = $this->get(route('system.logs.download', $this->log, false))
            ->assertStatus(200);

        $this->assertEquals(
            storage_path('logs/'.$this->log),
            $response->getFile()->getRealPath()
        );

        $this->cleanUp();
    }

    /** @test */
    public function empty()
    {
        $this->addLogEntry();

        $this->delete(route('system.logs.destroy', $this->log, false))
            ->assertStatus(200);

        $this->assertEquals('', \File::get(storage_path('logs/'.$this->log)));
    }

    private function addLogEntry()
    {
        \Log::info($this->faker->word);
    }

    private function cleanUp()
    {
        $this->delete(route('system.logs.destroy', $this->log, false));
    }
}
