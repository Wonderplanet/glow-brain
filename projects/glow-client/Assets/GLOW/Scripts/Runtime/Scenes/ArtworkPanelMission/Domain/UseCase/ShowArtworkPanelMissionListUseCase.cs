using System.Collections.Generic;
using System.Linq;
using System.Threading;
using Cysharp.Threading.Tasks;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.Extensions;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.Models.Mission;
using GLOW.Core.Domain.Repositories;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Scenes.ArtworkPanelMission.Domain.Factory;
using GLOW.Scenes.ArtworkPanelMission.Domain.Model;
using GLOW.Scenes.InGame.Domain.Models;
using GLOW.Scenes.Mission.Domain.Definition.Service;
using Zenject;

namespace GLOW.Scenes.ArtworkPanelMission.Domain.UseCase
{
    public class ShowArtworkPanelMissionListUseCase
    {
        [Inject] IMissionService MissionService { get; }
        [Inject] IMstMissionDataRepository MissionDataRepository { get; }
        [Inject] IMissionOfArtworkPanelRepository MissionOfArtworkPanelRepository { get; }
        [Inject] IArtworkPanelMissionResultModelFactory ArtworkPanelMissionResultModelFactory { get; }
        [Inject] IGameRepository GameRepository { get; }
        [Inject] IGameManagement GameManagement { get; }
        [Inject] IArtworkPanelMissionInfoModelFactory ArtworkPanelMissionInfoModelFactory { get; }
        
        public async UniTask<ArtworkPanelMissionModel> FetchAndUpdateArtworkPanelMissionModel(
            MasterDataId mstEventId,
            CancellationToken cancellationToken)
        {
            var result = await MissionService.ArtworkPanelUpdateAndFetch(cancellationToken);
            
            // 順番依存1 副作用 : 原画パネル関連の情報を更新
            UpdateFetchOther(result.UserArtworkFragmentModels);
            
            // 順番依存2 更新後のデータを取得する
            var updatedGameFetchOther = GameRepository.GetGameFetchOther();
            
            // 順番依存3 更新後のデータを使ってモデルを作成する
            var infoModel = CreateArtworkPanelMissionInfo(
                mstEventId, 
                updatedGameFetchOther.UserArtworkModels, 
                updatedGameFetchOther.UserArtworkFragmentModels);
            
            var userMissionLimitedTermModels = CreateMissionArtworkPanelModels(result);
            
            // 副作用 : ミッションのユーザーデータを保存
            MissionOfArtworkPanelRepository.SetUserMissionLimitedTermModels(userMissionLimitedTermModels);
            
            var artworkPanelMissionFetchResultModel = ArtworkPanelMissionResultModelFactory
                .CreateArtworkPanelMissionResultModel(userMissionLimitedTermModels, infoModel.MstArtworkPanelMissionId);
            
            var model = new ArtworkPanelMissionModel(
                infoModel.MstArtworkPanelMissionId,
                infoModel.MstEventId,
                infoModel.ArtworkPanelModel,
                infoModel.RemainingTimeSpan,
                artworkPanelMissionFetchResultModel);
            
            return model;
        }

        ArtworkPanelMissionInfoModel CreateArtworkPanelMissionInfo(
            MasterDataId mstEventId,
            IReadOnlyList<UserArtworkModel> userArtworkModels,
            IReadOnlyList<UserArtworkFragmentModel> userArtworkFragmentModels)
        {
            if (mstEventId.IsEmpty())
            {
                return ArtworkPanelMissionInfoModelFactory.CreateByLatestMstEventId(
                    userArtworkModels, 
                    userArtworkFragmentModels);
            }
            else
            {
                return ArtworkPanelMissionInfoModelFactory.CreateBySelectedMstEventId(
                    mstEventId, 
                    userArtworkModels, 
                    userArtworkFragmentModels);
            }
        }
        
        IReadOnlyList<UserMissionLimitedTermModel> CreateMissionArtworkPanelModels(
            MissionArtworkPanelUpdateAndFetchResultModel resultModel)
        {
            var mstArtworkPanelMissionLimitedTerms = MissionDataRepository
                .GetMstMissionLimitedTermModels()
                .Where(model => model.MissionCategory == MissionCategory.ArtworkPanel);
            
            var artworkPanelMissionLimitedTerms = mstArtworkPanelMissionLimitedTerms
                .GroupJoin(resultModel.UserMissionLimitedTermModels,
                    mst => mst.Id,
                    user => user.MstMissionLimitedTermId,
                    (mst, users) => new {mst, user = users.FirstOrDefault() ?? UserMissionLimitedTermModel.Empty})
                .Select(mstAndUser => CreateUserMissionLimitedTermModel(mstAndUser.user, mstAndUser.mst.Id));
            
            return artworkPanelMissionLimitedTerms.ToList();
        }
        
        UserMissionLimitedTermModel CreateUserMissionLimitedTermModel(
            UserMissionLimitedTermModel userMissionLimitedTermModel, 
            MasterDataId id)
        {
            if (userMissionLimitedTermModel.IsEmpty())
            {
                return userMissionLimitedTermModel with
                {
                    MstMissionLimitedTermId = id
                };
            }

            return userMissionLimitedTermModel;
        }

        void UpdateFetchOther(
            IReadOnlyList<UserArtworkFragmentModel> userArtworkFragmentModels)
        {
            var previousGameFetchOtherModel = GameRepository.GetGameFetchOther();
            var updatedGameFetchOtherModel = CreateUpdatedGameFetchOther(
                previousGameFetchOtherModel, 
                userArtworkFragmentModels);
            GameManagement.SaveGameFetchOther(updatedGameFetchOtherModel);
        }
        
        GameFetchOtherModel CreateUpdatedGameFetchOther(
            GameFetchOtherModel gameFetchOtherModel,
            IReadOnlyList<UserArtworkFragmentModel> userArtworkFragmentModels)
        {
            var updatedGameFetchOtherModel = gameFetchOtherModel with
            {
                UserArtworkFragmentModels = gameFetchOtherModel.UserArtworkFragmentModels.Update(userArtworkFragmentModels),
            };

            return updatedGameFetchOtherModel;
        }
    }
}