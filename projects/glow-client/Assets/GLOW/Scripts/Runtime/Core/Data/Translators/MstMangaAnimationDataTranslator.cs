using GLOW.Core.Data.Data;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.InGame;

namespace GLOW.Core.Data.Translators
{
    public class MstMangaAnimationDataTranslator
    {
        public static MstMangaAnimationModel ToMangaAnimationModel(MstMangaAnimationData data)
        {
            return new MstMangaAnimationModel(
                new MasterDataId(data.Id),
                new MasterDataId(data.MstStageId),
                data.ConditionType,
                new MangaAnimationConditionValue(data.ConditionValue),
                new TickCount(data.AnimationStartDelay),
                data.AnimationSpeed > 0f
                    ? new MangaAnimationSpeed(data.AnimationSpeed)
                    : MangaAnimationSpeed.Empty,
                new MangaAnimationBattlePauseFlag(data.IsPause),
                new MangaAnimationSkipPossibleFlag(data.CanSkip),
                new MangaAnimationAssetKey(data.AssetKey)
            );
        }
    }
}
