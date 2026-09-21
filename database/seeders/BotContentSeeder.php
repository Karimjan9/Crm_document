<?php

namespace Database\Seeders;

use App\Models\BotContent;
use Illuminate\Database\Seeder;

class BotContentSeeder extends Seeder
{
    public function run(): void
    {
        foreach ([
            'branches' => 'Filiallar manzili va ish vaqtini operatorimiz sizga aniqlashtirib beradi.',
            'useful-information' => 'Hujjatingiz rasmini yuboring — mutaxassisimiz sizga kerakli tartibni aytadi.',
            'notification.payment_received' => 'To‘lovingiz qabul qilindi. Qoldiq: :balance :currency.',
            'notification.status.ready_for_delivery' => 'Assalomu alaykum, :code buyurtmangiz tayyor bo‘ldi. Olish usuli bo‘yicha savolingiz bo‘lsa shu yerga yozing.',
            'notification.status.courier_sent' => ':code buyurtmangiz kuryerga topshirildi.',
            'notification.status.delivered' => ':code buyurtmangiz topshirildi. Xizmatimiz bo‘yicha fikringizni kutamiz.',
            'lead.follow_up' => 'Salom. Kecha hujjatingiz bo‘yicha narx yuborgandik. Savolingiz qolgan bo‘lsa, shu yerga yozing, yordam beramiz.',
        ] as $key => $text) BotContent::query()->firstOrCreate(['key' => $key], ['text' => $text]);
    }
}
