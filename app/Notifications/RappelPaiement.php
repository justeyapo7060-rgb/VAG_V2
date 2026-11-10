<?php

namespace App\Notifications;

use App\Models\Apprenant;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class RappelPaiement extends Notification
{
    public function __construct(
        public int $total,
        public int $paye,
        public int $reste,
        public ?\Carbon\Carbon $dateLimite,
    ) {
    }

    public static function pour(Apprenant $apprenant): self
    {
        return new self(
            $apprenant->montant_total,
            $apprenant->total_paye,
            $apprenant->reste_a_payer,
            $apprenant->date_limite,
        );
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $fmt = fn (int $n) => number_format($n, 0, ',', ' ') . ' FCFA';
        $retard = $this->dateLimite && $this->dateLimite->copy()->endOfDay()->isPast();

        $mail = (new MailMessage)
            ->subject($retard ? 'Paiement en retard : solde de votre formation' : 'Rappel : solde de votre formation')
            ->greeting("Bonjour {$notifiable->prenoms},")
            ->line("Total de vos formations : {$fmt($this->total)}")
            ->line("Déjà réglé : {$fmt($this->paye)}")
            ->line("Reste à payer : {$fmt($this->reste)}");

        if ($this->dateLimite) {
            $mail->line($retard
                ? "La date limite de solde ({$this->dateLimite->format('d/m/Y')}) est dépassée. Merci de régulariser au plus vite."
                : "Le solde doit être réglé au plus tard le {$this->dateLimite->format('d/m/Y')} (fin de votre cohorte).");
        } else {
            $mail->line('Le solde doit être réglé avant la fin de votre cohorte.');
        }

        return $mail->line('Merci de vous rapprocher de l’administration pour effectuer votre prochain versement.');
    }
}
