using System.Collections.Generic;
using System.Linq;
using System.Threading;
using Cysharp.Threading.Tasks;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ModelFactories;
using GLOW.Core.Domain.Models.Mission;
using GLOW.Core.Domain.Repositories;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Modules.CommonReceiveView.Domain.Model;
using GLOW.Scenes.ArtworkPanelMission.Domain.Applier;
using GLOW.Scenes.ArtworkPanelMission.Domain.Factory;
using GLOW.Scenes.ArtworkPanelMission.Domain.Model;
using GLOW.Scenes.Mission.Domain.Calculator;
using GLOW.Scenes.Mission.Domain.Definition.Service;
using Zenject;

namespace GLOW.Scenes.ArtworkPanelMission.Domain.UseCase
{
    public class ReceiveArtworkPanelMissionRewardUseCase
    {
        [Inject] IMissionService MissionService { get; }
        [Inject] IPlayerResourceModelFactory PlayerResourceModelFactory { get; }
        [Inject] IUserLevelUpCacheRepository UserLevelUpCacheRepository { get; }
        [Inject] IGameRepository GameRepository { get; }
        [Inject] IMissionOfArtworkPanelRepository MissionOfArtworkPanelRepository { get; }
        [Inject] IArtworkPanelMissionResultModelFactory ArtworkPanelMissionResultModelFactory { get; }
        [Inject] IArtworkPanelMissionReceivedStatusApplier ArtworkPanelMissionReceivedStatusApplier { get; }
        [Inject] IArtworkPanelMissionReceivedRewardApplier ArtworkPanelMissionReceivedRewardApplier { get; }

        public async UniTask<ArtworkPanelMissionReceiveResultModel> ReceiveMissionReward(
            CancellationToken cancellationToken,
            MasterDataId mstMissionId,
            MasterDataId mstArtworkPanelMissionId,
            bool isBulkReceive)
        {
            var receiveMissionIds = GetReceiveMissionIds(mstMissionId, isBulkReceive);
            var receiveMissionRewardModel = await MissionService.BulkReceiveReward(
                cancellationToken,
                MissionType.LimitedTerm,
                receiveMissionIds);

            // 副作用：ミッションの受け取り状態を更新
            var userMissionLimitedTermModels = ArtworkPanelMissionReceivedStatusApplier
                .UpdateReceivedArtworkPanelMissions(receiveMissionRewardModel.MissionReceiveRewardModels);

            // 順番依存1: 受け取り結果更新後の情報でモデルを作成
            var artworkPanelMissionFetchResultModel = ArtworkPanelMissionResultModelFactory
                .CreateArtworkPanelMissionResultModel(userMissionLimitedTermModels, mstArtworkPanelMissionId);

            // 順番依存2: 受け取り結果更新後のモデルで受け取れるミッション数を計算
            var receivableMissionCount = ReceivableMissionCountCalculator.GetReceivableMissionOfArtworkPanelCount(
                artworkPanelMissionFetchResultModel);

            var prevUserParameterModel = GameRepository.GetGameFetch().UserParameterModel;
            var beforeGameFetchOther = GameRepository.GetGameFetchOther();
            var beforeUserArtworkModels = beforeGameFetchOther.UserArtworkModels;
            var beforeUserArtworkFragments = beforeGameFetchOther.UserArtworkFragmentModels;

            // 副作用: ゲーム情報の更新
            ArtworkPanelMissionReceivedRewardApplier.UpdateGameFetchModel(
                receiveMissionRewardModel.UserParameterModel,
                mstArtworkPanelMissionId,
                receivableMissionCount);

            // 副作用: その他モデルの更新
            ArtworkPanelMissionReceivedRewardApplier.UpdateGameFetchOtherModel(
                receiveMissionRewardModel);

            // 副作用: 経験値を受け取れる関係でレベルアップする可能性があるため
            UserLevelUpCacheRepository.Save(
                receiveMissionRewardModel.UserLevelUpModel,
                prevUserParameterModel.Level,
                prevUserParameterModel.Exp);

            var receivedRewards = CreateCommonReceiveModels(
                receiveMissionRewardModel.MissionRewardModels);

            MissionOfArtworkPanelRepository.SaveReceivedArtworkPanelInfo(
                receiveMissionRewardModel.UserArtworkModels,
                receiveMissionRewardModel.UserArtworkFragmentModels,
                beforeUserArtworkModels,
                beforeUserArtworkFragments);

            return new ArtworkPanelMissionReceiveResultModel(
                receivedRewards,
                artworkPanelMissionFetchResultModel);
        }

        IReadOnlyList<MasterDataId> GetReceiveMissionIds(
            MasterDataId mstMissionId,
            bool isBulkReceive)
        {
            if (isBulkReceive)
            {
                var userMissionModels = MissionOfArtworkPanelRepository.GetUserMissionLimitedTermModels();
                var receivableMissionIds = userMissionModels
                    .Where(model => model.IsCleared && !model.IsReceivedReward)
                    .Select(model => model.MstMissionLimitedTermId)
                    .ToList();
                return receivableMissionIds;
            }
            else
            {
                return new List<MasterDataId> { mstMissionId };
            }
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
