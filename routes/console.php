<?php

use App\Console\Commands\AnonymizeOldLeads;
use Illuminate\Support\Facades\Schedule;

/*
|--------------------------------------------------------------------------
| Планирани задачи
|--------------------------------------------------------------------------
|
| Стартират се от системния cron:
| * * * * * cd /път/до/проекта && php artisan schedule:run >> /dev/null 2>&1
|
*/

// GDPR: заличаване на личните данни в стари заявки.
Schedule::command(AnonymizeOldLeads::class)->dailyAt('03:30');

// Изчистване на изтекли записи в кеша на неуспешните задачи.
Schedule::command('queue:prune-failed --hours=336')->weekly();
