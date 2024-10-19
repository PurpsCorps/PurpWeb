<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Receipt</title>
</head>
<body>
    <h1>Receipt</h1>
    @foreach($order as $key => $value)
        <p><strong>{{ e($key) }}:</strong>
        @if(is_string($value))
            {{ e($value) }}
        @else
            {{ json_encode($value, JSON_INVALID_UTF8_SUBSTITUTE) }}
        @endif
        </p>
    @endforeach
</body>
</html>