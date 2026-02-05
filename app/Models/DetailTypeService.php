<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class DetailTypeService extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function r_detailsTypeService_typeService(): BelongsTo
    {
        return $this->belongsTo(TypeService::class, 'id_type_services');
    }

    public function r_detailsTypeService_service(): BelongsTo
    {
        return $this->belongsTo(Service::class, 'id_service');
    }

    public function r_detailsTypeService_site(): BelongsTo
    {
        return $this->belongsTo(Site::class, 'id_site');
    }

    public function r_details_type_service_entite(): BelongsTo
    {
        return $this->belongsTo(Entite::class, 'id_entite');
    }
}
