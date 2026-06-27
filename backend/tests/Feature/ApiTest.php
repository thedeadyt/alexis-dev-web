<?php
namespace Tests\Feature;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Database\Seeders\ProjectSeeder;
use Database\Seeders\ServiceSeeder;
use Database\Seeders\TestimonialSeeder;

class ApiTest extends TestCase {
    use RefreshDatabase;

    protected function setUp(): void {
        parent::setUp();
        $this->seed([ProjectSeeder::class, ServiceSeeder::class, TestimonialSeeder::class]);
    }

    public function test_projects_returns_array(): void {
        $response = $this->getJson('/api/projects');
        $response->assertStatus(200)->assertJsonIsArray();
    }

    public function test_project_by_slug(): void {
        $response = $this->getJson('/api/projects/boulangerie-martin');
        $response->assertStatus(200)->assertJsonFragment(['slug' => 'boulangerie-martin']);
    }

    public function test_project_not_found(): void {
        $this->getJson('/api/projects/inexistant')->assertStatus(404);
    }

    public function test_services_returns_array(): void {
        $this->getJson('/api/services')->assertStatus(200)->assertJsonIsArray();
    }

    public function test_testimonials_returns_array(): void {
        $this->getJson('/api/testimonials')->assertStatus(200)->assertJsonIsArray();
    }

    public function test_contact_validates_required_fields(): void {
        $this->postJson('/api/contact', [])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['first_name', 'email']);
    }

    public function test_contact_stores_on_valid_data(): void {
        $this->postJson('/api/contact', [
            'first_name' => 'Jean',
            'email' => 'jean@test.com',
            'type' => 'Site vitrine',
            'budget' => '800€',
        ])->assertStatus(200)->assertJson(['success' => true]);
        $this->assertDatabaseHas('contacts', ['email' => 'jean@test.com']);
    }
}
