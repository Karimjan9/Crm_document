<?php

namespace App\Http\Controllers;

use App\Models\FilialModel;
use App\Models\IntakeOcrDocument;
use App\Models\IntakeSession;
use App\Models\ServicesModel;
use App\Services\DocumentOcrService;
use App\Services\SmartIntakeService;
use Illuminate\Http\Request;

class SmartIntakeController extends Controller
{
    public function __construct(
        private readonly SmartIntakeService $intake,
        private readonly DocumentOcrService $ocr,
    )
    {
    }

    public function start(Request $request)
    {
        $session = $this->intake->start($request->query('session'));
        $session->load('ocrDocuments');
        $services = ServicesModel::query()->with('addons:id,service_id,name,price,deadline')->orderBy('name')->get(['id', 'name', 'description', 'price', 'deadline']);
        $filials = FilialModel::query()->orderBy('name')->get(['id', 'name']);

        return view('intake.start', compact('session', 'services', 'filials'));
    }

    public function analyze(Request $request)
    {
        $data = $request->validate([
            'session' => ['nullable', 'string', 'exists:intake_sessions,token'],
            'filial_id' => ['nullable', 'integer', 'exists:filial,id'],
            'service_id' => ['nullable', 'integer', 'exists:services,id'],
            'query' => ['nullable', 'string', 'max:500'],
            'addon_ids' => ['nullable', 'array'],
            'addon_ids.*' => ['integer', 'exists:service_addons,id'],
            'required_files' => ['nullable', 'array', 'max:20'],
            'required_files.*' => ['string', 'max:160'],
            'needs_original' => ['nullable', 'boolean'],
            'needs_translation' => ['nullable', 'boolean'],
        ]);

        $session = ($data['session'] ?? null)
            ? $this->intake->start($data['session'])
            : $this->intake->start();
        $session->filial_id = $data['filial_id'] ?? $session->filial_id;
        $session->save();
        $result = $this->intake->analyze($data, $session);

        if ($request->expectsJson()) {
            return response()->json([
                'data' => [
                    'session' => $result->token,
                    'service' => $result->recommendedService,
                    'estimated_price' => (float) $result->estimated_price,
                    'estimated_deadline_days' => $result->estimated_deadline_days,
                    'required_files' => $result->required_files,
                    'recommended_addons' => $result->recommended_addons,
                ],
            ]);
        }

        return redirect()->route('intake.start', ['session' => $result->token]);
    }

    public function uploadOcr(Request $request, string $token)
    {
        $session = IntakeSession::query()->where('token', $token)->firstOrFail();
        $data = $request->validate([
            'file' => ['required', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:10240'],
        ]);

        $record = $this->ocr->upload($session, $data['file'], $request->user());

        if ($request->expectsJson()) {
            return response()->json(['data' => $record], 201);
        }

        return redirect()->route('intake.start', ['session' => $token])
            ->with('success', 'OCR fayli qabul qilindi. Inson tasdig\'idan keyin ma\'lumot ishlatiladi.');
    }

    public function approveOcr(Request $request, string $token, IntakeOcrDocument $ocr)
    {
        $session = IntakeSession::query()->where('token', $token)->firstOrFail();
        abort_unless((int) $ocr->intake_session_id === (int) $session->id, 404);
        if (! $request->user()->hasAnyRole(['super_admin', 'admin_manager'])) {
            abort_unless($session->filial_id !== null && (int) $session->filial_id === (int) $request->user()->filial_id, 403);
        }
        $data = $request->validate([
            'approval_note' => ['nullable', 'string', 'max:1000'],
            'extracted_data' => ['nullable', 'array'],
        ]);

        $record = $this->ocr->approve(
            $ocr,
            $request->user(),
            $data['extracted_data'] ?? null,
            $data['approval_note'] ?? null,
        );

        if ($request->expectsJson()) {
            return response()->json(['data' => $record]);
        }

        return redirect()->route('intake.start', ['session' => $token])
            ->with('success', 'OCR natijasi inson tomonidan tasdiqlandi.');
    }
}
