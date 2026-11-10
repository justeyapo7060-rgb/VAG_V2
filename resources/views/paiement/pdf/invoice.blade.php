<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <style>
        body 
        {   
            font-family: DejaVu Sans,
             sans-serif; font-size: 12px; 
             color: #1e293b; 
        }
        h1 {
             font-size: 18px;
              margin: 0 0 4px; 
            }
        table { 
            width: 100%; 
            border-collapse: collapse; 
            margin-top: 12px; 
        }
        td, th { 
            padding: 6px; 
            border-bottom: 1px solid #e2e8f0; 
            text-align: left;
         }
        .total { 
            font-weight: bold;
         }
    </style>
</head>
<body>
    <h1>Reçu de paiement n° {{ $paiement->reference }}</h1>
    <p>{{ $apprenant->nom }} {{ $apprenant->prenoms }} — {{ $apprenant->matricule }}</p>

    <table>
        <tr>
        <th>Versement</th>
            <td>n°{{ $paiement->numero }}</td>
        </tr>
        <tr>
            <th>Date</th><td>{{ $paiement->date_paiement->format('d/m/Y H:i') }}</td>
        </tr>
        <tr>
            <th>Mode</th><td>{{ $paiement->mode_paiement->label() }}</td>
        </tr>
        <tr class="total">
            <th>Montant payé</th>
            <td>{{ number_format($paiement->montant, 0, ',', ' ') }} FCFA</td>
        </tr>

         <tr class="total">
            <th>Montant dejà payé</th>
            <td>{{ number_format($resultat, 0, ',', ' ') }} FCFA</td>
        </tr>
        <tr>
            <th>Total des formations</th>
            <td>{{ number_format($apprenant->montant_total, 0, ',', ' ') }} FCFA</td>
        </tr>
        <tr><th>Reste à payer</th><td>{{ number_format($apprenant->reste_a_payer, 0, ',', ' ') }} FCFA</td></tr>
    </table>
</body>
</html>