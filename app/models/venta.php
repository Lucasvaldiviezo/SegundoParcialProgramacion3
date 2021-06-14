<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Venta extends Model
{
    use SoftDeletes;

    protected $primaryKey = 'id';
    protected $table = 'ventas';
    public $incrementing = true;
    public $timestamps = false;

    const DELETED_AT = 'fecha_de_baja';

    protected $fillable = [
        'id_cliente', 'id_critopmoneda', 'cantidad', 'total', 'fecha_de_compra', 'foto'
    ];
}

?>