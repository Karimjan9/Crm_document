<?php

namespace App\Http\Controllers;

use App\Models\BotContent;
use Illuminate\Http\Request;

class BotContentController extends Controller
{
    public function index()
    {
        return view('bot-content.index', ['items' => BotContent::query()->orderBy('key')->get()]);
    }

    public function store(Request $request)
    {
        $data = $request->validate(['key' => ['required', 'string', 'max:120', 'regex:/^[a-z0-9._-]+$/'], 'text' => ['required', 'string', 'max:4000']]);
        BotContent::query()->updateOrCreate(['key' => $data['key']], ['text' => $data['text'], 'updated_by_id' => $request->user()->id]);
        return back()->with('success', 'Bot matni saqlandi.');
    }
}
