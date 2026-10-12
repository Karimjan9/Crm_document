# Loyiha konteksti

Bu fayl AI yordamchilari uchun loyiha haqidagi doimiy eslatma.

## Web platforma va bot

- `Crm_document` — CRM web platformasi (PHP 8.2+, Laravel 12).
- Loyihaning Telegram boti `crm_bot` nomli alohida katalogda joylashgan.
- Ikkala katalog bir xil ota katalog ichida: `C:\x_programs\xamp\htdocs\laravel_projects`.
- Web platforma: `C:\x_programs\xamp\htdocs\laravel_projects\Crm_document`.
- Bot: `C:\x_programs\xamp\htdocs\laravel_projects\crm_bot`; web platformadan nisbiy yo'li: `../crm_bot`.
- Bot Python 3.11+, aiogram, FastAPI va Redis bilan ishlaydi.

## Ishlashda hisobga olish

- Web platforma va bot bitta tizimning o'zaro bog'langan qismlari.
- Mijoz, buyurtma va to'lov ma'lumotlari Laravel tomonida saqlanadi; bot CRM bilan `/api/v1/bot/*` API va webhooklar orqali bog'lanadi.
- Botga yoki CRM-bot integratsiyasiga tegishli vazifalarda ikkala loyihaning tegishli kodini va `README.md` fayllarini tekshir.
- Bot kodi ushbu katalog ichida emas; uni yonma-yon turgan `../crm_bot` katalogidan izla.

## Serverga kirish

- Server ulanish sozlamalari `.codex/server.local.json` faylida; bu katalog Git tomonidan e'tiborga olinmaydi.
- Parollar Windows DPAPI orqali joriy Windows foydalanuvchisi uchun shifrlangan. PowerShell orqali `ConvertTo-SecureString` va `System.Net.NetworkCredential` yordamida zarur bo'lganda ochish mumkin.
- Parollarni terminal chiqishiga, kuzatiladigan fayllarga yoki javoblarga chiqarmasdan foydalan.
- SSH: `root@169.58.252.161`, port `22`; serverdagi web loyiha `/var/www/crm_document`, bot `/var/www/crm_bot`.
- Lokal yordamchi orqali SSH buyruq bajarish: `python .codex/server-access.py 'buyruq'`; baza ulanishini tekshirish: `python .codex/server-access.py --check-database`.
- CRM bazasi `client_demo`, foydalanuvchisi `client_demo_user`; baza paroli lokal konfiguratsiyaning `database.password_dpapi` maydonida shifrlangan holda saqlanadi. Ulanish tekshiruvi holati `database.verified` va `application_database` bo'limida.
