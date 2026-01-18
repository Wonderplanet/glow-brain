using System.Linq;
using GLOW.Core.Domain.Calculator;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.Models.OprData;
using GLOW.Core.Domain.Repositories;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.Gacha;
using GLOW.Scenes.GachaConfirm.Domain.Model;
using GLOW.Scenes.GachaContent.Domain.Calculator;
using GLOW.Scenes.GachaList.Domain.Evaluator;
using GLOW.Scenes.PassShop.Domain.Factory;
using GLOW.Scenes.PassShop.Domain.Model;
using WPFramework.Domain.Modules;
using Zenject;

namespace GLOW.Scenes.GachaConfirm.Domain.UseCases
{
    public class GachaConfirmDialogUseCase
    {
        [Inject] IOprGachaRepository OprGachaRepository { get; }
        [Inject] IOprGachaUseResourceRepository OprGachaUseResourceRepository { get; }
        [Inject] IGameRepository GameRepository { get; }
        [Inject] ISystemInfoProvider SystemInfoProvider { get; }
        [Inject] IMstItemDataRepository MstItemDataRepository { get; }
        [Inject] IHeldAdSkipPassInfoModelFactory HeldAdSkipPassInfoModelFactory { get; }
        [Inject] IDailyResetTimeCalculator DailyResetTimeCalculator { get; }
        [Inject] IGachaEvaluator GachaEvaluator { get; }

        public GachaConfirmDialogUseCaseModel GetUseCaseModel(MasterDataId gachaId, GachaDrawType gachaDrawType)
        {
            var gameFetchModel = GameRepository.GetGameFetch();
            var gameFetchOtherModel = GameRepository.GetGameFetchOther();
            var oprGachaModel = OprGachaRepository.GetOprGachaModelFirstOrDefaultById(gachaId);
            var gachaUseResourceModels = OprGachaUseResourceRepository.FindByGachaId(oprGachaModel.Id);
            var userGachaModel = gameFetchOtherModel.UserGachaModels.FirstOrDefault(model => model.OprGachaId == gachaId) ?? UserGachaModel.CreateById(gachaId);
            var playerItemAmount = new ItemAmount(0);
            var costIconAssetPath = PlayerResourceIconAssetPath.Empty;
            var playerFreeDiamondAmount = gameFetchModel.UserParameterModel.FreeDiamond;
            var playerFreeDiamondAmountAfterConsumption = gameFetchModel.UserParameterModel.FreeDiamond;
            var playerPaidDiamondAmount = gameFetchModel.UserParameterModel.GetPaidDiamondFromPlatform(SystemInfoProvider.GetApplicationSystemInfo().PlatformId);
            var playerPaidDiamondAmountAfterConsumption = gameFetchModel.UserParameterModel.GetPaidDiamondFromPlatform(SystemInfoProvider.GetApplicationSystemInfo().PlatformId);
            var adGachaResetRemainingTimeSpan = AdGachaResetRemainingTimeSpan.Zero;
            var adGachaDrawableCount = AdGachaDrawableCount.Zero;
            OprGachaUseResourceModel oprGachaUseResourceModel = OprGachaUseResourceModel.Empty;

            switch (gachaDrawType)
            {
                case GachaDrawType.Ad:

                    // リセットの04:00までの時間
                    var remainingResetTime = DailyResetTimeCalculator.GetRemainingTimeToDailyReset();
                    adGachaResetRemainingTimeSpan = new AdGachaResetRemainingTimeSpan(remainingResetTime);
                    var isAdGachaDrawable = GachaEvaluator.IsAdGachaDrawable(oprGachaModel, userGachaModel);

                    var heldAdSkipInfoModel = HeldAdSkipPassInfoModelFactory.CreateHeldAdSkipPassInfo();
                    adGachaDrawableCount = GachaEvaluator.CalculateAdGachaDrawableCount(oprGachaModel, userGachaModel);
                    return new GachaConfirmDialogUseCaseModel(
                        oprGachaModel.Id,
                        oprGachaModel.GachaType,
                        MasterDataId.Empty,
                        CostType.Ad,
                        isAdGachaDrawable.ToDrawableFlag(),
                        oprGachaModel.GachaName,
                        CostAmount.Empty,
                        ItemName.Empty,
                        new GachaDrawCount(1),
                        costIconAssetPath,
                        playerItemAmount,
                        playerFreeDiamondAmount,
                        playerFreeDiamondAmountAfterConsumption,
                        playerPaidDiamondAmount,
                        playerPaidDiamondAmountAfterConsumption,
                        adGachaResetRemainingTimeSpan,
                        adGachaDrawableCount,
                        heldAdSkipInfoModel
                    );
                
                case GachaDrawType.Single:
                    var singleUseResourceModels = gachaUseResourceModels
                        .Where(model => model.GachaDrawCount.Value == 1 && model.CostType != CostType.Ad)
                        // .OrderBy(model => model.GachaCostPriority)
                        // .Reverse()
                        .ToList();

                    oprGachaUseResourceModel = GachaContentCalculator.GetHighestPriorityUseResourceModel(singleUseResourceModels, gameFetchModel, gameFetchOtherModel, SystemInfoProvider.GetApplicationSystemInfo().PlatformId);
                    break;

                case GachaDrawType.Multi:

                    var multiUseResourceModels = gachaUseResourceModels
                        .Where(model => model.GachaDrawCount.Value > 1 && model.CostType != CostType.Ad)
                        .OrderBy(model => model.GachaCostPriority)
                        .Reverse()
                        .ToList();
                    // 消費リソース取得 (消費リソースがない場合はプライオリティが低いものを返す)
                    oprGachaUseResourceModel = GachaContentCalculator.GetHighestPriorityUseResourceModel(multiUseResourceModels, gameFetchModel, gameFetchOtherModel, SystemInfoProvider.GetApplicationSystemInfo().PlatformId);
                    break;
            }

            var costType = oprGachaUseResourceModel.CostType;
            var costAmount = oprGachaUseResourceModel.CostAmount;
            var costName = ItemName.Empty;
            var gachaDrawCount = oprGachaUseResourceModel.GachaDrawCount;

            if (costType == CostType.Item)
            {
                costName = MstItemDataRepository.GetItem(oprGachaUseResourceModel.MstCostId).Name;
                var amount = gameFetchOtherModel.UserItemModels.FirstOrDefault(x => x.MstItemId == oprGachaUseResourceModel.MstCostId)?.Amount;
                playerItemAmount = amount != null ? new ItemAmount(amount.Value) : new ItemAmount(0);
                costIconAssetPath = GachaContentCalculator.GetItemIconAssetPath(costType, oprGachaUseResourceModel.MstCostId, MstItemDataRepository);
            }
            else if (costType == CostType.Diamond)
            {
                var consumeDiamond = new TotalDiamond((int)costAmount.Value);
                var result = DiamondCalculator.CalculateAfterDiamonds(playerPaidDiamondAmount, playerFreeDiamondAmount, consumeDiamond);
                playerFreeDiamondAmountAfterConsumption = result.free;
                playerPaidDiamondAmountAfterConsumption = result.paid;
            }
            else if (costType == CostType.PaidDiamond)
            {
                var consumeDiamond = new TotalDiamond((int)costAmount.Value);
                var result = DiamondCalculator.CalculateAfterOnlyPaidDiamond(playerPaidDiamondAmount, consumeDiamond);
                playerFreeDiamondAmountAfterConsumption = playerFreeDiamondAmount;  // 消費しないためそのまま
                playerPaidDiamondAmountAfterConsumption = result;
            }

            var isGachaDrawable = GachaEvaluator.IsGachaDrawable(
                oprGachaUseResourceModel,
                gameFetchModel,
                gameFetchOtherModel,
                SystemInfoProvider.GetApplicationSystemInfo().PlatformId,
                oprGachaModel,
                userGachaModel);

            return new GachaConfirmDialogUseCaseModel(
                oprGachaModel.Id,
                oprGachaModel.GachaType,
                oprGachaUseResourceModel.MstCostId,
                costType,
                isGachaDrawable,
                oprGachaModel.GachaName,
                costAmount,
                costName,
                gachaDrawCount,
                costIconAssetPath,
                playerItemAmount,
                playerFreeDiamondAmount,
                playerFreeDiamondAmountAfterConsumption,
                playerPaidDiamondAmount,
                playerPaidDiamondAmountAfterConsumption,
                adGachaResetRemainingTimeSpan,
                adGachaDrawableCount,
                HeldAdSkipPassInfoModel.Empty
                );
        }
    }
}
