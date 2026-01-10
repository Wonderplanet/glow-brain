<?php

namespace App\Models\Opr;

use App\Domain\Resource\Mst\Models\OprMasterReleaseControl as AppReleaseControl;

class OprMasterReleaseControl extends AppReleaseControl
{
    const STATUS_FUTURE = 'future'; // 将来的に使われる
    const STATUS_APPLYING = 'applying'; // 現在適用
    const STATUS_EXPIRED = 'expired'; // 過去使われた
    const STATUS_UNUSED = 'unused'; // 同じrelease_keyで後から作られた物があるので使用されない

    private string $status = "";

    /**
     * @param string $status
     * @return array
     */
    public function formatToStatusResponse(string $status = ''): array
    {
        return [
            'id'                  => $this->id,
            'status'              => $this->getStatus(),
            'release_key'         => $this->release_key,
            'git_revision'        => $this->git_revision,
            'client_data_hash'    => $this->client_data_hash,
            'release_at'          => $this->release_at,
            'release_description' => $this->release_description,
            'created_at'          => $this->created_at->format('Y-m-d H:i:s'),
        ];
    }

    public function getStatus(): string
    {
        return $this->status;
    }
    public function setStatus(string $status): void
    {
        $this->status = $status;
    }

    public function getColor(): string
    {
        switch ($this->getStatus()) {
            case self::STATUS_APPLYING: return "success";
            case self::STATUS_FUTURE: return "warning";
            case self::STATUS_EXPIRED: return "danger";
            case self::STATUS_UNUSED: return "secondary";
            default: return "primary";
        }
    }
}
