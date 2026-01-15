using System.Collections.Generic;
using System.Linq;
using System.Threading;
using Cysharp.Threading.Tasks;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ModelFactories;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.Models.Mission;
using GLOW.Core.Domain.Repositories;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.Mission;
using GLOW.Modules.CommonReceiveView.Domain.Model;
using GLOW.Scenes.AdventBattleMission.Domain.Applier;
using GLOW.Scenes.AdventBattleMission.Domain.Evaluator;
using GLOW.Scenes.AdventBattleMission.Domain.Model;
using GLOW.Scenes.Mission.Domain.Calculator;
using GLOW.Scenes.Mission.Domain.Creator;
using GLOW.Scenes.Mission.Domain.Definition.Service;
using GLOW.Scenes.Mission.Domain.Model;
using Zenject;

namespace GLOW.Scenes.AdventBattleMission.Domain.UseCase
{
    public class BulkReceiveAdventBattleMissionRewardUseCase
    {
        [Inject] IMissionService MissionService { get; }
        [Inject] IMstMissionDataRepository MissionDataRepository { get; }
        [Inject] IPlayerResourceModelFactory PlayerResourceModelFactory { get; }
        [Inject] IMissionResultModelFactory MissionResultModelFactory { get; }
        [Inject] ITimeProvider TimeProvider { get; }
        [Inject] IAdventBattleMissionReceivedRewardApplier AdventBattleMissionReceivedRewardApplier { get; }
        [Inject] IAdventBattleMissionReceivedStatusApplier AdventBattleMissionReceivedStatusApplier { get; }
        [Inject] IAdventBattleDateTimeEvaluator AdventBattleDateTimeEvaluator { get; }
        [Inject] IUserLevelUpCacheRepository UserLevelUpCacheRepository { get; }
        [Inject] IGameRepository GameRepository { get; }
        [Inject] IMissionOfAdventBattleRepository MissionOfAdventBattleRepository { get; }

        public async UniTask<AdventBattleMissionReceiveResultModel> BulkReceiveMissionReward(
            CancellationToken cancellationToken,
            MissionType missionType)
        {
            var validAdventBattleModel = AdventBattleDateTimeEvaluator.GetOpenedAdventBattleModel();
            if (validAdventBattleModel.IsEmpty())
            {
                // 降臨バトルを開催期間外の場合は受け取れないようにする
                return AdventBattleMissionReceiveResultModel.Empty;
            }

            var userMissionEventModels = MissionOfAdventBattleRepository.GetUserMissionEventModels();
            var userMissionEventIds = userMissionEventModels
                .Where(model => model.IsCleared && !model.IsReceivedReward)
                .Select(model => model.MstMissionEventId)
                .ToList();

            var userMissionLimitedTermModels = MissionOfAdventBattleRepository.GetUserMissionLimitedTermModels();
            var userMissionLimitedTermIds = userMissionLimitedTermModels
                .Where(model => model.IsCleared && !model.IsReceivedReward)
                .Select(model => model.MstMissionLimitedTermId)
                .ToList();

            var receiveMissionIds = new List<MasterDataId>();
            receiveMissionIds.AddRange(userMissionEventIds);
            receiveMissionIds.AddRange(userMissionLimitedTermIds);

            var receiveMissionRewardModel = await MissionService.BulkReceiveReward(
                cancellationToken,
                missionType,
                receiveMissionIds);

            // ミッションの受け取り状態を更新
            var adventBattleMissionAppliedModel = AdventBattleMissionReceivedStatusApplier
                .UpdateReceivedAdventBattleMissions(receiveMissionRewardModel.MissionReceiveRewardModels);

            var resultModel = MissionResultModelFactory.CreateAdventBattleMissionResultModel(
                MissionDataRepository,
                PlayerResourceModelFactory,
                adventBattleMissionAppliedModel.UserMissionEventModels,
                adventBattleMissionAppliedModel.UserMissionLimitedTermModels,
                TimeProvider,
                validAdventBattleModel.EndDateTime);

            var receivableMissionCount =
                ReceivableMissionCountCalculator.GetReceivableMissionOfAdventBattleCount(resultModel);

            var prevUserParameterModel = GameRepository.GetGameFetch().UserParameterModel;

            AdventBattleMissionReceivedRewardApplier.UpdateGameFetchModel(
                receiveMissionRewardModel.UserParameterModel,
                receivableMissionCount);

            AdventBattleMissionReceivedRewardApplier.UpdateGameFetchOtherModel(
                receiveMissionRewardModel);

            // 経験値を受け取れる関係でレベルアップする可能性があるため
            UserLevelUpCacheRepository.Save(
                receiveMissionRewardModel.UserLevelUpModel,
                prevUserParameterModel.Level,
                prevUserParameterModel.Exp);

            return new AdventBattleMissionReceiveResultModel(
                CreateCommonReceiveModels(receiveMissionRewardModel.MissionRewardModels),
                resultModel);
        }

        IReadOnlyList<CommonReceiveResourceModel> CreateCommonReceiveModels(IReadOnlyList<MissionRewardModel> models)
        {
            return models.Select(r =>
                    new CommonReceiveResourceModel(
                        r.RewardModel.UnreceivedRewardReasonType,
                        PlayerResourceModelFactory.Create(
                            r.RewardModel.ResourceType,
                            r.RewardModel.ResourceId,
                            r.RewardModel.Amount),
                        PlayerResourceModelFactory.Create(r.RewardModel.PreConversionResource))
                )
                .ToList();
        }
    }
}
