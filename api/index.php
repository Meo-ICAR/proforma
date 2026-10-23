<?php

if (! is_dir('/tmp/views')) {
    mkdir('/tmp/views', 0777, true);
}

// Inoltra le richieste di Vercel al punto d'ingresso principale di Laravel
require __DIR__.'/../public/index.php';
