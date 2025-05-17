<?php

namespace App\Services;

use App\Models\Task;
use App\Models\User;
use App\Models\ActivityLog;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;

class TaskService
{
    /**
     * Create a new task
     *
     * @param array $data
     * @param User $creator
     * @return Task
     */
    public function createTask(array $data, User $creator): Task
    {
        // Validate that assignment follows business rules
        $this->validateTaskAssignment($creator, $data['assigned_to']);

        $task = Task::create([
            'title' => $data['title'],
            'description' => $data['description'],
            'assigned_to' => $data['assigned_to'],
            'status' => $data['status'] ?? Task::STATUS_PENDING,
            'due_date' => $data['due_date'],
            'created_by' => $creator->id,
        ]);


        ActivityLog::log(
            $creator->id,
            'create_task',
            "Created task: {$task->title}"
        );

        return $task;
    }

    /**
     * Update an existing task
     *
     * @param Task $task
     * @param array $data
     * @param User $user
     * @return Task
     */
    public function updateTask(Task $task, array $data, User $user): Task
    {

        if (isset($data['assigned_to']) && $data['assigned_to'] !== $task->assigned_to) {
            $this->validateTaskAssignment($user, $data['assigned_to']);
        }

        $task->title = $data['title'] ?? $task->title;
        $task->description = $data['description'] ?? $task->description;
        $task->assigned_to = $data['assigned_to'] ?? $task->assigned_to;
        $task->status = $data['status'] ?? $task->status;
        $task->due_date = $data['due_date'] ?? $task->due_date;

        $task->save();


        ActivityLog::log(
            $user->id,
            'update_task',
            "Updated task: {$task->title}"
        );

        return $task;
    }

    /**
     * Delete a task
     *
     * @param Task $task
     * @param User $user
     * @return bool
     */
    public function deleteTask(Task $task, User $user): bool
    {
        $title = $task->title;
        $result = $task->delete();

        // Log the activity
        ActivityLog::log(
            $user->id,
            'delete_task',
            "Deleted task: {$title}"
        );

        return $result;
    }

    /**
     * Get tasks based on user role
     *
     * @param User $user
     * @return Collection
     */
    public function getTasksForUser(User $user): Collection
    {
        return Task::forRole($user)->with(['assignedUser', 'creator'])->get();
    }

    /**
     * Export tasks to CSV
     *
     * @param User $user
     * @return string
     */
    public function exportTasksToCsv(User $user): string
    {
        $tasks = $this->getTasksForUser($user);


        $csv = "ID,Title,Description,Assigned To,Status,Due Date,Created By\n";

        foreach ($tasks as $task) {
            $csv .= sprintf(
                '"%s","%s","%s","%s","%s","%s","%s"' . "\n",
                $task->id,
                str_replace('"', '""', $task->title),
                str_replace('"', '""', $task->description),
                $task->assignedUser->name,
                $task->status,
                $task->due_date->format('Y-m-d'),
                $task->creator->name
            );
        }

        return $csv;
    }

    /**
     * Check for overdue tasks and log them
     *
     * @return int Number of overdue tasks logged
     */
    public function checkOverdueTasks(): int
    {
        $overdueTasks = Task::overdue()->get();
        $count = 0;

        foreach ($overdueTasks as $task) {
            ActivityLog::logTaskOverdue($task);
            $count++;
        }

        return $count;
    }

    /**
     * Validate that task assignment follows business rules
     *
     * @param User $assigner
     * @param string $assigneeId
     * @return bool
     * @throws ValidationException
     */
    private function validateTaskAssignment(User $assigner, string $assigneeId): bool
    {
        $assignee = User::find($assigneeId);

        if (!$assignee) {
            throw ValidationException::withMessages([
                'assigned_to' => ['The assigned user does not exist.'],
            ]);
        }


        if ($assigner->isAdmin()) {
            return true;
        }


        if ($assigner->isManager() && $assignee->isStaff()) {
            return true;
        }


        if ($assigner->isStaff() && $assigner->id === $assignee->id) {
            return true;
        }

        throw ValidationException::withMessages([
            'assigned_to' => ['You are not authorized to assign tasks to this user.'],
        ]);
    }
}
