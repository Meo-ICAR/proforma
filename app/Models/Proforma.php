<?php

namespace App\Models;

use App\Mail\ProformaMail;
use App\Models\Company;
use App\Models\Fornitore;
use App\Models\Provvigione;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Mail;

class Proforma extends Model
{
    use SoftDeletes;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'proformas';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'stato',
        'fornitori_id',
        'anticipo',
        'anticipo_descrizione',
        'compenso',
        'compenso_descrizione',
        'contributo',
        'contributo_descrizione',
        'annotation',
        'emailsubject',
        'emailto',
        'emailbody',
        'emailfrom',
        'sended_at',
        'paid_at',
        'delta',
        'anticipo_residuo',
        'delta_annotation',
        'proforma_id',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'anticipo' => 'decimal:2',
        'anticipo_residuo' => 'decimal:2',
        'compenso' => 'decimal:2',
        'contributo' => 'decimal:2',
        'delta' => 'decimal:2',
        'sended_at' => 'datetime',
        'paid_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    /**
     * Get the fornitore that owns the proforma.
     */
    public function fornitore()
    {
        return $this->belongsTo(Fornitore::class, 'fornitori_id');
    }

    protected $with = ['fornitore'];

    /**
     * The model's default values for attributes.
     *
     * @var array
     */
    protected $attributes = [
        'stato' => 'Inserito',
    ];

    /**
     * Get the provvigioni for the proforma.
     */
    public function provvigioni()
    {
        return $this->hasMany(Provvigione::class, 'proforma_id');
    }

    protected static function booted()
    {
        static::deleting(function ($proforma) {
            // Update all related provvigioni
            $proforma->provvigioni()->update([
                'stato' => 'Inserito',
                'proforma_id' => null
            ]);
        });
    }

    /**
     * Scope a query to only include sent proformas.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeSent($query)
    {
        return $query->whereNotNull('sended_at');
    }

    /**
     * Scope a query to only include paid proformas.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopePaid($query)
    {
        return $query->whereNotNull('paid_at');
    }

    /**
     * Scope a query to only include proformas with a specific status.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  string  $status
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeWithStatus($query, $status)
    {
        return $query->where('stato', $status);
    }

    /**
     * Create a new proforma from a fornitore
     *
     * @param string $fornitori_id
     * @return \App\Models\Proforma
     */
    public static function createFromFornitore(string $fornitori_id): Proforma
    {
        $fornitore = Fornitore::with('company')->findOrFail($fornitori_id);

        $proformaData = [
            'fornitori_id' => $fornitori_id,
            'anticipo' => $fornitore->anticipo,
            'anticipo_descrizione' => $fornitore->anticipo_description,
            'anticipo_residuo' => $fornitore->anticipo_residuo,
            'compenso_descrizione' => $fornitore->company->compenso_descrizione ?? 'Compenso',
            'contributo' => $fornitore->contributo,
            'contributo_descrizione' => $fornitore->contributo_description,
            'emailsubject' => 'Proforma - ' . $fornitore->name,
            'emailto' => $fornitore->email,
            'stato' => 'Inserito',
            'compenso' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ];

        if ($fornitore->company) {
            $proformaData['company_id'] = $fornitore->company->id;
            // Use company's email subject if available
            if (!empty($fornitore->company->emailsubject)) {
                $proformaData['emailfrom'] = $fornitore->company->emailfrom;
            }
        }

        return self::create($proformaData);
    }

    /**
     * Find or create a proforma for a fornitore by P.IVA and return its ID
     *
     * @param string $piva The VAT number to search for
     * @return string The ID of the found or created proforma
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException If no fornitore is found with the given P.IVA
     */
    public static function findOrCreateByPiva(string $piva, float $importo): string
    {
        try {
            // Clean up the P.IVA
            $cleanedPiva = str_replace(' ', '', $piva);

            \Log::info('Searching for fornitore with P.IVA: ' . $cleanedPiva);

            // Find fornitore by P.IVA (case insensitive and ignoring spaces)
            $fornitore = Fornitore::whereRaw("REPLACE(piva, ' ', '') = ?", [$cleanedPiva])
                ->firstOrFail();

            // Check if there's already a proforma in 'Inserito' status for this fornitore
            $existingProforma = self::where('fornitori_id', $fornitore->id)
                ->where('stato', 'Inserito')
                ->first();

            if (!$existingProforma) {
                \Log::info('Creating new proforma for fornitore ID: ' . $fornitore->id);
                $existingProforma = self::createFromFornitore($fornitore->id);
            }

            // Update the compenso
            $compenso = $existingProforma->compenso;
            $existingProforma->update([
                'compenso' => $importo + $compenso,
            ]);

            \Log::info('Returning proforma ID: ' . $existingProforma->id);
            return $existingProforma->id;
        } catch (\Exception $e) {
            \Log::error('Error in findOrCreateByPiva: ' . $e->getMessage());
            \Log::error('Stack trace: ' . $e->getTraceAsString());
            throw $e;  // Re-throw to maintain the same behavior
        }
    }

    public function getProformanomeAttribute()
    {
        return $this->emailsubject . ' #' . $this->id;
    }

    /**
     * Send the proforma via email
     *
     * @return bool
     * @throws \Exception
     */
    public function inviaEmail($preview = false)
    {
        try {
            $proforma = $this->load(['provvigioni.pratica']);
            $ccEmail = null;
            $bccEmail = null;
            $cr = "\n";
            $somma = 0;
            $debug = $preview;
            $debug = true;
            $message = '';

            if (!$preview) {
                // Get the recipient email from the related fornitore
                $toEmail = $this->emailto;

                if (empty($toEmail)) {
                    throw new \Exception('Nessun indirizzo email specificato per il fornitore');
                }
                // Get the first company record for CC
                $company = Company::first();
                $ccEmail = $company ? $company->email_cc : null;
                $bccEmail = $company ? $company->email_bcc : null;
            }

            if ($preview) {
                // Get the logged-in user's email
                $loggedInUserEmail = auth()->user()?->email;
                $toEmail = $loggedInUserEmail ?? 'hassistosrl@gmail.com';
                $message .= 'Simulazione email da inviarsi a email: ' . $this->emailto . $cr;
            }

            // Prepare email details
            $subject = "Proforma #{$this->id} - {$this->fornitore->name}";
            $message .= "Proforma #{$this->id}";

            if ($this->compenso <> 0) {
                $n = 0;
                $message .= $cr . $this->compenso_descrizione . ': €' . number_format($this->compenso, 2);
                //   $somma += $this->compenso;

                foreach ($this->provvigioni as $provvigione) {
                    $n++;
                    $message .= "\n- " . $n
                        . '.  ' . $provvigione->id_pratica
                        . ' - ' . $provvigione->id
                        . ' - ' . (optional($provvigione->pratica)->cognome_cliente ?? 'N/A')
                        . ' - ' . (optional($provvigione->pratica)->nome_cliente ?? 'N/A')
                        . ': €' . number_format($provvigione->importo, 2);
                    $somma += $provvigione->importo;
                }
            }
            if ($this->anticipo <> 0) {
                if ($this->anticipo > 0) {
                    $message .= $cr . $this->anticipo_descrizione . ': €' . number_format($this->anticipo, 2);
                    $somma += $this->anticipo;
                } else {
                    $message .= $cr . $this->anticipo_descrizione . ': €' . -number_format($this->anticipo, 2);
                    $somma += -$this->anticipo;
                }
            }

            if ($this->contributo <> 0) {
                $message .= $cr . $this->contributo_descrizione . ': €' . number_format($this->contributo, 2);
                $somma += $this->contributo;
            }

            $message .= $cr . 'TOTALE LORDO € ' . number_format($somma, 2);

            if (!empty($this->annotation)) {
                $message .= $cr . 'Note: ' . $this->annotation;
            }
            if ($this->anticipo < 0) {
                $subject = "Anticipo #{$this->id} - {$this->fornitore->name} - Totale: € " . number_format($somma, 2);
            } else {
                $subject = "Proforma #{$this->id} - {$this->fornitore->name} - Totale: € " . number_format($somma, 2);
            }

            // Send the email
            $mail = Mail::to($toEmail);

            if (!$preview) {
                if ($ccEmail) {
                    $mail->cc($ccEmail);
                }
                if ($bccEmail) {
                    $mail->bcc($bccEmail);
                }
            }

            // Update the proforma status
            $this->update([
                'emailsubject' => $subject,
                'emailto' => $toEmail,
                'emailbody' => $message,
                //  'emailfrom' => $ccEmail,
            ]);
            // Replace this line:
            // $mail->send(new ProformaMail($this, $subject, $message));
            // With this:
            // Replace the existing send() call with this:

            /*
             * if (!$preview) {
             *     if ($ccEmail) {
             *         $message->cc($ccEmail);
             *     }
             *     if ($bccEmail) {
             *         $message->bcc($bccEmail);
             *     }
             * }
             * Mail::raw($message, function ($message) use ($toEmail, $subject, $ccEmail, $bccEmail) {
             *     $message
             *         ->to($toEmail)
             *         ->subject($subject);
             * });
             */
            // In Proforma.php, replace the Mail::raw() call with:
            Mail::send('emails.proforma', [
                'proforma' => $this,
                'content' => $message,
                'somma' => $somma,
                'preview' => $preview
            ], function ($message) use ($toEmail, $subject, $ccEmail, $bccEmail, $preview) {
                $message
                    ->to($toEmail)
                    ->subject($subject);

                if (!$preview) {
                    if ($ccEmail) {
                        $message->cc($ccEmail);
                    }
                    if ($bccEmail) {
                        $message->bcc($bccEmail);
                    }
                }
            });

            if (!$preview) {
                \Log::info('Updating proforma status after email send for ID: ' . $this->id);
                $this->update([
                    'sended_at' => now(),
                    'stato' => 'Inviato',
                    'data_invio' => now(),
                ]);
                // Update fornitore's anticipo_residuo
                if ($this->fornitore) {
                    $this->fornitore->increment('anticipo_residuo', -$this->anticipo);
                    \Log::info('Updated anticipo_residuo for fornitore ID: ' . $this->fornitore->id
                        . ' by ' . $this->anticipo
                        . '. New value: ' . $this->fornitore->anticipo_residuo);
                }
            }
            if ($preview) {
                \Log::info('NOT updated anticipo_residuo for fornitore ID: ' . $this->fornitore->name
                    . ' by ' . $this->anticipo
                    . '. New value: ' . $this->fornitore->anticipo_residuo - $this->anticipo);
            }

            return true;
        } catch (\Exception $e) {
            \Log::error("Errore durante l'invio del proforma #{$this->id}: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Send email
     *
     * @param string $email Recipient email address
     * @param string|null $subject Optional custom subject
     * @param string|null $message Optional custom message
     * @return bool
     */
    public function sendEmail($email, $subject = null, $message = null)
    {
        try {
            $subject = $subject ?? "Proforma #{$this->id} - {$this->fornitore->name}";
            $message = $message ?? "In allegato trovi la proforma #{$this->id}";

            Mail::to($email)
                ->send(new ProformaEmail($this, $subject, $message));

            // Update the proforma status
            $this->update([
                'stato' => 'Inviato',
                'sended_at' => now(),
            ]);

            return true;
        } catch (\Exception $e) {
            \Log::error("Errore nell'invio dell'email per la proforma #{$this->id}: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Send the proforma via email
     *
     * @param string $email Recipient email address
     * @param string|null $subject Optional custom subject
     * @param string|null $message Optional custom message
     * @return bool
     */
    public function testEmail($email = null, $subject = null, $message = null)
    {
        try {
            // Ensure relationships are loaded
            if (!$this->relationLoaded('fornitore')) {
                $this->load('fornitore');
            }
            $email = 'piergiuseppe.meo@gmail.com';
            $subject = $subject ?? "Test Proforma #{$this->id}";
            $message = $message ?? "This is a test email for Proforma #{$this->id}";
            // Log what we're about to do
            \Log::info("Sending test email for Proforma #{$this->id} to {$email}");
            // Simple email without using the view
            Mail::raw($message, function ($message) use ($email, $subject) {
                $message
                    ->to($email)
                    ->subject($subject);
            });
            \Log::info("Test email sent successfully to {$email}");
            return true;
        } catch (\Exception $e) {
            \Log::error('Failed to send test email: ' . $e->getMessage());
            return false;
        }
    }
}
