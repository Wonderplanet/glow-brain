 using System.Linq;
 using GLOW.Core.Domain.Constants;
 using GLOW.Core.Domain.ValueObjects;
 using GLOW.Core.Extensions;
 using GLOW.Core.Presentation.Translators;
 using GLOW.Scenes.GachaContent.Domain.Model;
using GLOW.Scenes.GachaList.Domain.Model;
using GLOW.Scenes.GachaList.Presentation.ViewModels.StepUpGacha;

namespace GLOW.Scenes.GachaContent.Presentation.Translator
{
    public static class StepUpGachaViewModelTranslator
    {
        public static StepUpGachaViewModel Translate(StepUpGachaContentUseCaseModel useCaseModel)
        {
            if (useCaseModel.IsEmpty())
            {
                return StepUpGachaViewModel.Empty;
            }

            var detailViewModels = useCaseModel.Steps
                .Select(step => TranslateStepUpGachaDetailViewModel(step, useCaseModel.CurrentLoopCount.Value))
                .ToList();

            var userStepUpStepCount = new StepUpStepCount(
                useCaseModel.UserCurrentStepNumber.Value,
                useCaseModel.MaxStepNumber.Value,
                useCaseModel.CurrentLoopCount.Value,
                useCaseModel.MaxLoopCount.Value);

            return new StepUpGachaViewModel(detailViewModels, userStepUpStepCount);
        }

        static StepUpGachaDetailViewModel TranslateStepUpGachaDetailViewModel(
            StepUpGachaStepUseCaseModel stepModel,
            int currentLoopCount)
        {
            // 現在のループ回数に該当するおまけ報酬を取得
            // LoopCountTargetがAllの場合は全ループ適用、それ以外は現在のループ回数と一致する報酬を取得
            var currentLoopReward = stepModel.StepRewards
                .FirstOrDefault(
                    reward => reward.LoopCountTarget.IsAll() || reward.LoopCountTarget.Value == currentLoopCount,
                    StepUpGachaStepRewardUseCaseModel.Empty);

            var hasOmake = currentLoopReward != null && !currentLoopReward.IsEmpty()
                ? ExistStepUpGachaOmakeFlag.True
                : ExistStepUpGachaOmakeFlag.False;
            
            var omakeResourceType = ResourceType.Coin;
            var omakeIconAssetPath = PlayerResourceIconAssetPath.Empty;
            var omakeAmount = ItemAmount.Zero;
            
            if (hasOmake.Value && !currentLoopReward.IsEmpty())
            {
                var playerResourceIconViewModel = PlayerResourceIconViewModelTranslator.ToPlayerResourceIconViewModel(
                    currentLoopReward.PlayerResourceModel);
                omakeResourceType = currentLoopReward.PlayerResourceModel.Type;
                omakeIconAssetPath = playerResourceIconViewModel.AssetPath;
                omakeAmount = new ItemAmount(currentLoopReward.PlayerResourceModel.Amount.Value);
            }

            return new StepUpGachaDetailViewModel(
                stepModel.StepNumber,
                stepModel.CostType,
                stepModel.MstCostId,
                stepModel.CostAmount,
                stepModel.DrawCount,
                stepModel.CostIconAssetPath,
                stepModel.FixedPrizeDescription,
                stepModel.IsFree,
                omakeResourceType,
                hasOmake,
                omakeIconAssetPath,
                omakeAmount);
        }
    }
}





