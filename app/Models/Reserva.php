<?php
// app/Models/Reserva.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Reserva extends Model
{
    protected $table = 'reserva';
    protected $primaryKey = 'ID_reserva';
    public $timestamps = false;

    protected $with = ['cliente', 'piscina'];
    
    protected $fillable = [
        'Fecha_reserva',
        'Hora_inicio',
        'Hora_fin',
        'Adelanto',
        'Descuento',
        'Tipo_reserva',
        'ID_Cliente',
        'ID_empleado',
        'ID_Piscina'
    ];

    public function cliente()
    {
        return $this->belongsTo(Cliente::class, 'ID_Cliente', 'ID_Cliente');
    }



    public function piscina()
    {
        return $this->belongsTo(Piscina::class, 'ID_Piscina', 'ID_Piscina');
    }
}

