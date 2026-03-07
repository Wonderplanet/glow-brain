using System.Linq;
using GLOW.Core.Domain.Calculator;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.Models.OprData;
using GLOW.Core.Domain.Repositories;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.Gacha;
using GLOW.Scenes.GachaConfirm.Domain.Evaluator;
using GLOW.Scenes.GachaConfirm.Domain.Model;
using GLOW.Scenes.GachaContent.Domain.Calculator;
using GLOW.Scenes.GachaList.Domain.Evaluator;
using GLOW.Scenes.PassShop.Domain.Factory;
using GLOW.Scenes.PassShop.Domain.Model;
using WPFramework.Domain.Modules;
using WPFramework.Modules.Log;
using Zenject;

namespace GLOW.Scenes.GachaConfirm.Domain.UseCases
{
    public class GachaConfirmDialogUseCase
    {
        [Inject] IOprGachaRepository OprGachaRepository { get; }
        [Inject] IOprGachaUseResourceRepository OprGachaUseResourceRepository { get; }
        [Inject] IOprStepUpGachaStepRepository OprStepUpGachaStepRepository { get; }
        [Inject] IGameRepository GameRepository { get; }
        [Inject] ISystemInfoProvider SystemInfoProvider { get; }
        [Inject] IMstItemDataRepository MstItemDataRepository { get; }
        [Inject] IHeldAdSkipPassInfoModelFactory HeldAdSkipPassInfoModelFactory { get; }
        [Inject] IDailyResetTimeCalculator DailyResetTimeCalculator { get; }
        [Inject] IGachaEvaluator GachaEvaluator { get; }
        [Inject] GachaConfirmEvaluator GachaConfirmEvaluator { get; }

        public GachaConfirmDialogUseCaseModel GetUseCaseModel(MasterDataId gachaId, GachaDrawType gachaDrawType)
        {
            var gameFetchModel = GameRepository.GetGameFetch();
            var gameFetchOtherModel = GameRepository.GetGameFetchOther();
            var oprGachaModel = OprGachaRepository.GetOprGachaModelFirstOrDefaultById(gachaId);
            
            // oprGachaModelがnullの場合は、エラーログを出力してEmptyモデルを返す
            if (oprGachaModel == null)
            {
                ApplicationLog.LogError(
                    nameof(GachaConfirmDialogUseCase),
                    $"ガチャマスターデータが見つかりません。GachaId: {gachaId.Value}");
                
                // エラー時のデフォルト値を返す
                return GachaConfirmDialogUseCaseModel.Empty;
            }
            
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
                        heldAdSkipInfoModel,
                        GetCurrentStepNumberForStepUpGacha(oprGachaModel.GachaType, userGachaModel)
                    );
                
                case GachaDrawType.Single:
                    // ステップアップガチャの場合は現在のステップ情報から取得
                    var singleStepUpModel = TryGetStepUpGachaUseCaseModel(
                        oprGachaModel,
                        userGachaModel,
                        gachaId,
                        gameFetchModel,
                        gameFetchOtherModel,
                        playerFreeDiamondAmount,
                        playerPaidDiamondAmount,
                        adGachaResetRemainingTimeSpan,
                        adGachaDrawableCount);
                    
                    if (singleStepUpModel != null)
                    {
                        return singleStepUpModel;
                    }

                    var singleUseResourceModels = gachaUseResourceModels
                        .Where(model => model.GachaDrawCount.Value == 1 && model.CostType != CostType.Ad)
                        // .OrderBy(model => model.GachaCostPriority)
                        // .Reverse()
                        .ToList();

                    oprGachaUseResourceModel = GachaContentCalculator.GetHighestPriorityUseResourceModel(singleUseResourceModels, gameFetchModel, gameFetchOtherModel, SystemInfoProvider.GetApplicationSystemInfo().PlatformId);
                    break;

                case GachaDrawType.Multi:
                    // ステップアップガチャの場合は現在のステップ情報から取得
                    var multiStepUpModel = TryGetStepUpGachaUseCaseModel(
                        oprGachaModel,
                        userGachaModel,
                        gachaId,
                        gameFetchModel,
                        gameFetchOtherModel,
                        playerFreeDiamondAmount,
                        playerPaidDiamondAmount,
                        adGachaResetRemainingTimeSpan,
                        adGachaDrawableCount);
                    
                    if (multiStepUpModel != null)
                    {
                        return multiStepUpModel;
                    }

                    var multiUseResourceModels = gachaUseResourceModels
                        .Where(model => model.GachaDrawCount.Value > 1 && model.CostType != CostType.Ad)
                        .OrderBy(model => model.GachaCostPriority)
                        .Reverse()
                        .ToList();
                    // 消費リソース取得 (消費リソースがない場合はプライオリティが低いものを返す)
                    oprGachaUseResourceModel = GachaContentCalculator.GetHighestPriorityUseResourceModel(multiUseResourceModels, gameFetchModel, gameFetchOtherModel, SystemInfoProvider.GetApplicationSystemInfo().PlatformId);
                    break;
            }

            // oprGachaUseResourceModelがnullの場合、ステップアップガチャのデータが不正
            if (oprGachaUseResourceModel == null || oprGachaUseResourceModel.IsEmpty())
            {
                ApplicationLog.LogError(
                    nameof(GachaConfirmDialogUseCase),
                    $"Failed to get UseResourceModel. GachaId: {gachaId.Value}, GachaDrawType: {gachaDrawType}, GachaType: {oprGachaModel.GachaType}");
                
                // 空のUseCaseModelを返す
                return GachaConfirmDialogUseCaseModel.Empty;
            }

            var costType = oprGachaUseResourceModel.CostType;
            var costAmount = oprGachaUseResourceModel.CostAmount;
            var gachaDrawCount = oprGachaUseResourceModel.GachaDrawCount;

            var costName = GetCostNameForItem(costType, oprGachaUseResourceModel.MstCostId);
            playerItemAmount = GetPlayerItemAmount(costType, oprGachaUseResourceModel.MstCostId, gameFetchOtherModel);
            costIconAssetPath = GetCostIconAssetPath(costType, oprGachaUseResourceModel.MstCostId);

            var diamondAmounts = GetDiamondAmountsAfterConsumption(
                costType,
                costAmount,
                playerFreeDiamondAmount,
                playerPaidDiamondAmount);

            playerFreeDiamondAmountAfterConsumption = diamondAmounts.freeDiamond;
            playerPaidDiamondAmountAfterConsumption = diamondAmounts.paidDiamond;

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
                HeldAdSkipPassInfoModel.Empty,
                GetCurrentStepNumberForStepUpGacha(oprGachaModel.GachaType, userGachaModel)
                );
        }

        GachaConfirmDialogUseCaseModel TryGetStepUpGachaUseCaseModel(
            OprGachaModel oprGachaModel,
            UserGachaModel userGachaModel,
            MasterDataId gachaId,
            GameFetchModel gameFetchModel,
            GameFetchOtherModel gameFetchOtherModel,
            FreeDiamond playerFreeDiamondAmount,
            PaidDiamond playerPaidDiamondAmount,
            AdGachaResetRemainingTimeSpan adGachaResetRemainingTimeSpan,
            AdGachaDrawableCount adGachaDrawableCount)
        {
            if (oprGachaModel.GachaType != GachaType.Stepup)
            {
                return null;
            }

            // UserGachaModelから現在のステップ番号を取得
            var stepNumber = userGachaModel.CurrentStepNumber.Value == 0
                ? new StepUpGachaStepNumber(1) // デフォルトは1ステップ目
                : new StepUpGachaStepNumber(userGachaModel.CurrentStepNumber.Value);
            
            var stepModel = OprStepUpGachaStepRepository.GetOprStepUpGachaStepModelFirstOrDefault(gachaId, stepNumber);
            
            if (stepModel.IsEmpty())
            {
                // ステップデータが見つからない場合は、通常のガチャロジックにフォールバック
                // これはデータエラーの可能性があるため、ログを出力
                ApplicationLog.LogWarning(
                    nameof(GachaConfirmDialogUseCase),
                    $"ステップアップガチャのステップデータが見つかりません。GachaId: {gachaId.Value}, StepNumber: {stepNumber.Value}, CurrentLoopCount: {userGachaModel.CurrentLoopCount.Value}");
                return null;
            }

            return CreateUseCaseModelFromStepModel(
                oprGachaModel,
                stepModel,
                userGachaModel,
                gameFetchModel,
                gameFetchOtherModel,
                playerFreeDiamondAmount,
                playerPaidDiamondAmount,
                adGachaResetRemainingTimeSpan,
                adGachaDrawableCount);
        }

        GachaConfirmDialogUseCaseModel CreateUseCaseModelFromStepModel(
            OprGachaModel oprGachaModel,
            OprStepUpGachaStepModel stepModel,
            UserGachaModel userGachaModel,
            GameFetchModel gameFetchModel,
            GameFetchOtherModel gameFetchOtherModel,
            FreeDiamond playerFreeDiamondAmount,
            PaidDiamond playerPaidDiamondAmount,
            AdGachaResetRemainingTimeSpan adGachaResetRemainingTimeSpan,
            AdGachaDrawableCount adGachaDrawableCount)
        {
            // 初回無料判定: IsFirstFreeがtrueで、かつ1ループ目(CurrentLoopCount == 0)の場合
            var isFirstLoopCycle = userGachaModel.CurrentLoopCount.Value == 0;
            var isFirstFree = stepModel.IsFirstFree && isFirstLoopCycle;
            
            var costType = isFirstFree ? CostType.Free : stepModel.CostType;
            var costAmount = isFirstFree ? CostAmount.Zero : stepModel.CostAmount;
            var gachaDrawCount = stepModel.DrawCount;

            var costName = GetCostNameForItem(costType, stepModel.MstCostId);
            var playerItemAmount = GetPlayerItemAmount(costType, stepModel.MstCostId, gameFetchOtherModel);
            var costIconAssetPath = GetCostIconAssetPath(costType, stepModel.MstCostId);

            var diamondAmounts = GetDiamondAmountsAfterConsumption(
                costType,
                costAmount,
                playerFreeDiamondAmount,
                playerPaidDiamondAmount);

            var playerFreeDiamondAmountAfterConsumption = diamondAmounts.freeDiamond;
            var playerPaidDiamondAmountAfterConsumption = diamondAmounts.paidDiamond;

            // ステップアップガチャの引ける判定を追加する必要があります
            var isGachaDrawable = GachaConfirmEvaluator.CanDrawStepUpGacha(
                stepModel,
                gameFetchModel,
                gameFetchOtherModel,
                userGachaModel);

            return new GachaConfirmDialogUseCaseModel(
                oprGachaModel.Id,
                oprGachaModel.GachaType,
                stepModel.MstCostId,
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
                HeldAdSkipPassInfoModel.Empty,
                GetCurrentStepNumberForStepUpGacha(oprGachaModel.GachaType, userGachaModel)
            );
        }

        ItemName GetCostNameForItem(CostType costType, MasterDataId mstCostId)
        {
            if (costType == CostType.Item)
            {
                return MstItemDataRepository.GetItem(mstCostId).Name;
            }

            return ItemName.Empty;
        }

        ItemAmount GetPlayerItemAmount(CostType costType, MasterDataId mstCostId, GameFetchOtherModel gameFetchOtherModel)
        {
            if (costType == CostType.Item)
            {
                var amount = gameFetchOtherModel.UserItemModels
                    .FirstOrDefault(x => x.MstItemId == mstCostId)?.Amount;
                
                return amount != null ? new ItemAmount(amount.Value) : new ItemAmount(0);
            }

            return new ItemAmount(0);
        }

        PlayerResourceIconAssetPath GetCostIconAssetPath(CostType costType, MasterDataId mstCostId)
        {
            if (costType == CostType.Item)
            {
                return GachaContentCalculator.GetItemIconAssetPath(costType, mstCostId, MstItemDataRepository);
            }

            return PlayerResourceIconAssetPath.Empty;
        }

        (FreeDiamond freeDiamond, PaidDiamond paidDiamond) GetDiamondAmountsAfterConsumption(
            CostType costType,
            CostAmount costAmount,
            FreeDiamond playerFreeDiamondAmount,
            PaidDiamond playerPaidDiamondAmount)
        {
            if (costType == CostType.Diamond)
            {
                var consumeDiamond = new TotalDiamond((int)costAmount.Value);
                var result = DiamondCalculator.CalculateAfterDiamonds(
                    playerPaidDiamondAmount,
                    playerFreeDiamondAmount,
                    consumeDiamond);
                
                return (result.free, result.paid);
            }

            if (costType == CostType.PaidDiamond)
            {
                var consumeDiamond = new TotalDiamond((int)costAmount.Value);
                var result = DiamondCalculator.CalculateAfterOnlyPaidDiamond(playerPaidDiamondAmount, consumeDiamond);
                
                return (playerFreeDiamondAmount, result);
            }

            // CostType.ItemまたはCostType.Freeの場合は消費しない
            return (playerFreeDiamondAmount, playerPaidDiamondAmount);
        }

        StepUpGachaCurrentStepNumber GetCurrentStepNumberForStepUpGacha(GachaType gachaType, UserGachaModel userGachaModel)
        {
            // ステップアップガチャではない場合はEmptyを返す
            if (gachaType != GachaType.Stepup)
            {
                return StepUpGachaCurrentStepNumber.Empty;
            }
            
            // ステップが0の場合は1ステップ目とみなす
            if (userGachaModel.CurrentStepNumber.Value == 0)
            {
                return new StepUpGachaCurrentStepNumber(1);
            }

            return userGachaModel.CurrentStepNumber;
        }
    }
}
