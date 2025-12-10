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
