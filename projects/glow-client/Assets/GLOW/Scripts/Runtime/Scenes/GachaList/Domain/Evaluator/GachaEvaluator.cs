using System;
using System.Collections.Generic;
using System.Linq;
using GLOW.Core.Domain.Calculator;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.Models.OprData;
using GLOW.Core.Domain.ValueObjects.Gacha;
using Zenject;

namespace GLOW.Scenes.GachaList.Domain.Evaluator
{
    public class GachaEvaluator : IGachaEvaluator
    {
        [Inject] IDailyResetTimeCalculator DailyResetTimeCalculator { get; }

        // ガシャ優先度ソート(降順)
        List<OprGachaModel> IGachaEvaluator.SortOprGachaModelByPriority(IReadOnlyList<OprGachaModel> models)
        {
            var sortedModels = models.OrderBy(x => x.GachaPriority.Value).Reverse().ToList();
            return sortedModels;
        }

        // ガチャの消費コストソート(降順)
        List<OprGachaUseResourceModel> IGachaEvaluator.SortGachaUseResourceModelByPriority(List<OprGachaUseResourceModel> models)
        {
            var sortedModels = models.OrderBy(x => x.GachaCostPriority.Value).Reverse().ToList();
            return sortedModels;
        }

        bool IGachaEvaluator.IsFreePlay(
            OprGachaModel gachaModel,
            UserGachaModel userGachaModel)
        {
            var isFreeDrawable = IsFreeDrawGachaDrawable(gachaModel, userGachaModel);
            var isAdDrawable = IsAdGachaDrawable(gachaModel, userGachaModel);
            return isFreeDrawable || isAdDrawable;
        }

        // 無料キャンペーンガチャが引けるか
        DrawableFlag IGachaEvaluator.IsFreeDrawGachaDrawable(
            OprGachaModel gachaModel,
            UserGachaModel userGachaModel)
        {
            return IsFreeDrawGachaDrawable(gachaModel, userGachaModel);
        }

        // 広告ガチャが引けるか
        AdGachaDrawableFlag IGachaEvaluator.IsAdGachaDrawable(
            OprGachaModel gachaModel,
            UserGachaModel userGachaModel)
        {
            return IsAdGachaDrawable(gachaModel, userGachaModel);
        }

        // 指定リソースでガチャが引けるか
        DrawableFlag IGachaEvaluator.IsGachaDrawable(
            OprGachaUseResourceModel model,
            GameFetchModel gameFetchModel,
            GameFetchOtherModel gameFetchOtherModel,
            string platformId,
            OprGachaModel oprGachaModel,
            UserGachaModel userGachaModel)
        {
            // 上限に届いている場合は引けない
            if (HasReachedLimitedCount(oprGachaModel, userGachaModel))
            {
                return DrawableFlag.False;
            }

            switch (model.CostType)
            {
                case CostType.Coin:
                    var coin = gameFetchModel.UserParameterModel.Coin.Value;
                    return new DrawableFlag(coin >= model.CostAmount.Value);

                case CostType.Diamond:
                    var diamond = gameFetchModel.UserParameterModel.GetTotalDiamond(platformId).Value;
                    return new DrawableFlag(diamond >= model.CostAmount.Value);

                case CostType.PaidDiamond:
                    var paidDiamond = gameFetchModel.UserParameterModel
                        .GetPaidDiamondFromPlatform(platformId).Value;
                    return new DrawableFlag(paidDiamond >= model.CostAmount.Value);

                case CostType.Item:
                    var item = gameFetchOtherModel.UserItemModels.FirstOrDefault(x => x.MstItemId == model.MstCostId);
                    return new DrawableFlag(item != null && item.Amount.Value >= model.CostAmount.Value);

                case CostType.Free:
                    return DrawableFlag.True;

                case CostType.Ad:
                // 広告ガチャは別途判定
                case CostType.Cash:
                default:
                    return DrawableFlag.False;
            }
        }

        bool IGachaEvaluator.HasReachedDrawLimitedCount(
            OprGachaModel oprGachaModel,
            UserGachaModel userGachaModel)
        {
            return HasReachedLimitedCount(oprGachaModel, userGachaModel);
        }

        AdGachaDrawableCount IGachaEvaluator.CalculateAdGachaDrawableCount(
            OprGachaModel gachaModel,
            UserGachaModel userGachaModel)
        {
            var isAdGachaDrawable = IsAdGachaDrawable(gachaModel, userGachaModel);
            if (!isAdGachaDrawable)
            {
                return AdGachaDrawableCount.Zero;
            }

            // 前回引いた日付からリセット後の場合は最大回数を返す
            if (DailyResetTimeCalculator.IsPastDailyRefreshTime(userGachaModel.AdPlayedAt))
            {
                return gachaModel.DailyAdPlayLimitCount.ToAdGachaDrawableCount();
            }

            var drawableCount = gachaModel.DailyAdPlayLimitCount - userGachaModel.DailyAdPlayedCount;
            return drawableCount;
        }

        bool IGachaEvaluator.IsExpiredUnlockDuration(
            GachaUnlockDurationHours unlockDurationHours,
            GachaExpireAt gachaExpireAt,
            DateTimeOffset now)
        {
            // 期間限定ではない場合、開催中のためfalseを返す
            if(unlockDurationHours.IsEmpty()) return false;

            // 期間限定で終了期限がない場合、開催中ではないためtrueを返す
            if (gachaExpireAt.IsEmpty()) return true;

            // expires_atの期間外の場合、開催中ではないためtrueを返す
            if (gachaExpireAt.Value < now) return true;

            return false;
        }

        static bool HasReachedLimitedCount(
            OprGachaModel oprGachaModel,
            UserGachaModel userGachaModel)
        {
            var totalPlayLimitCount = oprGachaModel.TotalPlayLimitCount;
            var dailyPlayLimitCount = oprGachaModel.DailyPlayLimitCount;
            var playedCount = userGachaModel.PlayedCount;
            var dailyPlayedCount = userGachaModel.DailyPlayedCount;

            // 最大引ける回数設定があり、超えている場合は引けない
            if (!totalPlayLimitCount.IsUnlimited() && playedCount.Value >= totalPlayLimitCount.Value)
            {
                return true;
            }

            // 1日に引ける回数が設定があり、超えている場合は引けない
            if (!dailyPlayLimitCount.IsUnlimited() && dailyPlayedCount.Value >= dailyPlayLimitCount.Value)
            {
                return true;
            }

            return false;
        }

        DrawableFlag IsFreeDrawGachaDrawable(OprGachaModel gachaModel, UserGachaModel userGachaModel)
        {
            // 無料ガシャでは無い場合は引けない
            if (gachaModel.GachaType != GachaType.Free)
            {
                return DrawableFlag.False;
            }

            // 最大数が設定され、最大引ける回数を超えている場合は引けない
            if (gachaModel.TotalPlayLimitCount != null &&
                userGachaModel.PlayedCount.Value >= gachaModel.TotalPlayLimitCount.Value)
            {
                return DrawableFlag.False;
            }

            // 1日に引ける回数が設定され、超えている場合は引けない
            if (userGachaModel.DailyPlayedCount.Value >= gachaModel.DailyPlayLimitCount.Value)
            {
                return DrawableFlag.False;
            }

            return new DrawableFlag(true);
        }

        AdGachaDrawableFlag IsAdGachaDrawable(OprGachaModel gachaModel, UserGachaModel userGachaModel)
        { 
            // 広告ガシャが利用不可・未設定の場合は引けない
            if (gachaModel.EnableAdPlay == null || gachaModel.EnableAdPlay.Value == false)
            {
                return AdGachaDrawableFlag.False;
            }

            // 広告ガシャの最大数が設定され、超えている場合は引けない ※現状使用されない想定
            if (!gachaModel.TotalAdPlayLimitCount.IsUnlimited() &&
                userGachaModel.AdPlayedCount.Value >= gachaModel.TotalAdPlayLimitCount.Value)
            {
                return AdGachaDrawableFlag.False;
            }

            // 1日に引ける広告ガシャの回数が設定され、超えている場合は引けない
            if (!gachaModel.DailyAdPlayLimitCount.IsUnlimited() &&
                userGachaModel.DailyAdPlayedCount.Value >= gachaModel.DailyAdPlayLimitCount.Value)
            {
                // 上限回数を超えていても前回引いた日付からリセット後の場合は引ける(引くと日跨ぎダイアログが表示される)
                if (DailyResetTimeCalculator.IsPastDailyRefreshTime(userGachaModel.AdPlayedAt))
                {
                    return AdGachaDrawableFlag.True;
                }
                
                return AdGachaDrawableFlag.False;
            }

            return AdGachaDrawableFlag.True;
        }
    }
}
