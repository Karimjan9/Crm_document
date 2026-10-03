<?php

namespace App\Http\Controllers;

use App\Models\BotContent;
use App\Services\BotUsefulInformation;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class BotUsefulInformationController extends Controller
{
    public function edit(BotUsefulInformation $information)
    {
        return view('bot-useful-information.edit', ['catalog' => $information->editorData(), 'icons' => BotUsefulInformation::ICONS]);
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:80'],
            'intro' => ['nullable', 'string', 'max:4000'],
            'topics' => ['nullable', 'array', 'max:12'],
            'topics.*' => ['required', 'array:id,icon,title,summary,body,checklist_text,tip,published'],
            'topics.*.id' => ['required', 'string', 'max:40', 'regex:/^[a-z0-9_-]+$/', 'distinct'],
            'topics.*.icon' => ['required', Rule::in(array_keys(BotUsefulInformation::ICONS))],
            'topics.*.title' => ['required', 'string', 'max:70'],
            'topics.*.summary' => ['nullable', 'string', 'max:120'],
            'topics.*.body' => ['required', 'string', 'max:1600'],
            'topics.*.checklist_text' => ['nullable', 'string', 'max:1300', function ($attribute, $value, $fail): void {
                $lines = $this->checklist($value);
                if (count($lines) > 6 || collect($lines)->contains(fn ($line) => mb_strlen($line) > 200)) {
                    $fail('Ro‘yxat 6 tagacha banddan iborat bo‘lsin; har bir band 200 belgidan oshmasin.');
                }
            }],
            'topics.*.tip' => ['nullable', 'string', 'max:250'],
            'topics.*.published' => ['required', 'boolean'],
        ]);
        $topics = [];
        foreach ($data['topics'] ?? [] as $topic) {
            $topics[] = [
                'id' => $topic['id'], 'icon' => $topic['icon'], 'title' => trim($topic['title']),
                'summary' => trim($topic['summary'] ?? ''), 'body' => trim($topic['body']),
                'checklist' => $this->checklist($topic['checklist_text'] ?? ''),
                'tip' => trim($topic['tip'] ?? ''), 'published' => (bool) $topic['published'],
            ];
        }
        $item = BotContent::query()->firstOrNew(['key' => 'useful-information']);
        $item->fill([
            'text' => trim($data['intro'] ?? ''), 'updated_by_id' => $request->user()->id,
            'metadata' => [...($item->metadata ?? []), 'title' => trim($data['title']), 'topics' => $topics],
        ])->save();

        return redirect()->route('bot-useful-information.edit')->with('success', 'Foydali ma’lumotlar saqlandi. Botdagi keyingi ochishda yangilanadi.');
    }

    private function checklist(string $value): array
    {
        return array_values(array_filter(array_map('trim', preg_split('/\R/u', $value)), fn ($line) => $line !== ''));
    }
}
