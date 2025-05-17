<?php
namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Task;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;

class TaskTest extends TestCase
{
    use RefreshDatabase;

    public function test_manager_cannot_assign_task_to_admin()
    {
        $manager = User::factory()->create(['role' => 'manager']);
        $admin = User::factory()->create(['role' => 'admin']);

        Sanctum::actingAs($manager);

        $response = $this->postJson('/api/tasks', [
            'title' => 'Test Task',
            'description' => 'Some description',
            'assigned_to' => $admin->id,
            'due_date' => now()->addDay(),
            'status' => 'pending',
        ]);

        $response->assertStatus(403);
    }

    public function test_admin_can_create_task_for_anyone()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $staff = User::factory()->create(['role' => 'staff']);

        Sanctum::actingAs($admin);

        $response = $this->postJson('/api/tasks', [
            'title' => 'Admin Task',
            'description' => 'Assigned to staff',
            'assigned_to' => $staff->id,
            'due_date' => now()->addDay(),
            'status' => 'pending',
        ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('tasks', ['assigned_to' => $staff->id]);
    }
}
