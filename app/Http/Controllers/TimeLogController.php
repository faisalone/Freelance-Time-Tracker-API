<?php

namespace App\Http\Controllers;

use App\Actions\TimeLog\StartTimeLogAction;
use App\Actions\TimeLog\StopTimeLogAction;
use App\Http\Requests\TimeLogRequest;
use App\Http\Resources\TimeLogResource;
use App\Models\Project;
use App\Models\TimeLog;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Spatie\QueryBuilder\QueryBuilder;

class TimeLogController extends Controller
{
    use AuthorizesRequests;

    public function __construct(
        private readonly StartTimeLogAction $startTimeLogAction,
        private readonly StopTimeLogAction $stopTimeLogAction
    ) {}

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        
        $timeLogs = QueryBuilder::for(TimeLog::class)
            ->with(['project.client'])
            ->whereHas('project.client', function ($query) use ($user) {
                $query->where('user_id', $user->id);
            })
            ->allowedFilters(['project_id', 'is_billable', 'tags'])
            ->allowedSorts(['start_time', 'created_at', 'hours'])
            ->allowedIncludes(['project', 'project.client'])
            ->paginate($request->query('per_page', 15));

        return response()->json([
            'data' => TimeLogResource::collection($timeLogs),
            'meta' => [
                'current_page' => $timeLogs->currentPage(),
                'last_page' => $timeLogs->lastPage(),
                'per_page' => $timeLogs->perPage(),
                'total' => $timeLogs->total(),
            ],
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(TimeLogRequest $request): JsonResponse
    {
        $project = Project::findOrFail($request->project_id);
        $this->authorize('view', $project->client);

        $data = $request->validated();
        // Ensure start_time is set for running logs
        if (empty($data['start_time']) && empty($data['end_time'])) {
            $data['start_time'] = now();
        }
        $timeLog = TimeLog::create($data);
        // Calculate hours if both start and end times are provided
        if ($timeLog->start_time && $timeLog->end_time) {
            $timeLog->calculateHours();
            $timeLog->save();
        }

        return response()->json([
            'message' => 'Time log created successfully',
            'data' => new TimeLogResource($timeLog->load('project')),
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, TimeLog $timeLog): JsonResponse
    {
        $this->authorize('view', $timeLog->project->client);

        return response()->json([
            'data' => new TimeLogResource($timeLog->load('project.client')),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(TimeLogRequest $request, TimeLog $timeLog): JsonResponse
    {
        $this->authorize('update', $timeLog->project->client);

        $timeLog->update($request->validated());
        
        // Recalculate hours if times were updated
        if ($timeLog->start_time && $timeLog->end_time) {
            $timeLog->calculateHours();
            $timeLog->save();
        }

        return response()->json([
            'message' => 'Time log updated successfully',
            'data' => new TimeLogResource($timeLog->load('project')),
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, TimeLog $timeLog): JsonResponse
    {
        $this->authorize('delete', $timeLog->project->client);

        $timeLog->delete();

        return response()->json(null, 204);
    }

    /**
     * Start a new time log for a project.
     */
    public function start(Request $request): JsonResponse
    {
        $request->validate([
            'project_id' => ['required', 'exists:projects,id'],
            'description' => ['nullable', 'string'],
        ]);

        $project = Project::query()
            ->where('id', $request->project_id)
            ->with('client')
            ->firstOrFail();

        $this->authorize('view', $project->client);

        $timeLog = $this->startTimeLogAction->execute($project, $request->description);

        return response()->json([
            'message' => 'Time log started successfully',
            'data' => new TimeLogResource($timeLog->load('project')),
        ], 201);
    }

    /**
     * Stop a running time log.
     */
    public function stop(Request $request, TimeLog $timeLog): JsonResponse
    {
        $this->authorize('update', $timeLog->project->client);

        $timeLog = $this->stopTimeLogAction->execute($timeLog);

        return response()->json([
            'message' => 'Time log stopped successfully',
            'data' => new TimeLogResource($timeLog->load('project')),
        ]);
    }

    /**
     * Get all running time logs for the authenticated user.
     */
    public function running(Request $request): JsonResponse
    {
        $user = $request->user();
        
        $runningLogs = TimeLog::query()
            ->whereHas('project.client', function ($query) use ($user) {
                $query->where('user_id', $user->id);
            })
            ->running()
            ->with('project.client')
            ->get();

        return response()->json([
            'data' => TimeLogResource::collection($runningLogs),
        ]);
    }
}
