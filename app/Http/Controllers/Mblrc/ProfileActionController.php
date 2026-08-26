<?php

namespace App\Http\Controllers\Mblrc;

use App\Http\Controllers\Controller;
use App\Models\FormerRebel;
use App\Models\FrGovernmentAssistance;
use App\Models\FrSkill;
use App\Services\AssistanceCertificateService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * AJAX/action endpoints backing the FR profile page widgets:
 * program status, geolocation, skills, assistance, education/work.
 */
class ProfileActionController extends Controller
{
    public function __construct(
        private readonly AssistanceCertificateService $certificates
    ) {}

    public function updateProgramStatus(Request $request, FormerRebel $formerRebel): JsonResponse
    {
        $data = $request->validate([
            'reintegration_status' => ['required', Rule::in(['Not-Started', 'On-going', 'Completed'])],
            'reintegration_date' => ['nullable', 'date'],
        ]);

        $formerRebel->programStatus()->updateOrCreate(
            ['former_rebel_id' => $formerRebel->id],
            [
                'reintegration_status' => $data['reintegration_status'],
                'reintegration_date' => $data['reintegration_date'] ?? null,
                'updated_by' => $request->user()->name,
            ]
        );

        return response()->json([
            'success' => true,
            'message' => 'The FR/FVE program status was updated successfully.',
        ]);
    }

    public function saveLocation(Request $request, FormerRebel $formerRebel): JsonResponse
    {
        $data = $request->validate([
            'placement_address' => ['required', 'string', 'max:255'],
            'latitude' => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
        ]);

        DB::transaction(function () use ($formerRebel, $data, $request) {
            $formerRebel->update([
                'placement_address' => $data['placement_address'],
                'latitude' => $data['latitude'],
                'longitude' => $data['longitude'],
            ]);
            $formerRebel->locationHistories()->create([
                'placement_address' => $data['placement_address'],
                'latitude' => $data['latitude'],
                'longitude' => $data['longitude'],
                'updated_by' => $request->user()->name,
            ]);
        });

        return response()->json(['success' => true]);
    }

    public function locationHistory(FormerRebel $formerRebel): JsonResponse
    {
        return response()->json(
            $formerRebel->locationHistories()->latest()->get()
        )->withHeaders($this->privateResponseHeaders());
    }

    public function storeSkill(Request $request, FormerRebel $formerRebel): JsonResponse
    {
        $data = $request->validate([
            'skill_name' => ['required', 'string', 'max:255'],
            'proficiency_level' => ['required', Rule::in(['Beginner', 'Intermediate', 'Advanced'])],
        ]);

        $skillName = trim($data['skill_name']);
        $skill = $formerRebel->skills()->updateOrCreate(
            ['skill_name' => $skillName],
            ['proficiency_level' => $data['proficiency_level']]
        );

        return response()->json(['success' => true, 'skill' => $skill]);
    }

    public function destroySkill(FrSkill $skill): JsonResponse
    {
        $skill->delete();

        return response()->json(['success' => true]);
    }

    /** Static vocational-skill autocomplete list (replaces get_skills_suggestions). */
    public function skillSuggestions(Request $request): JsonResponse
    {
        $all = [
            'Welding', 'Carpentry', 'Masonry', 'Plumbing', 'Electrical Installation',
            'Automotive Servicing', 'Driving', 'Farming', 'Livestock Raising', 'Fishing',
            'Cooking', 'Baking', 'Dressmaking', 'Tailoring', 'Hairdressing',
            'Computer Literacy', 'Electronics Repair', 'Handicrafts', 'Painting', 'Landscaping',
        ];
        $term = strtolower($request->query('term', ''));
        $matches = $term
            ? array_values(array_filter($all, fn ($s) => str_contains(strtolower($s), $term)))
            : $all;

        return response()->json($matches);
    }

    public function storeAssistance(Request $request, FormerRebel $formerRebel): JsonResponse
    {
        $data = $request->validate([
            'assistance_type' => ['required', 'string', 'max:255'],
            'date_received' => ['nullable', 'date'],
            'status' => ['nullable', Rule::in(['Pending', 'In Progress', 'Completed'])],
            'certificate' => ['nullable', 'file', 'mimes:jpg,jpeg,png,gif,pdf', 'max:25600'],
        ]);

        $path = $request->hasFile('certificate')
            ? $this->certificates->store($request->file('certificate'))
            : null;

        try {
            $assistance = $formerRebel->assistances()->create([
                'assistance_type' => trim($data['assistance_type']),
                'date_received' => $data['date_received'] ?? null,
                'status' => $data['status'] ?? 'Pending',
                'certificate_file' => $path,
            ]);
        } catch (\Throwable $exception) {
            $this->certificates->delete($path);

            throw $exception;
        }

        return response()->json(['success' => true, 'assistance' => $assistance]);
    }

    public function destroyAssistance(FrGovernmentAssistance $assistance): JsonResponse
    {
        if ($assistance->status === 'Completed' || $assistance->date_received || $assistance->certificate_file) {
            abort(422, 'Received, completed, or documented assistance records cannot be deleted.');
        }

        $this->certificates->delete($assistance->certificate_file);
        $assistance->delete();

        return response()->json(['success' => true]);
    }

    public function updateEducationWork(Request $request, FormerRebel $formerRebel): JsonResponse
    {
        $data = $request->validate([
            'educational_attainment' => ['nullable', 'string', 'max:255'],
            'occupation' => ['nullable', 'string', 'max:255'],
        ]);

        $formerRebel->educationWorks()->create($data);
        // keep the FR's denormalized occupation in sync
        $formerRebel->update(['occupation' => $data['occupation'] ?? null]);

        return response()->json(['success' => true]);
    }

    public function downloadAssistanceCertificate(FrGovernmentAssistance $assistance): StreamedResponse
    {
        abort_unless($assistance->certificate_file, 404);

        return $this->certificates->download($assistance->certificate_file);
    }

    private function privateResponseHeaders(): array
    {
        return [
            'Cache-Control' => 'no-store, private',
            'Pragma' => 'no-cache',
        ];
    }
}
