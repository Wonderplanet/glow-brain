using System.Collections.Generic;
using System.Threading;
using Cysharp.Threading.Tasks;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.Extensions;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.Models.Pvp;
using GLOW.Core.Domain.Modules.LocalNotification;
using GLOW.Core.Domain.Repositories;
using GLOW.Core.Domain.Services;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Scenes.AdventBattle.Domain.Factory;
using GLOW.Scenes.AdventBattleResult.Domain.Model;
using GLOW.Scenes.ArtworkFragmentAcquisition.Domain.Models;
using GLOW.Scenes.BattleResult.Domain.Evaluator;
using GLOW.Scenes.BattleResult.Domain.Models;
using GLOW.Scenes.InGame.Domain.Models;
using GLOW.Scenes.InGame.Domain.ValueObjects;
using Zenject;

namespace GLOW.Scenes.BattleResult.Domain.Factory
{
    public class PvpVictoryResultModelFactory : IPvpVictoryResultModelFactory
    {
        [Inject] IGameService GameService { get; }
        [Inject] IPvpService PvpService { get; }
        [Inject] IPvpInGameEndBattleLogModelFactory PvpInGameEndBattleLogModelFactory { get; }
        [Inject] IInGameScene InGameScene { get; }
        [Inject] IGameManagement GameManagement { get; }
        [Inject] IGameRepository GameRepository { get; }
        [Inject] IPvpResultPointModelFactory PvpResultPointModelFactory { get; }
        [Inject] ILocalNotificationScheduler LocalNotificationScheduler { get; }
        [Inject] IPvpReceivedRewardRepository PvpReceivedRewardRepository { get; }

        async UniTask<VictoryResultModel> IPvpVictoryResultModelFactory.CreateVictoryPvpResultModel(
            CancellationToken cancellationToken,
            PvpResultEvaluator.PvpResultType resultType)
        {
            var prevGameFetchOtherModel = GameRepository.GetGameFetchOther();
            var isWin = resultType == PvpResultEvaluator.PvpResultType.Victory;
            var sysPvpModel = prevGameFetchOtherModel.SysPvpSeasonModel;
            var pvpInGameEndBattleLogModel = PvpInGameEndBattleLogModelFactory.CreateInGameEndBattleLogModel(
                prevGameFetchOtherModel.UserUnitModels,
                InGameScene.StageTimeModel.CurrentTickCount.ToStageClearTime(),
                InGameScene.SpecialRuleUnitStatusModels);

            var fetchResultModel = await GameService.Fetch(cancellationToken);
            var pvpEndResultModel = await PvpService.End(
                cancellationToken,
                sysPvpModel.Id,
                pvpInGameEndBattleLogModel,
                isWin);

            // ランクマッチトップ画面用に報酬を保存
            SavePvpReceivedRewards(pvpEndResultModel.RewardModels);

            SaveGameUpdateAndFetch(fetchResultModel.FetchModel, prevGameFetchOtherModel, pvpEndResultModel);

            // ランクマッチの通知の更新
            LocalNotificationScheduler.RefreshRemainPvPSchedule();

            // 中断復帰するとUserPvpStatusが取得できないのでafterからresultを引いたものにする
            var afterPoint = pvpEndResultModel.UsrPvpStatus.Score;
            var bonusPointModel = pvpEndResultModel.BonusPointModel;
            var beforePoint = afterPoint - bonusPointModel.AllBonusPoint;

            var pvpResultScoreModel = PvpResultPointModelFactory.CreatePvpResultPointModel(
                beforePoint,
                pvpEndResultModel.UsrPvpStatus.Score,
                pvpEndResultModel.BonusPointModel);

            return new VictoryResultModel(
                UnitAssetKey.Empty,
                new List<UserExpGainModel>(),
                UserLevelUpEffectModel.Empty,
                new List<PlayerResourceModel>(),
                new List<IReadOnlyList<PlayerResourceModel>>(),
                new List<UnreceivedRewardReasonType>() { UnreceivedRewardReasonType.None },
                new List<ArtworkFragmentAcquisitionModel>(),
                ResultScoreModel.Empty,
                ResultSpeedAttackModel.Empty,
                AdventBattleResultScoreModel.Empty,
                pvpResultScoreModel,
                InGameType.Pvp,
                RemainingTimeSpan.Empty,
                InGameRetryModel.Empty);
        }

        void SavePvpReceivedRewards(IReadOnlyList<PvpRewardModel> models)
        {
            PvpReceivedRewardRepository.SavePvpReceivedRewards(models);
        }

        void SaveGameUpdateAndFetch(
            GameFetchModel fetchModel,
            GameFetchOtherModel prevGameFetchOtherModel,
            PvpEndResultModel pvpEndResultModel)
        {
            var updatedGameFetchModel = fetchModel with
            {
                UserParameterModel = pvpEndResultModel.ParameterModel
            };

            var updatedGameFetchOtherModel = prevGameFetchOtherModel with
            {
                UserItemModels = prevGameFetchOtherModel.UserItemModels.Update(pvpEndResultModel.UsrItems),
                UserPvpStatusModel = pvpEndResultModel.UsrPvpStatus,
                UserEmblemModel = prevGameFetchOtherModel.UserEmblemModel.Update(pvpEndResultModel.UsrEmblems),
            };

            GameManagement.SaveGameUpdateAndFetch(updatedGameFetchModel, updatedGameFetchOtherModel);
        }
    }
}
