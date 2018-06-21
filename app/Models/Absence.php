<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
/**
 * @property int $id
 * @property string $name
 * @property string $description
 * @property string $last_updated_at
 * @property int $last_updated_by_employee
 * @property string $created_at
 * @property int $created_by_employee
 * @property string $delete_flag
 * @property Employee[] $employees
 */
class Absence extends Model
{
    /**
     * @var array
     */

    public $table = 'absences';
    protected $fillable = [
        'employee_id',
        'absence_type_id',
        'absence_status_id',
        'from_date',
        'to_date',
        'reason',
        'description',
        'is_deny',
        'is_late',
        'is_summary',
        'last_updated_at', 'last_updated_by_employee', 'created_at', 'created_by_employee', 'delete_flag'];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function employee()
    {
        return $this->belongsTo('App\Models\Employee','employees_id');
    }

    public function absenceType()
    {
        return $this->belongsTo('App\Models\AbsenceType', 'absence_types_id');
    }

    public function absenceStatus()
    {
        return $this->belongsTo('App\Models\AbsenceStatus', 'absence_status_id');
    }

}
