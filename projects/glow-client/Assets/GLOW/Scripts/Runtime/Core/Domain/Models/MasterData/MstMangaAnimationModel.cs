using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.InGame;

namespace GLOW.Core.Domain.Models
{
    public record MstMangaAnimationModel(
        MasterDataId Id,
        MasterDataId MstStageId,
        MangaAnimationConditionType ConditionType,
        MangaAnimationConditionValue ConditionValue,
        TickCount AnimationStartDelay,
        MangaAnimationSpeed AnimationSpeed,
        MangaAnimationBattlePauseFlag IsPause,
        MangaAnimationSkipPossibleFlag CanSkip,
        MangaAnimationAssetKey AssetKey)
    {
        public static MstMangaAnimationModel Empty { get; } = new(
            MasterDataId.Empty,
            MasterDataId.Empty,
            MangaAnimationConditionType.None,
            MangaAnimationConditionValue.Empty,
            TickCount.Empty,
            MangaAnimationSpeed.Empty,
            MangaAnimationBattlePauseFlag.False,
            MangaAnimationSkipPossibleFlag.False,
            MangaAnimationAssetKey.Empty
        );

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
    }
}
