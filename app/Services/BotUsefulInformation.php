<?php

namespace App\Services;

use App\Models\BotContent;
use Illuminate\Support\Str;

class BotUsefulInformation
{
    public const ICONS = [
        'document' => '📄', 'photo' => '📷', 'checklist' => '✅', 'clock' => '🕒',
        'question' => '❓', 'order' => '📦', 'info' => 'ℹ️', 'support' => '💬',
    ];

    public function editorData(): array
    {
        $item = BotContent::query()->where('key', 'useful-information')->first();
        $metadata = $item?->metadata ?? [];

        return [
            'title' => $metadata['title'] ?? 'Foydali ma’lumotlar',
            'intro' => $item?->text ?? 'Hujjat yuborishdan oldin kerakli ma’lumotni tanlang. Savolingiz bo‘lsa, mutaxassisga murojaat qiling.',
            'topics' => $metadata['topics'] ?? $this->defaults(),
            'legacy_text' => $item && ! array_key_exists('topics', $metadata) ? $item->text : null,
        ];
    }

    public function content(): array
    {
        $catalog = $this->editorData();
        $topics = [];
        foreach ($catalog['topics'] as $topic) {
            if (! ($topic['published'] ?? false)) {
                continue;
            }
            $topics[] = array_intersect_key($topic, array_flip(['id', 'icon', 'title', 'summary', 'body', 'checklist', 'tip']));
        }
        $plain = [$catalog['title'], $catalog['intro']];
        foreach ($topics as $topic) {
            $plain[] = (self::ICONS[$topic['icon']] ?? 'ℹ️').' '.$topic['title']."\n".$topic['body']
                .(empty($topic['checklist']) ? '' : "\n• ".implode("\n• ", $topic['checklist']))
                .(empty($topic['tip']) ? '' : "\n💡 ".$topic['tip']);
        }

        return [
            'title' => $catalog['title'], 'intro' => $catalog['intro'], 'topics' => $topics,
            // Keep the original field compatible with the older plain-text bot.
            'text' => $catalog['legacy_text'] ?? Str::limit(implode("\n\n", array_filter($plain)), 1900),
        ];
    }

    private function defaults(): array
    {
        return [
            [
                'id' => 'clear-photo', 'icon' => 'photo', 'title' => 'Hujjatni sifatli yuborish',
                'summary' => 'O‘qilishi aniq rasm yoki PDF yuboring.',
                'body' => 'Hujjatdagi matn ravshan ko‘rinsa, mutaxassis murojaatingizni tushunishi osonlashadi.',
                'checklist' => ['Hujjatning barcha burchaklari ko‘rinsin.', 'Rasmda soya va yorug‘lik aksi bo‘lmasin.', 'Bir nechta sahifa bo‘lsa, ketma-ket yuboring.'],
                'tip' => 'Bot JPG, PNG va PDF qabul qiladi. Har bir fayl 20 MB gacha bo‘lishi mumkin.', 'published' => true,
            ],
            [
                'id' => 'send-request', 'icon' => 'checklist', 'title' => 'Murojaatni qanday yuboraman?',
                'summary' => 'Xizmatni yozing, hujjatni qo‘shing va jo‘nating.',
                'body' => '“📝 Yangi murojaat” tugmasini bosing. Kerakli xizmatni matn bilan yozishingiz yoki hujjat yuborishingiz mumkin.',
                'checklist' => ['Kerakli xizmat yoki tarjima tilini yozing.', 'Qo‘shimcha izoh va hujjatlarni biriktiring.', 'Kontaktni yuboring yoki saqlangan kontakt bilan murojaatni jo‘nating.'],
                'tip' => 'Murojaatga 10 tagacha fayl qo‘shish mumkin.', 'published' => true,
            ],
            [
                'id' => 'quote-time', 'icon' => 'clock', 'title' => 'Narx va muddatni aniqlashtirish',
                'summary' => 'Hujjat va kerakli xizmat haqida ma’lumot bering.',
                'body' => 'Murojaatingizda xizmatni va sizga kerakli muddatni yozing. Mutaxassis hujjatingizni ko‘rib, narx va bajarish muddatini aniqlashtiradi.',
                'checklist' => ['Hujjatning barcha sahifalarini yuboring.', 'Qaysi til yoki xizmat kerakligini yozing.', 'Qachongacha kerakligini ko‘rsating.'],
                'tip' => 'Savolingiz bo‘lsa, bosh menyudagi “👩‍💼 Operator” tugmasidan foydalaning.', 'published' => true,
            ],
        ];
    }
}
