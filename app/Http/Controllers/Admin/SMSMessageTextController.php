<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Models\SMSMessageTextModel;
use App\Http\Controllers\Controller;

class SMSMessageTextController extends Controller
{
    public function index()
    {
        $smsMessages = SMSMessageTextModel::paginate();
        return view('admin.sms_message_text.index', compact('smsMessages'));
    }

    public function report()
    {
        $typeLabels = [
            'xabarnoma' => 'Xabarnoma',
            'ogohlantirish' => 'Ogohlantirish',
            'boshqa' => 'Boshqa',
        ];

        $typeStats = SMSMessageTextModel::query()
            ->selectRaw('type, COUNT(*) as total')
            ->groupBy('type')
            ->pluck('total', 'type');

        $stats = [
            'total' => SMSMessageTextModel::count(),
            'filled' => SMSMessageTextModel::query()
                ->where(function ($query) {
                    $query->whereNotNull('message_text1')
                        ->orWhereNotNull('message_text2')
                        ->orWhereNotNull('message_text3');
                })
                ->count(),
            'empty' => SMSMessageTextModel::query()
                ->whereNull('message_text1')
                ->whereNull('message_text2')
                ->whereNull('message_text3')
                ->count(),
        ];

        $recentMessages = SMSMessageTextModel::query()
            ->latest('updated_at')
            ->take(8)
            ->get();

        return view('admin.sms_message_text.report', compact('stats', 'typeLabels', 'typeStats', 'recentMessages'));
    }

    public function create()
    {
        return view('admin.sms_message_text.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|unique:s_m_s_message_text,name',
            'type' => 'required|in:xabarnoma,ogohlantirish,boshqa',
            'message_text1' => 'nullable|string',
            'message_text2' => 'nullable|string',
            'message_text3' => 'nullable|string',
            'description' => 'nullable|string',
        ]);

        SMSMessageTextModel::create([
            'name' => $request->name,
            'type' => $request->type,
            'message_text1' => $request->message_text1,
            'message_text2' => $request->message_text2,
            'message_text3' => $request->message_text3,
            'description' => $request->description,
        ]);

        return redirect()->route('superadmin.sms_message_text.index')->with('success', 'SMS xabari matni muvaffaqiyatli yaratildi.');
    }

    public function edit($id)
    {
        $smsMessage = SMSMessageTextModel::findOrFail($id);
        return view('admin.sms_message_text.edit', compact('smsMessage'));
    }

    public function update(Request $request, $id)
    {
        $smsMessage = SMSMessageTextModel::findOrFail($id);

        $request->validate([
            'name' => 'required|unique:s_m_s_message_text,name,' . $smsMessage->id,
            'type' => 'required|in:xabarnoma,ogohlantirish,boshqa',
            'message_text1' => 'nullable|string',
            'message_text2' => 'nullable|string',
            'message_text3' => 'nullable|string',
            'description' => 'nullable|string',
        ]);

        $smsMessage->update([
            'name' => $request->name,
            'type' => $request->type,
            'message_text1' => $request->message_text1,
            'message_text2' => $request->message_text2,
            'message_text3' => $request->message_text3,
            'description' => $request->description,
        ]);

        return redirect()->route('superadmin.sms_message_text.index')->with('success', 'SMS xabari matni muvaffaqiyatli yangilandi.');
    }

    public function destroy($id)
    {
        $smsMessage = SMSMessageTextModel::findOrFail($id);
        $smsMessage->delete();

        return redirect()->route('superadmin.sms_message_text.index')->with('success', 'SMS xabari matni muvaffaqiyatli o‘chirildi.');
    }


}
