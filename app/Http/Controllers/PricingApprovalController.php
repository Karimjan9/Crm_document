<?php

namespace App\Http\Controllers;

use App\Models\DocumentsModel;
use App\Models\Order;
use App\Models\PricingApproval;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Support\Str;

class PricingApprovalController extends Controller
{
    public function store(Request $request)
    {
        abort_unless($request->user()?->hasAnyRole(['employee', 'admin_filial', 'admin_manager', 'super_admin']), 403);

        $data = $request->validate([
            'document_id' => ['nullable', 'integer', 'exists:documents,id'],
            'order_id' => ['nullable', 'integer', 'exists:orders,id'],
            'discount_percent' => ['required', 'numeric', 'min:0', 'max:100'],
            'discount_amount' => ['required', 'numeric', 'min:0'],
            'reason' => ['required', 'string', 'max:2000'],
        ]);

        $this->authorizeTarget($request, $data['document_id'] ?? null, $data['order_id'] ?? null);

        $approval = PricingApproval::create([
            'document_id' => $data['document_id'] ?? null,
            'order_id' => $data['order_id'] ?? null,
            'requested_by_id' => $request->user()->id,
            'status' => 'pending',
            'discount_percent' => $data['discount_percent'],
            'discount_amount' => $data['discount_amount'],
            'reason' => $data['reason'],
            'metadata' => ['approval_token' => Str::random(48)],
        ]);

        if ($request->expectsJson()) {
            return response()->json([
                'data' => $approval,
                'approval_token' => data_get($approval->metadata, 'approval_token'),
            ], 201);
        }

        return redirect()->back()->with('success', 'Chegirma approval so‘rovi yuborildi. ID: ' . $approval->id);
    }

    public function index(Request $request)
    {
        $this->authorizeAdmin($request);

        $approvals = PricingApproval::query()
            ->with([
                'requestedBy:id,name,login',
                'approvedBy:id,name,login',
                'document:id,document_code',
                'order:id,order_code',
            ])
            ->latest('id')
            ->paginate(25);

        return view('admin.pricing.approvals', compact('approvals'));
    }

    public function approve(Request $request, PricingApproval $pricingApproval)
    {
        $this->authorizeAdmin($request);

        DB::transaction(function () use ($request, $pricingApproval): void {
            $approval = PricingApproval::query()->lockForUpdate()->findOrFail($pricingApproval->id);
            abort_if($approval->status !== 'pending', 409, 'Bu approval allaqachon ko‘rib chiqilgan.');
            abort_if(
                $approval->document_id && !$request->user()->can('update', $approval->document),
                403,
                'Approval targetiga ruxsat yo‘q.'
            );
            abort_if(
                $approval->order_id && !$request->user()->can('update', $approval->order),
                403,
                'Approval orderiga ruxsat yo‘q.'
            );
            $approval->forceFill([
                'status' => 'approved',
                'approved_by_id' => $request->user()->id,
                'approved_at' => now(),
                'rejected_at' => null,
            ])->save();
        });

        return redirect()->back()->with('success', 'Chegirma tasdiqlandi.');
    }

    public function reject(Request $request, PricingApproval $pricingApproval)
    {
        $this->authorizeAdmin($request);

        DB::transaction(function () use ($request, $pricingApproval): void {
            $approval = PricingApproval::query()->lockForUpdate()->findOrFail($pricingApproval->id);
            abort_if($approval->status !== 'pending', 409, 'Bu approval allaqachon ko‘rib chiqilgan.');
            abort_if(
                $approval->document_id && !$request->user()->can('update', $approval->document),
                403,
                'Approval targetiga ruxsat yo‘q.'
            );
            abort_if(
                $approval->order_id && !$request->user()->can('update', $approval->order),
                403,
                'Approval orderiga ruxsat yo‘q.'
            );
            $approval->forceFill([
                'status' => 'rejected',
                'approved_by_id' => $request->user()->id,
                'rejected_at' => now(),
            ])->save();
        });

        return redirect()->back()->with('success', 'Chegirma rad etildi.');
    }

    private function authorizeTarget(Request $request, ?int $documentId, ?int $orderId): void
    {
        $document = null;
        if ($documentId) {
            $document = DocumentsModel::query()->findOrFail($documentId);
            $request->user()->can('update', $document) || abort(403);
        }

        if ($orderId) {
            $order = Order::query()->findOrFail($orderId);
            $request->user()->can('update', $order) || abort(403);

            if ($document && (int) $document->order_id !== (int) $order->id) {
                abort(422, 'Approval document va orderga mos kelmaydi.');
            }
        }
    }

    private function authorizeAdmin(Request $request): void
    {
        abort_unless($request->user()?->hasAnyRole(['admin_manager', 'super_admin']), 403);
    }
}
