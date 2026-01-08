using System;
using System.Collections.Generic;
using System.Linq;
using System.Threading;
using Cysharp.Threading.Tasks;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.Evaluator;
using GLOW.Core.Domain.Extensions;
using GLOW.Core.Domain.Factories;
using GLOW.Core.Domain.ModelFactories;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.Models.Tutorial;
using GLOW.Core.Domain.Repositories;
using GLOW.Core.Domain.Services;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Extensions;
using GLOW.Modules.Tutorial.Domain.Applier;
using GLOW.Modules.Tutorial.Domain.Definitions;
using GLOW.Scenes.AdventBattleResult.Domain.Model;
using GLOW.Scenes.ArtworkFragmentAcquisition.Domain.Models;
using GLOW.Scenes.BattleResult.Domain.Appliers;
using GLOW.Scenes.BattleResult.Domain.Evaluator;
using GLOW.Scenes.BattleResult.Domain.Factory;
using GLOW.Scenes.BattleResult.Domain.Models;
using GLOW.Scenes.InGame.Domain.Battle;
using GLOW.Scenes.InGame.Domain.Models;
using GLOW.Scenes.InGame.Domain.Repositories;
using GLOW.Scenes.InGame.Domain.ValueObjects;
using GLOW.Scenes.PvpBattleResult.Domain.Model;
using Zenject;

#if GLOW_INGAME_DEBUG
using GLOW.Debugs.InGame.Domain.Definitions;
#endif

namespace GLOW.Scenes.BattleResult.Domain.UseCases
{
    public class VictoryUseCase
    {
        [Inject] IGameRepository GameRepository { get; }
        [Inject] IInGameScene InGameScene { get; }
        [Inject] IGameService GameService { get; }
        [Inject] IGameManagement GameManagement { get; }
        [Inject] IRandomProvider RandomProvider { get; }
        [Inject] ISelectedStageEvaluator SelectedStageEvaluator { get; }
        [Inject] IUserExpGainModelsFactory UserExpGainModelsFactory { get; }
        [Inject] IMstTutorialRepository MstTutorialRepository { get; }
        [Inject] ITutorialService TutorialService { get; }
        [Inject] ITutorialStatusApplier TutorialStatusApplier { get; }
        [Inject] ITutorialVictoryResultModelFactory TutorialVictoryResultModelFactory { get; }
        [Inject] IPvpVictoryResultModelFactory PvpVictoryResultModelFactory { get; }
        [Inject] IPvpResultEvaluator PvpResultEvaluator { get; }
        [Inject] IStageVictoryResultModelFactory StageVictoryResultModelFactory { get; }
        [Inject] IAdventBattleVictoryResultModelFactory AdventBattleVictoryResultModelFactory { get; }
        [Inject] IPvpSelectedOpponentStatusCacheRepository PvpSelectedOpponentStatusCacheRepository { get; }

#if GLOW_DEBUG
        [Inject] IInGameDebugSettingRepository InGameDebugSettingRepository { get; }
#endif

        public async UniTask<VictoryResultModel> Victory(CancellationToken cancellationToken)
        {
#if GLOW_DEBUG
            // デバッグでAPIをスキップする場合はAPI叩かずに空Resultを返す
            var debugSetting = InGameDebugSettingRepository.Get();

            if (debugSetting.IsSkipApi)
            {
                var prevGameFetchModel = GameRepository.GetGameFetch();
                var prevUserParameter = prevGameFetchModel.UserParameterModel;

                // キャラ
                var pickupUnit = PickupPlayerUnit();
                var newDebugSetting = debugSetting with { IsSkipApi = false };
                InGameDebugSettingRepository.Save(newDebugSetting);

                var debugUserLevelUpResultModel = new UserLevelUpResultModel(
                    prevUserParameter.Exp,
                    prevUserParameter.Exp,
                    Array.Empty<UsrLevelRewardResultModel>());
                var debugUserExpGains = UserExpGainModelsFactory.CreateUserExpGainModels(
                    debugUserLevelUpResultModel,
                    prevUserParameter.Level,
                    prevUserParameter.Exp);

                return new VictoryResultModel(
                    pickupUnit.AssetKey,
                    debugUserExpGains,
                    UserLevelUpEffectModel.Empty,
                    Array.Empty<PlayerResourceModel>(),
                    Array.Empty<IReadOnlyList<PlayerResourceModel>>(),
                    new List<UnreceivedRewardReasonType>() {UnreceivedRewardReasonType.None },
                    Array.Empty<ArtworkFragmentAcquisitionModel>(),
                    ResultScoreModel.Empty,
                    ResultSpeedAttackModel.Empty,
                    AdventBattleResultScoreModel.Empty,
                    PvpBattleResultPointModel.Empty,
                    InGameType.Normal,
                    RemainingTimeSpan.Empty,
                    InGameRetryModel.Empty);
            }
#endif

            SelectedStageModel selectedStage = SelectedStageEvaluator.GetSelectedStage();
            var tutorialStatus = GameRepository.GetGameFetchOther().TutorialStatus;
            if (!tutorialStatus.IsCompleted())
            {
                // チュートリアル中のリザルトを上書きする
                var result = await VictoryInTutorial(cancellationToken, tutorialStatus, selectedStage.SelectedStageId);
                return result;
            }

            if (selectedStage.InGameType == InGameType.AdventBattle)
            {
                var result = await AdventBattleVictoryResultModelFactory.CreateVictoryAdventBattleResultModel(
                    cancellationToken,
                    selectedStage.SelectedMstAdventBattleId);
                return result;
            }
            else if (selectedStage.InGameType == InGameType.Pvp)
            {
                var pvpResultModel = PvpResultEvaluator.Evaluate();
                var result = await PvpVictoryResultModelFactory.CreateVictoryPvpResultModel(
                    cancellationToken,
                    pvpResultModel.ResultType);
                // バトルが終了し不要になったためPVPの対戦相手ステータスをクリア
                PvpSelectedOpponentStatusCacheRepository.ClearOpponentStatus();
                return result;
            }
            else
            {
                var result = await StageVictoryResultModelFactory.VictoryInStage(
                    cancellationToken,
                    selectedStage.SelectedStageId);
                return result;
            }
        }

        DeckUnitModel PickupPlayerUnit()
        {
            var index = RandomProvider.Range(InGameScene.DeckUnits.Count(c => !c.IsEmptyUnit()));
            return InGameScene.DeckUnits[index];
        }

        IReadOnlyList<UserItemModel> UpdateUserItemModels(
            IReadOnlyList<UserItemModel> currentModels,
            IReadOnlyList<UserItemModel> updatedModels)
        {
            // updatedModelsは更新されたものだけなので、
            // currentModelsからいったんupdateModelsに含まれるものを除いて、
            // currentModelsとupdateModelsを結合する
            return currentModels
                .Where(currentItem => updatedModels.All(updatedItem => updatedItem.MstItemId != currentItem.MstItemId))
                .Concat(updatedModels)
                .ToList();
        }

        async UniTask<VictoryResultModel> VictoryInTutorial(
            CancellationToken cancellationToken,
            TutorialStatusModel tutorialStatus,
            MasterDataId mstStageId)
        {
            var mstTutorialModels = MstTutorialRepository.GetMstTutorialModels();
            var currentTutorial = mstTutorialModels
                .FirstOrDefault(x => x.TutorialFunctionName == tutorialStatus.TutorialFunctionName, MstTutorialModel.Empty);

            var nextTutorialModel = mstTutorialModels
                .Where(x => x.TutorialType != TutorialType.Free)
                .MinByAboveLowerLimit(x => x.SortOrder.Value, currentTutorial.SortOrder.Value) ?? MstTutorialModel.Empty;

            // 次のチュートリアルが存在しない場合は例外を投げる
            if(nextTutorialModel.IsEmpty()) throw new Exception("次のTutorialStatusが存在しません");

            var newtTutorialStatus = new TutorialStatusModel(nextTutorialModel.TutorialFunctionName);

            var result = await TutorialService.EndTutorialStage(cancellationToken, newtTutorialStatus.TutorialFunctionName);

            // チュートリアルステージクリア情報
            var fetchResultModel = await GameService.Fetch(cancellationToken);

            // ユーザー情報の更新
            var prevGameFetchModel = GameRepository.GetGameFetch();

            var gameFetchOther = GameRepository.GetGameFetchOther();
            var newGameFetchOther = gameFetchOther with
            {
                UserUnitModels = gameFetchOther.UserUnitModels.Update(result.UserUnitModels),
                UserItemModels = gameFetchOther.UserItemModels.Update(result.UserItemModels)
            };

            GameManagement.SaveGameUpdateAndFetch(fetchResultModel.FetchModel, newGameFetchOther);

            TutorialStatusApplier.UpdateTutorialStatus(result.TutorialStatusModel);

            return TutorialVictoryResultModelFactory.CreateTutorialVictoryResultModel(
                result,
                prevGameFetchModel.UserParameterModel,
                mstStageId);
        }
    }
}
