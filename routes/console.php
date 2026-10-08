<?php

use Illuminate\Support\Facades\Schedule;

/*
| Tarefas agendadas (executadas pelo container "scheduler": php artisan schedule:work).
*/

// Remove tokens de API (Sanctum) expirados há mais de 24h.
Schedule::command('sanctum:prune-expired --hours=24')->daily();

// Limpa jobs com falha antigos da fila.
Schedule::command('queue:prune-failed --hours=168')->daily();
