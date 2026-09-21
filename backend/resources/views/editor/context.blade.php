<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Технический контекст редактора</title>
</head>
<body>
    <h1>Технический контекст редактора</h1>

    <h2>Site</h2>
    <p>id: {{ $site->id }}</p>
    <p>name: {{ $site->name }}</p>

    <h2>Page</h2>
    <p>id: {{ $page->id }}</p>
    <p>name: {{ $page->name }}</p>

    <h2>editor_page_id</h2>
    <p>{{ $page->editor_page_id }}</p>
</body>
</html>
