<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\FilialModel;
use App\Models\Partner;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Spatie\Permission\Models\Role;

class PartnerController extends Controller
{
    public function index(Request $request)
    {
        $this->authorizeAccess($request);

        $partners = Partner::query()
            ->withCount(['orders', 'users'])
            ->with('filials:id,name')
            ->when($request->filled('q'), function ($query) use ($request): void {
                $search = trim((string) $request->input('q'));
                $query->where(function ($builder) use ($search): void {
                    $builder->where('company_name', 'like', "%{$search}%")
                        ->orWhere('code', 'like', "%{$search}%");
                });
            })
            ->latest('id')
            ->paginate(20)
            ->withQueryString();

        return view('admin.partners.index', compact('partners'));
    }

    public function create(Request $request)
    {
        $this->authorizeAccess($request);

        return view('admin.partners.form', [
            'partner' => new Partner(['status' => 'active', 'currency' => 'UZS', 'payment_terms_days' => 30]),
            'filials' => FilialModel::query()->orderBy('name')->get(['id', 'name']),
            'types' => Partner::TYPES,
            'isEdit' => false,
        ]);
    }

    public function store(Request $request)
    {
        $this->authorizeAccess($request);
        $data = $request->validate($this->rules());

        DB::transaction(function () use ($data): void {
            $partner = Partner::create($this->partnerData($data));
            $this->syncFilials($partner, $data['filial_ids'] ?? []);

            $user = User::create([
                'name' => $data['admin_name'],
                'login' => $data['admin_login'],
                'phone' => $this->normalizePhone($data['admin_phone']),
                'password' => Hash::make($data['admin_password']),
                'partner_id' => $partner->id,
                'filial_id' => null,
            ]);
            Role::findOrCreate('partner_admin', 'web');
            $user->assignRole('partner_admin');
        });

        return redirect()->route('admin.partners.index')->with('success', 'B2B partner va kabinet administratori yaratildi.');
    }

    public function edit(Request $request, Partner $partner)
    {
        $this->authorizeAccess($request);

        return view('admin.partners.form', [
            'partner' => $partner->load('filials:id,name'),
            'filials' => FilialModel::query()->orderBy('name')->get(['id', 'name']),
            'types' => Partner::TYPES,
            'isEdit' => true,
        ]);
    }

    public function update(Request $request, Partner $partner)
    {
        $this->authorizeAccess($request);
        $data = $request->validate($this->rules($partner));
        $partner->update($this->partnerData($data));
        $this->syncFilials($partner, $data['filial_ids'] ?? []);

        return redirect()->route('admin.partners.index')->with('success', 'Partner ma\'lumotlari yangilandi.');
    }

    private function rules(?Partner $partner = null): array
    {
        return [
            'company_name' => ['required', 'string', 'max:180'],
            'code' => ['required', 'string', 'max:40', 'alpha_dash', Rule::unique('partners', 'code')->ignore($partner?->id)],
            'type' => ['required', Rule::in(Partner::TYPES)],
            'contact_name' => ['nullable', 'string', 'max:120'],
            'email' => ['nullable', 'email', 'max:180'],
            'phone' => ['nullable', 'string', 'max:40'],
            'tax_id' => ['nullable', 'string', 'max:40'],
            'billing_email' => ['nullable', 'email', 'max:180'],
            'discount_percent' => ['required', 'numeric', 'min:0', 'max:100'],
            'credit_limit' => ['required', 'numeric', 'min:0'],
            'payment_terms_days' => ['required', 'integer', 'min:0', 'max:365'],
            'currency' => ['required', 'string', 'size:3'],
            'status' => ['required', Rule::in(Partner::STATUSES)],
            'brand_name' => ['nullable', 'string', 'max:180'],
            'brand_logo_url' => ['nullable', 'url', 'max:500'],
            'brand_primary_color' => ['required', 'regex:/^#[0-9a-fA-F]{6}$/'],
            'brand_secondary_color' => ['required', 'regex:/^#[0-9a-fA-F]{6}$/'],
            'tracking_title' => ['nullable', 'string', 'max:180'],
            'notes' => ['nullable', 'string', 'max:5000'],
            'filial_ids' => ['required', 'array', 'min:1'],
            'filial_ids.*' => ['integer', 'exists:filial,id'],
            'admin_name' => [$partner ? 'nullable' : 'required', 'string', 'max:120'],
            'admin_login' => [$partner ? 'nullable' : 'required', 'string', 'max:80', 'unique:users,login'],
            'admin_phone' => [$partner ? 'nullable' : 'required', 'string', 'max:40'],
            'admin_password' => [$partner ? 'nullable' : 'required', 'confirmed', Password::min(12)],
        ];
    }

    private function partnerData(array $data): array
    {
        return [
            'company_name' => $data['company_name'],
            'code' => strtoupper($data['code']),
            'type' => $data['type'],
            'contact_name' => $data['contact_name'] ?? null,
            'email' => $data['email'] ?? null,
            'phone' => $data['phone'] ?? null,
            'tax_id' => $data['tax_id'] ?? null,
            'billing_email' => $data['billing_email'] ?? null,
            'discount_percent' => $data['discount_percent'],
            'credit_limit' => $data['credit_limit'],
            'payment_terms_days' => $data['payment_terms_days'],
            'currency' => strtoupper($data['currency']),
            'status' => $data['status'],
            'brand_name' => $data['brand_name'] ?? null,
            'brand_logo_url' => $data['brand_logo_url'] ?? null,
            'brand_primary_color' => $data['brand_primary_color'],
            'brand_secondary_color' => $data['brand_secondary_color'],
            'tracking_title' => $data['tracking_title'] ?? null,
            'notes' => $data['notes'] ?? null,
        ];
    }

    private function syncFilials(Partner $partner, array $filialIds): void
    {
        $partner->filials()->sync(collect($filialIds)->mapWithKeys(fn ($id) => [(int) $id => ['is_active' => true]])->all());
    }

    private function normalizePhone(string $phone): string
    {
        return substr(preg_replace('/\D/', '', $phone), -15);
    }

    private function authorizeAccess(Request $request): void
    {
        abort_unless($request->user()?->hasAnyRole(['super_admin', 'admin_manager']), 403);
    }
}
