<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ $proforma->emailsubject }}</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background-color: #f8f9fa; padding: 20px; text-align: center; border-bottom: 1px solid #e9ecef; }
        .content { padding: 20px 0; }
        .footer { margin-top: 20px; padding-top: 20px; border-top: 1px solid #e9ecef; font-size: 0.9em; color: #6c757d; }
        table { width: 100%; border-collapse: collapse; margin: 20px 0; }
        th, td { padding: 12px; text-align: left; border-bottom: 1px solid #dee2e6; }
        th { background-color: #f8f9fa; }
        .total { font-weight: bold; font-size: 1.1em; }
        .note { background-color: #f8f9fa; padding: 15px; border-radius: 4px; margin-top: 20px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h2>Proforma #{{ $proforma->id }}</h2>
            <p>{{ $proforma->fornitore->name }}</p>
        </div>

        <div class="content">
            @if($preview)
            <div style="background-color: #fff3cd; color: #856404; padding: 10px; margin-bottom: 20px; border-radius: 4px;">
                <strong>ANTEPRIMA</strong> - Questa email verrà inviata a: {{ $proforma->fornitore->email }}
            </div>
            @endif

            <p>Gentile {{ $proforma->fornitore->name }},</p>
            <p>Di seguito il proforma #{{ $proforma->id }} con i seguenti dettagli dei compensi riconosciuti:</p>

            <table>
                @if($proforma->compenso > 0)
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Pratica</th>
                        <th>Cliente</th>
                        <th>Importo</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($proforma->provvigioni as $index => $provvigione)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>#{{ $provvigione->id_pratica }} ({{ $provvigione->id }})</td>
                        <td>
                            {{ optional($provvigione->pratica)->cognome_cliente ?? 'N/A' }}
                            {{ optional($provvigione->pratica)->nome_cliente ?? '' }}
                        </td>
                        <td>€ {{ number_format($provvigione->importo, 2, ',', '.') }}</td>
                    </tr>
                    @endforeach
                </tbody>
                @endif
                <tfoot>
                    @if($proforma->anticipo > 0)
                    <tr>
                        <td colspan="3" style="text-align: right;">{{ $proforma->anticipo_descrizione }}:</td>
                        <td>€ {{ number_format($proforma->anticipo, 2, ',', '.') }}</td>
                    </tr>
                    @endif
                    @if($proforma->contributo > 0)
                    <tr>
                        <td colspan="3" style="text-align: right;">{{ $proforma->contributo_descrizione }}:</td>
                        <td>€ {{ number_format($proforma->contributo, 2, ',', '.') }}</td>
                    </tr>
                    @endif
                    <tr class="total">
                        <td colspan="3" style="text-align: right;">TOTALE LORDO:</td>
                        <td>€ {{ number_format($somma, 2, ',', '.') }}</td>
                    </tr>
                </tfoot>
            </table>

            @if(!empty($proforma->annotation))
            <div class="note">
                <strong>Note:</strong><br>
                {!! nl2br(e($proforma->annotation)) !!}
            </div>
            @endif

            <p>Cordiali saluti,<br>
            {{ config('app.name') }}</p>
        </div>

        <div class="footer">
            <p>Questa email è stata generata automaticamente, si prega di non rispondere.</p>
            <p>Se hai ricevuto questa email per errore, ti preghiamo di contattarci.</p>
        </div>
    </div>
</body>
</html>
