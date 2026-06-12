<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
</head>
<body style="font-family: Arial, sans-serif; font-size: 14px; padding: 20px; background: #f5f5f5;">
    <div style="max-width: 600px; margin: 0 auto; background: white; border-radius: 8px; padding: 24px; border: 1px solid #e0e0e0;">
        <h2 style="color: #333; margin-bottom: 16px;">{{ $title }}</h2>
        <p style="color: #555; line-height: 1.6;">{{ $message }}</p>
        @if($actionUrl)
            <p style="margin-top: 20px;">
                <a href="{{ $actionUrl }}" style="display: inline-block; padding: 10px 20px; background: #4f46e5; color: white; text-decoration: none; border-radius: 6px; font-weight: bold;">Voir les détails</a>
            </p>
        @endif
        <hr style="border: none; border-top: 1px solid #eee; margin: 20px 0;">
        <p style="color: #999; font-size: 12px;">Ce message a été envoyé automatiquement par GestStockDigit.</p>
    </div>
</body>
</html>
