<?php
// app/Models/InvoiceIn.php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InvoiceIn extends Model
{
    use HasFactory;

    protected $table = 'invoiceins';
    protected $primaryKey = 'id';
    public $incrementing = true;
    protected $keyType = 'integer';

    protected $fillable = [
        'tipo_di_documento',
        'nr_documento',
        'nr_fatt_acq_registrata',
        'nr_nota_cr_acq_registrata',
        'data_ricezione_fatt',
        'codice_td',
        'nr_cliente_fornitore',
        'nome_fornitore',
        'partita_iva',
        'nr_documento_fornitore',
        'allegato',
        'data_documento_fornitore',
        'data_primo_pagamento_prev',
        'imponibile_iva',
        'importo_iva',
        'importo_totale_fornitore',
        'importo_totale_collegato',
        'data_ora_invio_ricezione',
        'stato',
        'id_documento',
        'id_sdi',
        'nr_lotto_documento',
        'nome_file_doc_elettronico',
        'filtro_carichi',
        'cdc_codice',
        'cod_colleg_dimen_2',
        'allegato_in_file_xml',
        'note_1',
        'note_2'
    ];

    protected $casts = [
        'data_ricezione_fatt' => 'date',
        'data_documento_fornitore' => 'date',
        'data_primo_pagamento_prev' => 'date',
        'data_ora_invio_ricezione' => 'datetime',
        'imponibile_iva' => 'decimal:2',
        'importo_iva' => 'decimal:2',
        'importo_totale_fornitore' => 'decimal:2',
        'importo_totale_collegato' => 'decimal:2',
        'allegato_in_file_xml' => 'boolean',
    ];

    // Add relationships here if needed
    // For example, relationship with Fornitore
    public function fornitore()
    {
        return $this->belongsTo(Fornitore::class, 'partita_iva', 'piva');
    }
}
