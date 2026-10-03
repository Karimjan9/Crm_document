<?php

namespace App\Http\Controllers;

use App\Models\Lead;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class BotRequestController extends Controller
{
    private const STATUS_LABELS = [
        'new' => 'Yangi', 'contacted' => 'Bog‘lanildi', 'qualified' => 'Aniqlashtirildi',
        'quoted' => 'Taklif yuborildi', 'won' => 'Buyurtmaga aylandi', 'lost' => 'Yopildi',
    ];

    public function index(Request $request)
    {
        $filters = $request->validate([
            'q' => ['nullable', 'string', 'max:120'],
            'status' => ['nullable', Rule::in(Lead::STATUSES)],
        ]);
        $base = Lead::query()->visibleTo($request->user())->whereHas('botIntakeRequest');
        $counts = (clone $base)->selectRaw('status, count(*) as total')->groupBy('status')->pluck('total', 'status');
        $query = (clone $base)->with(['botIntakeRequest', 'assignedTo:id,name', 'filial:id,name'])
            ->withCount(['telegramMessages as intake_files_count' => fn (Builder $query) => $query->where('direction', 'incoming')->whereNotNull('attachment_path')]);
        $search = trim($filters['q'] ?? '');
        if ($search !== '') {
            $query->where(function (Builder $query) use ($search): void {
                foreach (['name', 'phone', 'notes', 'interested_service', 'campaign'] as $field) {
                    $query->orWhere($field, 'like', '%'.$search.'%');
                }
                $query->orWhereHas('botIntakeRequest', fn (Builder $intake) => $intake
                    ->where('payload->customer->telegram_username', 'like', '%'.ltrim($search, '@').'%')
                    ->orWhere('payload->customer->phone', 'like', '%'.$search.'%'));
                if (ctype_digit($search)) {
                    $query->orWhere('leads.id', (int) $search);
                }
            });
        }
        $selectedStatus = $filters['status'] ?? '';
        if ($selectedStatus !== '') {
            $query->where('status', $selectedStatus);
        }

        return view('bot-requests.index', [
            'requests' => $query->latest('id')->paginate(20)->withQueryString(),
            'counts' => $counts, 'statuses' => self::STATUS_LABELS,
            'search' => $search, 'selectedStatus' => $selectedStatus,
        ]);
    }

    public function show(Request $request, Lead $lead)
    {
        $lead = Lead::query()->visibleTo($request->user())->whereHas('botIntakeRequest')->whereKey($lead->id)
            ->with(['botIntakeRequest', 'assignedTo:id,name', 'filial:id,name'])->firstOrFail();
        $attachments = $lead->telegramMessages()->where('direction', 'incoming')->whereNotNull('attachment_path')->oldest('id')->get();
        $messages = $lead->telegramMessages()->whereNull('attachment_path')->latest('id')->limit(20)->get()->reverse();

        return view('bot-requests.show', [
            'lead' => $lead, 'attachments' => $attachments, 'messages' => $messages,
            'statuses' => self::STATUS_LABELS, 'readOnly' => $request->user()->hasRole('super_admin'),
        ]);
    }
}
