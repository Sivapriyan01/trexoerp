<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield("title", "Invoice")</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style>
        /* Essential print styles */
        @media print {
            body { background: white; margin: 0; padding: 0; }
            .no-print { display: none !important; }
        }
    </style>
</head>
<body class="bg-slate-100 py-10">
    @yield("content")
</body>
</html>