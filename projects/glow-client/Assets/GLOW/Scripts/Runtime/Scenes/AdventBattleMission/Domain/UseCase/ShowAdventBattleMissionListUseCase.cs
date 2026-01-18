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
using GLOW.Scenes.AdventBattleMission.Domain.Evaluator;
using GLOW.Scenes.AdventBattleMission.Domain.Model;
using GLOW.Scenes.Mission.Domain.Creator;
using GLOW.Scenes.Mission.Domain.Definition.Service;
using Zenject;

namespace GLOW.Scenes.AdventBattleMission.Domain.UseCase
{
    public class ShowAdventBattleMissionListUseCase
    {
        [Inject] IMissionService MissionService { get; }
        [Inject] IMstMissionDataRepository MissionDataRepository { get; }
        [Inject] IMissionOfAdventBattleRepository MissionOfAdventBattleRepository { get; }
        [Inject] IPlayerResourceModelFactory PlayerResourceModelFactory { get; }
        [Inject] ITimeProvider TimeProvider { get; }
        [Inject] IMissionResultModelFactory MissionResultModelFactory { get; }
        [Inject] IAdventBattleDateTimeEvaluator AdventBattleDateTimeEvaluator { get; }

        public async UniTask<AdventBattleMissionFetchResultModel> GetAdventBattleMissionList(CancellationToken cancellationToken)
        {
            var validAdventBattleModel = AdventBattleDateTimeEvaluator.GetOpenedAdventBattleModel();
            if(validAdventBattleModel.IsEmpty())
            {
                // 降臨バトルを開催していない場合はミッションを表示しない
                return AdventBattleMissionFetchResultModel.Empty;
            }
            
            var missionAdventBattleFetchResultModel = await MissionService.AdventBattleUpdateAndFetch(cancellationToken);
            
            var (userMissionEventModels, userMissionLimitedTermModels) 
                = CreateMissionAdventBattleModels(missionAdventBattleFetchResultModel);
            
            MissionOfAdventBattleRepository.SetUserMissionEventModels(userMissionEventModels);
            MissionOfAdventBattleRepository.SetUserMissionLimitedTermModels(userMissionLimitedTermModels);
            
            var resultModel = MissionResultModelFactory.CreateAdventBattleMissionResultModel(
                MissionDataRepository,
                PlayerResourceModelFactory,
                userMissionEventModels,
                userMissionLimitedTermModels,
                TimeProvider,
                validAdventBattleModel.EndDateTime);
            
            return resultModel;
        }

        (IReadOnlyList<UserMissionEventModel>, IReadOnlyList<UserMissionLimitedTermModel>) CreateMissionAdventBattleModels(
            MissionAdventBattleFetchResultModel missionAdventBattleFetchResultModel)
        {
            var mstAdventBattleMissionEvents = MissionDataRepository.GetMstMissionEventModels()
                .Where(model => model.EventCategory == EventCategory.AdventBattle);
            var mstAdventBattleLimitedTerms = MissionDataRepository.GetMstMissionLimitedTermModels()
                .Where(model => model.MissionCategory == MissionCategory.AdventBattle);
            
            var adventBattleMissionEvents = mstAdventBattleMissionEvents
                .GroupJoin(missionAdventBattleFetchResultModel.UserMissionEventModels,
                    mst => mst.Id,
                    user => user.MstMissionEventId,
                    (mst, users) => new {mst, user = users.FirstOrDefault() ?? UserMissionEventModel.Empty})
                .Select(mstAndUser => CreateUserMissionEventModel(mstAndUser.user, mstAndUser.mst.Id));
            
            var adventBattleMissionLimitedTerms = mstAdventBattleLimitedTerms
                .GroupJoin(missionAdventBattleFetchResultModel.UserMissionLimitedTermModels,
                    mst => mst.Id,
                    user => user.MstMissionLimitedTermId,
                    (mst, users) => new {mst, user = users.FirstOrDefault() ?? UserMissionLimitedTermModel.Empty})
                .Select(mstAndUser => CreateUserMissionLimitedTermModel(mstAndUser.user, mstAndUser.mst.Id));
            
            return (adventBattleMissionEvents.ToList(), adventBattleMissionLimitedTerms.ToList());
        }

        UserMissionEventModel CreateUserMissionEventModel(UserMissionEventModel userMissionEventModel, MasterDataId id)
        {
            if (userMissionEventModel.IsEmpty())
            {
                return userMissionEventModel with
                {
                    MstMissionEventId = id
                };
            }

            return userMissionEventModel;
        }

        UserMissionLimitedTermModel CreateUserMissionLimitedTermModel(
            UserMissionLimitedTermModel userMissionLimitedTermModel, MasterDataId id)
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
    }
}