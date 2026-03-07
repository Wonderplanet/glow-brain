using System.Collections.Generic;
using System.Linq;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ModelFactories;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.Models.OprData;
using GLOW.Core.Domain.Repositories;
using GLOW.Core.Domain.ValueObjects.Gacha;
using GLOW.Scenes.GachaContent.Domain.Calculator;
using GLOW.Scenes.GachaContent.Domain.Model;
using Zenject;

namespace GLOW.Scenes.GachaContent.Domain.UseCases
{
    public class StepUpGachaContentUseCaseModelFactory : IStepUpGachaContentUseCaseModelFactory
    {
        [Inject] IOprStepUpGachaRepository OprStepUpGachaRepository { get; }
        [Inject] IOprStepUpGachaStepRepository OprStepUpGachaStepRepository { get; }
        [Inject] IOprStepUpGachaStepRewardRepository OprStepUpGachaStepRewardRepository { get; }
        [Inject] IGameRepository GameRepository { get; }
        [Inject] IMstItemDataRepository MstItemDataRepository { get; }
        [Inject] IPlayerResourceModelFactory PlayerResourceModelFactory { get; }

        StepUpGachaContentUseCaseModel IStepUpGachaContentUseCaseModelFactory.Create(OprGachaModel gachaModel)
        {
            // ステップアップガチャでない場合はEmpty
            if (gachaModel.GachaType != GachaType.Stepup)
            {
                return StepUpGachaContentUseCaseModel.Empty;
            }

            var gameFetchOtherModel = GameRepository.GetGameFetchOther();
            var userGachaModel =
                gameFetchOtherModel.UserGachaModels.FirstOrDefault(model => model.OprGachaId == gachaModel.Id) ?? 
                UserGachaModel.CreateById(gachaModel.Id);

            var stepUpGachaData = OprStepUpGachaRepository.GetOprStepUpGachaModelFirstOrDefault(gachaModel.Id);
            var stepDatas = OprStepUpGachaStepRepository.GetOprStepUpGachaModels(gachaModel.Id);
            var stepRewardDatas = OprStepUpGachaStepRewardRepository.GetOprStepUpGachaStepRewardModels(gachaModel.Id);

            // UserGachaModelから現在のステップとループ回数を取得
            var currentStepNumber = userGachaModel.CurrentStepNumber.Value == 0
                ? new StepUpGachaCurrentStepNumber(1) // デフォルトは1ステップ目
                : new StepUpGachaCurrentStepNumber(userGachaModel.CurrentStepNumber.Value);
            var currentLoopCount = userGachaModel.CurrentLoopCount;

            // 各ステップのUseCaseModelを作成
            var steps = stepDatas
                .Select(stepData => CreateStepUpGachaStepUseCaseModel(stepData, stepRewardDatas, currentLoopCount))
                .ToList();

            return new StepUpGachaContentUseCaseModel(
                steps,
                currentStepNumber,
                stepUpGachaData.MaxStepNumber,
                stepUpGachaData.MaxLoopCount,
                currentLoopCount);
        }

        StepUpGachaStepUseCaseModel CreateStepUpGachaStepUseCaseModel(
            OprStepUpGachaStepModel stepData,
            IReadOnlyList<OprStepUpGachaStepRewardModel> stepRewardDatas,
            StepUpGachaCurrentLoopCount currentLoopCount)
        {
            // コストアイコンパスを取得
            var costIconAssetPath = GachaContentCalculator.GetItemIconAssetPath(
                stepData.CostType, 
                stepData.MstCostId, 
                MstItemDataRepository);
            
            // このステップの報酬を取得
            var stepRewards = stepRewardDatas
                .Where(r => r.StepNumber.Value == stepData.StepNumber.Value)
                .Select(r => CreateStepUpGachaStepRewardUseCaseModel(r))
                .ToList();
            
            // 初回無料判定: CostTypeがFree、または(IsFirstFreeがtrueで、かつ1ループ目(CurrentLoopCount == 0))の場合
            var isFirstLoopCycle = currentLoopCount.Value == 0;
            var isFree = stepData.CostType == CostType.Free || (stepData.IsFirstFree && isFirstLoopCycle)
                ? GachaFreeDrawFlag.True
                : GachaFreeDrawFlag.False;
            
            return new StepUpGachaStepUseCaseModel(
                stepData.StepNumber,
                stepData.CostType,
                stepData.MstCostId,
                stepData.CostAmount,
                stepData.DrawCount,
                costIconAssetPath,
                stepData.FixedPrizeDescription,
                isFree,
                stepRewards);
        }

        StepUpGachaStepRewardUseCaseModel CreateStepUpGachaStepRewardUseCaseModel(
            OprStepUpGachaStepRewardModel rewardData)
        {
            // PlayerResourceModelFactoryを使ってリソースモデルを作成
            var playerResourceModel = PlayerResourceModelFactory.Create(
                rewardData.ResourceType,
                rewardData.ResourceId,
                rewardData.ResourceAmount.ToPlayerResourceAmount());
            
            return new StepUpGachaStepRewardUseCaseModel(
                rewardData.LoopCountTarget,
                playerResourceModel);
        }
    }
}

