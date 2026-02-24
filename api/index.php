<?php

// Forward Vercel requests to Laravel's normal starting point
// We use realpath to ensure the serverless function finds the right folder
require __DIR__ . '/../public/index.php';