using System;
using System.Collections.Generic;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.Models.OprData;
using GLOW.Core.Domain.ValueObjects.Gacha;

namespace GLOW.Scenes.GachaList.Domain.Evaluator
{
    public interface IGachaEvaluator
    {
        List<OprGachaModel> SortOprGachaModelByPriority(IReadOnlyList<OprGachaModel> models);
        List<OprGachaUseResourceModel> SortGachaUseResourceModelByPriority(List<OprGachaUseResourceModel> models);
        bool IsFreePlay(OprGachaModel gachaModel, UserGachaModel userGachaModel);
        DrawableFlag IsFreeDrawGachaDrawable(OprGachaModel gachaModel, UserGachaModel userGachaModel);
        AdGachaDrawableFlag IsAdGachaDrawable(OprGachaModel gachaModel, UserGachaModel userGachaModel);
        DrawableFlag IsGachaDrawable(
            OprGachaUseResourceModel model,
            GameFetchModel gameFetchModel,
            GameFetchOtherModel gameFetchOtherModel,
            string platformId,
            OprGachaModel oprGachaModel,
            UserGachaModel userGachaModel);
        bool HasReachedDrawLimitedCount(OprGachaModel oprGachaModel, UserGachaModel userGachaModel);
        AdGachaDrawableCount CalculateAdGachaDrawableCount(
            OprGachaModel gachaModel,
            UserGachaModel userGachaModel);
        bool IsExpiredUnlockDuration(
            GachaUnlockDurationHours unlockDurationHours,
            GachaExpireAt gachaExpireAt,
            DateTimeOffset now);
    }
}
