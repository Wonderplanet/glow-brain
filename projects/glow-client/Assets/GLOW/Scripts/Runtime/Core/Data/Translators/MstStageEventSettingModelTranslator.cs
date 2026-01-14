using GLOW.Core.Data.Data;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.InGame;
using GLOW.Core.Domain.ValueObjects.Stage;

namespace GLOW.Core.Data.Translators
{
    public static class MstStageEventSettingModelTranslator
    {
        public static MstStageEventSettingModel Translate(MstStageEventSettingData data)
        {
            return new MstStageEventSettingModel(
                new MasterDataId(data.Id),
                string.IsNullOrEmpty(data.MstStageId) ? MasterDataId.Empty : new MasterDataId(data.MstStageId),
                data.ResetType,
                data.ClearableCount == null ? ClearableCount.Empty : new ClearableCount(data.ClearableCount.Value),
                new AdChallengeCount(data.AdChallengeCount),
                string.IsNullOrEmpty(data.BackgroundAssetKey)
                    ? KomaBackgroundAssetKey.Empty
                    : new KomaBackgroundAssetKey(data.BackgroundAssetKey),
                data.StartAt,
                data.EndAt
            );
        }
    }
}
