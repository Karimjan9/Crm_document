<?php

namespace App\Http\Controllers;

use App\Models\FilialModel;
use App\Models\Lead;
use App\Models\User;
use App\Services\LeadService;
use App\Services\TelegramBotIntegrationService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class LeadController extends Controller
{
    public function __construct(private readonly LeadService $leads, private readonly TelegramBotIntegrationService $bot) {}
    public function index(Request $request)
    {
        $leads = Lead::query()->visibleTo($request->user())->with(['assignedTo:id,name', 'activities', 'telegramMessages'])->when($request->filled('status'), fn ($q) => $q->where('status', $request->input('status')))->latest('id')->paginate(30)->withQueryString();
        return view('leads.index', ['leads' => $leads, 'statuses' => Lead::STATUSES, 'filials' => $this->filials($request), 'users' => $this->users($request)]);
    }
    public function store(Request $request)
    {
        $this->ensureWritable($request);
        $lead = $this->leads->save($this->validated($request), $request->user());
        return redirect()->route('leads.index')->with('success', "Lead #{$lead->id} yaratildi.");
    }
    public function update(Request $request, Lead $lead)
    {
        $this->ensureWritable($request);
        $this->visible($request, $lead);
        $this->leads->save($this->validated($request), $request->user(), $lead);
        return back()->with('success', 'Lead yangilandi.');
    }
    public function activity(Request $request, Lead $lead)
    {
        $this->ensureWritable($request);
        $this->visible($request, $lead);
        $data = $request->validate(['type' => ['required', Rule::in(['call', 'message', 'meeting', 'note'])], 'body' => ['required', 'string', 'max:2000']]);
        $this->leads->addActivity($lead, $request->user(), $data['type'], $data['body']);
        return back()->with('success', 'Aloqa tarixi saqlandi.');
    }
    public function convert(Request $request, Lead $lead)
    {
        $this->ensureWritable($request);
        $this->visible($request, $lead);
        abort_if(in_array($lead->status, ['won', 'lost'], true), 422);
        $data = $request->validate(['title' => ['nullable', 'string', 'max:255'], 'priority' => ['nullable', Rule::in(['low', 'normal', 'high', 'urgent'])], 'promised_at' => ['nullable', 'date']]);
        $order = $this->leads->convert($lead, $request->user(), $data);
        return redirect()->route('orders.show', $order)->with('success', 'Lead orderga aylantirildi.');
    }
    public function telegramReply(Request $request, Lead $lead)
    {
        $this->ensureWritable($request);
        $this->visible($request, $lead);
        $data = $request->validate(['message' => ['required', 'string', 'max:4000']]);
        $this->bot->sendOperatorReply($lead, $request->user(), $data['message']);
        return back()->with('success', 'Javob Telegram bot orqali yuborildi.');
    }
    private function validated(Request $request): array
    {
        return $request->validate(['filial_id' => ['nullable', 'integer', 'exists:filial,id'], 'assigned_to_id' => ['nullable', 'integer', 'exists:users,id'], 'name' => ['required', 'string', 'max:160'], 'phone' => ['nullable', 'string', 'max:40'], 'source' => ['nullable', 'string', 'max:80'], 'campaign' => ['nullable', 'string', 'max:120'], 'interested_service' => ['nullable', 'string', 'max:180'], 'status' => ['required', Rule::in(Lead::STATUSES)], 'estimated_amount' => ['nullable', 'numeric', 'min:0'], 'quoted_amount' => ['nullable', 'numeric', 'min:0'], 'lost_reason' => ['nullable', 'string', 'max:255'], 'next_follow_up_at' => ['nullable', 'date'], 'notes' => ['nullable', 'string', 'max:5000']]);
    }
    private function visible(Request $request, Lead $lead): void { abort_unless(Lead::query()->visibleTo($request->user())->whereKey($lead->id)->exists(), 403); }
    private function ensureWritable(Request $request): void { abort_if($request->user()?->hasRole('super_admin'), 403, 'Super admin leadlarni faqat kuzatadi.'); }
    private function filials(Request $request) { return $request->user()->hasAnyRole(['super_admin', 'admin_manager']) ? FilialModel::query()->orderBy('name')->get(['id', 'name']) : FilialModel::query()->whereKey($request->user()->filial_id)->get(['id', 'name']); }
    private function users(Request $request) { return User::query()->when(! $request->user()->hasAnyRole(['super_admin', 'admin_manager']), fn ($q) => $q->where('filial_id', $request->user()->filial_id))->orderBy('name')->get(['id', 'name', 'filial_id']); }
}
