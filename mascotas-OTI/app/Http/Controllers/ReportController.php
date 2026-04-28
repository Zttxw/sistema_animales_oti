<?php

namespace App\Http\Controllers;

use App\Models\Animal;
use App\Models\Vaccination;
use App\Models\Campaign;
use App\Models\StrayAnimal;
use App\Models\Adoption;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    /**
     * Dashboard general con KPIs del sistema.
     */
    public function dashboard()
    {
        return response()->json([
            'animals' => [
                'total'        => Animal::count(),
                'active'       => Animal::where('status', 'ACTIVE')->count(),
                'lost'         => Animal::where('status', 'LOST')->count(),
                'for_adoption' => Animal::where('status', 'FOR_ADOPTION')->count(),
                'deceased'     => Animal::where('status', 'DECEASED')->count(),
            ],
            'stray_animals' => [
                'total'        => StrayAnimal::count(),
                'observed'     => StrayAnimal::where('status', 'OBSERVED')->count(),
                'rescued'      => StrayAnimal::where('status', 'RESCUED')->count(),
                'in_treatment' => StrayAnimal::where('status', 'IN_TREATMENT')->count(),
            ],
            'adoptions' => [
                'available'  => Adoption::where('status', 'AVAILABLE')->count(),
                'in_process' => Adoption::where('status', 'IN_PROCESS')->count(),
                'completed'  => Adoption::where('status', 'ADOPTED')->count(),
            ],
            'campaigns' => [
                'total'       => Campaign::count(),
                'upcoming'    => Campaign::upcoming()->count(),
                'finished'    => Campaign::where('status', 'FINISHED')->count(),
            ],
            'vaccinations_pending' => Vaccination::whereNotNull('next_dose_at')
                ->where('next_dose_at', '<=', today()->addDays(30))
                ->count(),
            'users_total' => User::where('status', 'ACTIVE')->count(),
        ]);
    }

    /**
     * Animales registrados por especie.
     */
    public function animalsBySpecies()
    {
        $data = Animal::select('species_id', DB::raw('count(*) as total'))
            ->with('species:id,name')
            ->groupBy('species_id')
            ->get()
            ->map(fn($row) => [
                'species' => $row->species->name ?? 'Sin especie',
                'total'   => $row->total,
            ]);

        return response()->json($data);
    }

    /**
     * Animales registrados por mes (último año).
     */
    public function animalsPerMonth()
    {
        $data = Animal::select(
                DB::raw('YEAR(created_at) as year'),
                DB::raw('MONTH(created_at) as month'),
                DB::raw('COUNT(*) as total')
            )
            ->where('created_at', '>=', now()->subYear())
            ->groupBy('year', 'month')
            ->orderBy('year')
            ->orderBy('month')
            ->get();

        return response()->json($data);
    }

    /**
     * Participación por campaña.
     */
    public function campaignParticipation(Request $request)
    {
        $campaigns = Campaign::select('id', 'title', 'scheduled_at', 'status')
            ->withCount('participants')
            ->withCount(['participants as attended_count' => fn($q) => $q->where('attended', true)])
            ->when($request->from, fn($q) => $q->where('scheduled_at', '>=', $request->from))
            ->when($request->to,   fn($q) => $q->where('scheduled_at', '<=', $request->to))
            ->orderByDesc('scheduled_at')
            ->get();

        return response()->json($campaigns);
    }

    /**
     * Vacunas próximas a vencer agrupadas por semana.
     */
    public function upcomingVaccinations()
    {
        $data = Vaccination::with(['animal:id,name,municipal_code', 'animal.owner:id,first_name,last_name,phone'])
            ->whereNotNull('next_dose_at')
            ->where('next_dose_at', '<=', today()->addDays(60))
            ->orderBy('next_dose_at')
            ->get()
            ->groupBy(fn($v) => $v->next_dose_at->format('Y-W')); // Agrupar por semana

        return response()->json($data);
    }

    /**
     * Reporte de animales callejeros por estado y zona.
     */
    public function strayAnimalsSummary()
    {
        $byStatus = StrayAnimal::select('status', DB::raw('count(*) as total'))
            ->groupBy('status')
            ->get();

        $bySpecies = StrayAnimal::select('species_id', DB::raw('count(*) as total'))
            ->with('species:id,name')
            ->groupBy('species_id')
            ->get()
            ->map(fn($r) => ['species' => $r->species->name ?? 'Desconocida', 'total' => $r->total]);

        return response()->json([
            'by_status'  => $byStatus,
            'by_species' => $bySpecies,
        ]);
    }
}