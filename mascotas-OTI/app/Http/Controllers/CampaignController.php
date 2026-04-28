<?php

namespace App\Http\Controllers;

use App\Models\Campaign;
use App\Models\CampaignParticipant;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

class CampaignController extends Controller
{
    /**
     * List campaigns.
     * CITIZEN: only PUBLISHED / IN_PROGRESS / FINISHED.
     * Staff: all.
     */
    public function index(Request $request): JsonResponse
    {
        $query = Campaign::with('campaignType', 'createdBy');

        // CITIZEN and guests only see public campaigns
        if (!$request->user() || $request->user()->hasRole('CITIZEN')) {
            $query->whereIn('status', ['PUBLISHED', 'IN_PROGRESS', 'FINISHED']);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        return response()->json($query->orderByDesc('scheduled_at')->paginate(15));
    }

    /**
     * Show a single campaign.
     */
    public function show(Campaign $campaign): JsonResponse
    {
        return response()->json(
            $campaign->load('campaignType', 'createdBy', 'participants.user')
        );
    }

    /**
     * Create a new campaign.
     */
    public function store(Request $request): JsonResponse
    {
        Gate::authorize('create', Campaign::class);

        $data = $request->validate([
            'title'            => 'required|string|max:150',
            'campaign_type_id' => 'required|exists:campaign_types,id',
            'description'      => 'nullable|string',
            'scheduled_at'     => 'required|date|after_or_equal:today',
            'location'         => 'nullable|string|max:200',
            'capacity'         => 'nullable|integer|min:1',
            'target_audience'  => 'nullable|string',
            'requirements'     => 'nullable|string',
        ]);

        $data['created_by'] = Auth::id();
        $data['status']     = 'DRAFT';

        $campaign = Campaign::create($data);

        return response()->json($campaign->load('campaignType'), 201);
    }

    /**
     * Update campaign data.
     */
    public function update(Request $request, Campaign $campaign): JsonResponse
    {
        Gate::authorize('update', $campaign);

        if (in_array($campaign->status, ['FINISHED', 'CANCELLED'])) {
            return response()->json(['message' => 'No se puede modificar una campaña finalizada o cancelada.'], 422);
        }

        $data = $request->validate([
            'title'            => 'sometimes|required|string|max:150',
            'campaign_type_id' => 'sometimes|required|exists:campaign_types,id',
            'description'      => 'nullable|string',
            'scheduled_at'     => 'sometimes|required|date',
            'location'         => 'nullable|string|max:200',
            'capacity'         => 'nullable|integer|min:1',
            'target_audience'  => 'nullable|string',
            'requirements'     => 'nullable|string',
        ]);

        $campaign->update($data);

        return response()->json($campaign->fresh('campaignType'));
    }

    /**
     * Change campaign status (publish, start, finish, cancel).
     */
    public function updateStatus(Request $request, Campaign $campaign): JsonResponse
    {
        Gate::authorize('updateStatus', $campaign);

        $data = $request->validate([
            'status' => 'required|in:DRAFT,PUBLISHED,IN_PROGRESS,FINISHED,CANCELLED',
        ]);

        $campaign->update(['status' => $data['status']]);

        return response()->json($campaign);
    }

    /**
     * Register a participant for a campaign.
     */
    public function registerParticipant(Request $request, Campaign $campaign): JsonResponse
    {
        Gate::authorize('registerParticipant', $campaign);

        $data = $request->validate([
            'user_id'   => 'required|exists:users,id',
            'animal_id' => 'nullable|exists:animals,id',
        ]);

        // Check capacity
        if ($campaign->capacity && $campaign->participants()->count() >= $campaign->capacity) {
            return response()->json(['message' => 'La campaña ha alcanzado su capacidad máxima.'], 422);
        }

        $participant = CampaignParticipant::firstOrCreate([
            'campaign_id' => $campaign->id,
            'user_id'     => $data['user_id'],
            'animal_id'   => $data['animal_id'] ?? null,
        ]);

        return response()->json($participant->load('user', 'animal'), 201);
    }

    /**
     * Mark attendance for a participant.
     */
    public function markAttendance(Request $request, Campaign $campaign): JsonResponse
    {
        Gate::authorize('markAttendance', $campaign);

        $data = $request->validate([
            'participant_id' => 'required|exists:campaign_participants,id',
            'attended'       => 'required|boolean',
        ]);

        $participant = CampaignParticipant::where('campaign_id', $campaign->id)
            ->findOrFail($data['participant_id']);

        $participant->update(['attended' => $data['attended']]);

        return response()->json($participant);
    }
}
