<?php

namespace App\Models\Adm;

use App\Models\Mst\OprGacha;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class AdmGachaCaution extends AdmModel
{
    use HasUuids;

    protected $casts = [
        'html_json' => 'string',
    ];

    protected $table = 'adm_gacha_cautions';
    protected $guarded = [];

    public function author()
    {
        return $this->hasOne(AdmUser::class, 'id', 'author_adm_user_id');
    }

    public function getAuthorNameAttribute(): string
    {
        return $this->author?->name ?? '';
    }

    public function getHtmlString(): string
    {
        return tiptap_converter()->asHTML($this->html_json);
    }

    public function formatToResponse(): array
    {
        return parent::toArray();
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function opr_gachas()
    {
        return $this->hasMany(OprGacha::class, 'display_gacha_caution_id', 'id');
    }

    public static function createFromResponseArray(array $response): self
    {
        $model = new self();
        $model->fill($response);
        return $model;
    }

    public function formatToInsertArray(): array
    {
        $array = $this->toArray();

        $now = CarbonImmutable::now();
        $array['created_at'] = $now;
        $array['updated_at'] = $now;

        return $array;
    }
}
