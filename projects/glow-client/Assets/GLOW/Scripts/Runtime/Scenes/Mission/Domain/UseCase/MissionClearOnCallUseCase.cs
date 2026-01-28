using System.Collections.Generic;
using System.Linq;
using System.Threading;
using Cysharp.Threading.Tasks;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ModelFactories;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.Repositories;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Extensions;
using GLOW.Scenes.Mission.Domain.Creator;
using GLOW.Scenes.Mission.Domain.Definition.Service;
using GLOW.Scenes.Mission.Domain.Model;
using Zenject;

namespace GLOW.Scenes.Mission.Domain.UseCase
{
    public class MissionClearOnCallUseCase
    {
        [Inject] IMissionService MissionService { get; }
        [Inject] IMissionCacheRepository MissionCacheRepository { get; }
        [Inject] IMissionResultModelFactory MissionResultModelFactory { get; }
        [Inject] IMstMissionDataRepository MissionDataRepository { get; }
        [Inject] IPlayerResourceModelFactory PlayerResourceModelFactory { get; }
        [Inject] IGameRepository GameRepository { get; }
        
        public async UniTask<MissionFetchResultModel> ClearOnCall(
            CancellationToken cancellationToken,
            MissionType missionType,
            MasterDataId missionId)
        {
            var clearOnCallResultModel = await MissionService.ClearOnCall(cancellationToken, missionType, missionId);
            
            var missionModel = MissionCacheRepository.GetMissionModel();
            IReadOnlyList<UserMissionAchievementModel> userMissionAchievement = missionModel.UserMissionAchievementModels;
            IReadOnlyList<UserMissionBeginnerModel> userMissionBeginner = missionModel.UserMissionBeginnerModels;
            
            var updatedAchievementIds = clearOnCallResultModel.UserMissionAchievementModels
                .Select(model => model.MstMissionAchievementId)
                .ToHashSet();
            
            userMissionAchievement = userMissionAchievement
                .Where(model => !updatedAchievementIds.Contains(model.MstMissionAchievementId))
                .Concat(clearOnCallResultModel.UserMissionAchievementModels)
                .ToList();
            
            
            var updatedBeginnerIds = clearOnCallResultModel.UserMissionBeginnerModels
                .Select(model => model.MstMissionBeginnerId)
                .ToHashSet();
            
            userMissionBeginner = userMissionBeginner
                .Where(model => !updatedBeginnerIds.Contains(model.MstMissionBeginnerId))
                .Concat(clearOnCallResultModel.UserMissionBeginnerModels)
                .ToList();

            var updatedMissionModel = missionModel with
            {
                UserMissionAchievementModels = userMissionAchievement.ToList(),
                UserMissionBeginnerModels = userMissionBeginner.ToList()
            };
            
            MissionCacheRepository.SetMissionModel(updatedMissionModel);

            var gameFetchOtherModel = GameRepository.GetGameFetchOther();
            
            var missionAchievementResultModel = MissionResultModelFactory.CreateMissionAchievementResultModel(
                MissionDataRepository, 
                PlayerResourceModelFactory, 
                updatedMissionModel.UserMissionAchievementModels);
            
            var missionDailyBonusResultModel = MissionResultModelFactory.CreateMissionDailyBonusResultModel(
                MissionDataRepository, 
                PlayerResourceModelFactory, 
                updatedMissionModel.UserMissionDailyBonusModels, 
                gameFetchOtherModel.UserLoginInfoModel, 
                gameFetchOtherModel.MissionReceivedDailyBonusModel);
            
            var missionDailyResultModel = MissionResultModelFactory.CreateMissionDailyResultModel(
                MissionDataRepository, 
                PlayerResourceModelFactory,
                updatedMissionModel.UserMissionDailyModels, 
                updatedMissionModel.UserMissionBonusPointModels);
            
            var missionWeeklyResultModel = MissionResultModelFactory.CreateMissionWeeklyResultModel(
                MissionDataRepository, 
                PlayerResourceModelFactory,
                updatedMissionModel.UserMissionWeeklyModels, 
                updatedMissionModel.UserMissionBonusPointModels);
            
            var missionBeginnerResultModel = MissionResultModelFactory.CreateMissionBeginnerResultModel(
                MissionDataRepository, 
                PlayerResourceModelFactory, 
                updatedMissionModel.UserMissionBeginnerModels, 
                updatedMissionModel.UserMissionBonusPointModels);

            return new MissionFetchResultModel(
                missionAchievementResultModel, 
                missionDailyBonusResultModel, 
                missionDailyResultModel, 
                missionWeeklyResultModel, 
                missionBeginnerResultModel, 
                missionModel.BeginnerMissionDaysFromStart);
        }
    }
}