<?php

declare(strict_types=1);

namespace App\Domain\Resource\Mst\Repositories;

use App\Domain\Common\Constants\ErrorCode;
use App\Domain\Common\Enums\Language;
use App\Domain\Common\Exceptions\GameException;
use App\Domain\Resource\Mst\Entities\OprGachaI18nEntity;
use App\Domain\Resource\Mst\Models\OprGachaI18n;
use App\Infrastructure\MasterRepository;
use Illuminate\Support\Collection;

class OprGachaI18nRepository
{
    public function __construct(
        private MasterRepository $masterRepository,
    ) {
    }

    /**
     * @return Collection<OprGachaI18nEntity>
     * @throws GameException
     */
    public function getAll(): Collection
    {
        return $this->masterRepository->get(OprGachaI18n::class);
    }

    /**
     * @param string $oprGachaId
     * @param Language $language
     *
     * @return OprGachaI18nEntity|null
     * @throws GameException
     */
    public function getByOprGachaIdAndLanguage(string $oprGachaId, Language $language): ?OprGachaI18nEntity
    {
        $entities = $this->getAll()->filter(function ($entity) use ($oprGachaId, $language) {
            return $entity->getOprGachaId() === $oprGachaId && $entity->getLanguage() === $language;
        });

        return $entities->first();
    }

    /**
     * @param string $oprGachaId
     * @param Language $language
     *
     * @return OprGachaI18nEntity
     * @throws GameException
     */
    public function getByOprGachaIdAndLanguageWithError(string $oprGachaId, Language $language): OprGachaI18nEntity
    {
        $entity = $this->getByOprGachaIdAndLanguage($oprGachaId, $language);
        if ($entity === null) {
            throw new GameException(
                ErrorCode::MST_NOT_FOUND,
                sprintf('opr_gachas_i18n record is not found. (opr_gacha_id: %s)', $oprGachaId),
            );
        }

        return $entity;
    }

    /**
     * @param string $language
     *
     * @return Collection
     * @throws GameException
     */
    public function getByLanguage(string $language): Collection
    {
        return $this->getAll()->filter(function ($entity) use ($language) {
            return $entity->getLanguage()->value === $language;
        });
    }
}
