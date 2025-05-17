<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTaskRequest;
use App\Models\Task;
use App\Services\TaskService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Gate;
use Symfony\Component\HttpFoundation\StreamedResponse;

class TaskController extends Controller
{
    protected $taskService;

    /**
     * Create a new controller instance.
     *
     * @param TaskService $taskService
     */
    public function __construct(TaskService $taskService)
    {
        $this->taskService = $taskService;
    }

    /**
     * Display a listing of the tasks.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $tasks = $this->taskService->getTasksForUser($request->user());

        return response()->json([
            'tasks' => $tasks,
        ]);
    }

    /**
     * Store a newly created task in storage.
     *
     * @param StoreTaskRequest $request
     * @return JsonResponse
     */
    public function store(StoreTaskRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $task = $this->taskService->createTask($validated, $request->user());

        return response()->json([
            'message' => 'Task created successfully',
            'task' => $task,
        ], 201);
    }

    /**
     * Display the specified task.
     *
     * @param Task $task
     * @return JsonResponse
     */
    public function show(Task $task): JsonResponse
    {
        if (Gate::denies('view', $task)) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        return response()->json([
            'task' => $task->load(['assignedUser', 'creator']),
        ]);
    }

    /**
     * Update the specified task in storage.
     *
     * @param Request $request
     * @param Task $task
     * @return JsonResponse
     */
    public function update(Request $request, Task $task): JsonResponse
    {
        if (Gate::denies('update', $task)) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'title' => 'sometimes|string|max:255',
            'description' => 'sometimes|string',
            'assigned_to' => 'sometimes|uuid|exists:users,id',
            'status' => 'sometimes|in:pending,in_progress,done',
            'due_date' => 'sometimes|date',
        ]);

        $task = $this->taskService->updateTask($task, $validated, $request->user());

        return response()->json([
            'message' => 'Task updated successfully',
            'task' => $task->load(['assignedUser', 'creator']),
        ]);
    }

    /**
     * Remove the specified task from storage.
     *
     * @param Task $task
     * @return JsonResponse
     */
    public function destroy(Task $task): JsonResponse
    {
        if (Gate::denies('delete', $task)) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $this->taskService->deleteTask($task, request()->user());

        return response()->json([
            'message' => 'Task deleted successfully',
        ]);
    }

    /**
     * Export tasks to CSV.
     *
     * @param Request $request
     * @return StreamedResponse
     */
    public function export(Request $request): StreamedResponse
    {
        $csv = $this->taskService->exportTasksToCsv($request->user());

        return response()->streamDownload(function () use ($csv) {
            echo $csv;
        }, 'tasks.csv', [
            'Content-Type' => 'text/csv',
        ]);
    }
}
