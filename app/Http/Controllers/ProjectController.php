<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProjectRequest;
use App\Http\Resources\ProjectResource;
use App\Models\Client;
use App\Models\Project;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Spatie\QueryBuilder\QueryBuilder;

class ProjectController extends Controller
{
    use AuthorizesRequests;

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        
        $projects = QueryBuilder::for(Project::class)
            ->whereHas('client', function ($query) use ($user) {
                $query->where('user_id', $user->id);
            })
            ->allowedFilters(['client_id', 'status', 'title'])
            ->allowedSorts(['title', 'deadline', 'created_at', 'status'])
            ->allowedIncludes(['client', 'timeLogs'])
            ->with('client') // Always load client relationship
            ->withCount('timeLogs')
            ->paginate($request->query('per_page', 15));

        return response()->json([
            'data' => ProjectResource::collection($projects),
            'meta' => [
                'current_page' => $projects->currentPage(),
                'last_page' => $projects->lastPage(),
                'per_page' => $projects->perPage(),
                'total' => $projects->total(),
            ],
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(ProjectRequest $request): JsonResponse
    {
        \Log::info('Request reached ProjectController@store', [
            'headers' => $request->headers->all(),
            'content_type' => $request->header('Content-Type'),
            'accept' => $request->header('Accept'),
            'data' => $request->all()
        ]);
        
        $client = Client::findOrFail($request->client_id);
        $this->authorize('view', $client);

        $project = Project::create($request->validated());

        return response()->json([
            'message' => 'Project created successfully',
            'data' => new ProjectResource($project->load('client')),
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, Project $project): JsonResponse
    {
        $this->authorize('view', $project->client);

        $project->load('client', 'timeLogs');

        return response()->json([
            'data' => new ProjectResource($project),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(ProjectRequest $request, Project $project): JsonResponse
    {
        $this->authorize('update', $project->client);

        $project->update($request->validated());

        return response()->json([
            'message' => 'Project updated successfully',
            'data' => new ProjectResource($project->load('client')),
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, Project $project): JsonResponse
    {
        $this->authorize('delete', $project->client);

        $project->delete();

        return response()->json(null, 204);
    }
}
