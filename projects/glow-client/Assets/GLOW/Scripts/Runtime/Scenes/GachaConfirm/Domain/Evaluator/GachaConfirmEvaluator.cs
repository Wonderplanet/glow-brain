using System.Linq;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.Models.OprData;
using GLOW.Core.Domain.ValueObjects.Gacha;
using WPFramework.Domain.Modules;
using Zenject;

namespace GLOW.Scenes.GachaConfirm.Domain.Evaluator
{
    public class GachaConfirmEvaluator
    {
        [Inject] ISystemInfoProvider SystemInfoProvider { get; }

        public DrawableFlag CanDrawStepUpGacha(
            OprStepUpGachaStepModel stepModel,
            GameFetchModel gameFetchModel,
            GameFetchOtherModel gameFetchOtherModel,
            UserGachaModel userGachaModel)
        {
            // 初回無料判定: IsFirstFreeがtrueで、かつ1ループ目(CurrentLoopCount == 0)の場合
            var isFirstLoopCycle = userGachaModel.CurrentLoopCount.Value == 0;
            var isFirstFree = stepModel.IsFirstFree && isFirstLoopCycle;
            if (isFirstFree)
            {
                return DrawableFlag.True;
            }
            
            var costType = stepModel.CostType;
            var costAmount = stepModel.CostAmount;

            // コストチェック
            switch (costType)
            {
                case CostType.Item:
                    var itemAmount = gameFetchOtherModel.UserItemModels
                        .FirstOrDefault(x => x.MstItemId == stepModel.MstCostId)?.Amount;
                    var hasEnoughItem = itemAmount != null && itemAmount.Value >= costAmount.Value;
                    return hasEnoughItem ? DrawableFlag.True : DrawableFlag.False;

                case CostType.Diamond:
                    var totalDiamond = gameFetchModel.UserParameterModel.FreeDiamond.Value
                        + gameFetchModel.UserParameterModel.GetPaidDiamondFromPlatform(SystemInfoProvider.GetApplicationSystemInfo().PlatformId).Value;
                    return totalDiamond >= costAmount.Value ? DrawableFlag.True : DrawableFlag.False;

                case CostType.PaidDiamond:
                    var paidDiamond = gameFetchModel.UserParameterModel.GetPaidDiamondFromPlatform(SystemInfoProvider.GetApplicationSystemInfo().PlatformId);
                    return paidDiamond.Value >= costAmount.Value ? DrawableFlag.True : DrawableFlag.False;

                case CostType.Coin:
                    return gameFetchModel.UserParameterModel.Coin.Value >= costAmount.Value ? DrawableFlag.True : DrawableFlag.False;

                default:
                    return DrawableFlag.True;
            }
        }
    }
}


