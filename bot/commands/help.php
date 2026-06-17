<?php

global $botToken, $chatId;

$text =
"📌 DAFTAR COMMAND

/start - Mulai bot
/saldo - Cek saldo kas
/help - Bantuan";

kirimPesan($botToken, $chatId, $text);