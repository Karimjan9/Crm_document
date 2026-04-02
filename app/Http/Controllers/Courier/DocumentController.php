<?php

namespace App\Http\Controllers\Courier;

use App\Http\Controllers\Controller;
use App\Models\DocumentCourier;
use Illuminate\Http\Request;

class DocumentController extends Controller
{
    public function index()
    {
        $courierId = auth()->id();

        $assignments = DocumentCourier::with([
                'document.client',
                'document.service',
                'document.user',
                'document.files',
                'sentBy',
            ])
            ->where('courier_id', $courierId)
            ->whereIn('status', ['sent', 'accepted'])
            ->orderBy('updated_at', 'desc')
            ->get();

        return view('courier.document.index', compact('assignments'));
    }

    public function history()
    {
        $courierId = auth()->id();

        $assignments = DocumentCourier::with([
                'document.client',
                'document.service',
                'document.user',
                'document.files',
                'sentBy',
            ])
            ->where('courier_id', $courierId)
            ->whereIn('status', ['returned', 'rejected'])
            ->orderBy('updated_at', 'desc')
            ->get();

        return view('courier.document.history', compact('assignments'));
    }

    public function accept(Request $request, DocumentCourier $documentCourier)
    {
        $this->authorizeCourier($documentCourier);

        if ($documentCourier->status !== 'sent') {
            return redirect()->back()->with('error', 'Bu hujjatni qabul qilib bo‘lmaydi.');
        }

        $request->validate([
            'comment' => 'nullable|string|max:1000',
        ]);

        $documentCourier->status = 'accepted';
        $documentCourier->courier_comment = $request->comment;
        $documentCourier->accepted_at = now();
        $documentCourier->save();

        return redirect()->back()->with('success', 'Hujjat qabul qilindi.');
    }

    public function reject(Request $request, DocumentCourier $documentCourier)
    {
        $this->authorizeCourier($documentCourier);

        if ($documentCourier->status !== 'sent') {
            return redirect()->back()->with('error', 'Bu hujjatni rad etib bo‘lmaydi.');
        }

        $request->validate([
            'comment' => 'required|string|max:1000',
        ]);

        $documentCourier->status = 'rejected';
        $documentCourier->courier_comment = $request->comment;
        $documentCourier->rejected_at = now();
        $documentCourier->save();

        return redirect()->back()->with('success', 'Hujjat rad etildi.');
    }

    public function returnDocument(Request $request, DocumentCourier $documentCourier)
    {
        $this->authorizeCourier($documentCourier);

        if ($documentCourier->status !== 'accepted') {
            return redirect()->back()->with('error', 'Bu hujjatni qaytarib bo‘lmaydi.');
        }

        $request->validate([
            'comment' => 'nullable|string|max:1000',
        ]);

        $documentCourier->status = 'returned';
        $documentCourier->return_comment = $request->comment;
        $documentCourier->returned_at = now();
        $documentCourier->save();

        return redirect()->back()->with('success', 'Hujjat qaytarildi.');
    }

    private function authorizeCourier(DocumentCourier $documentCourier): void
    {
        if ($documentCourier->courier_id !== auth()->id()) {
            abort(403);
        }
    }
}
