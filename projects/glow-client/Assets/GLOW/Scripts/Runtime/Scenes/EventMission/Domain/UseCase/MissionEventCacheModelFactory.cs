using System.Collections.Generic;
using System.Linq;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.Models.Mission;
using GLOW.Core.Domain.Repositories;
using GLOW.Core.Domain.ValueObjects;
using Zenject;

namespace GLOW.Scenes.EventMission.Domain.UseCase
{
    public class MissionEventCacheModelFactory : IMissionEventCacheModelFactory
    {
        [Inject] IMstMissionDataRepository MissionDataRepository { get; }

        MissionEventCacheModel IMissionEventCacheModelFactory.Create(
            MissionEventUpdateAndFetchResultModel missionUpdateAndFetchResultModel)
        {
            var mstMissionEventModels = MissionDataRepository.GetMstMissionEventModels();
            
            var eventMissionDictionary = mstMissionEventModels
                .GroupBy(model => model.MstEventId)
                .ToDictionary(
                    group => group.Key,
                    group =>
                    {
                        var mstEventId = group.Key;
                        
                        var eventAchievementIds = mstMissionEventModels
                            .Where(model => model.MstEventId == mstEventId)
                            .Select(model => model.Id)
                            .ToList();
                        
                        var userMissionEventModels = missionUpdateAndFetchResultModel.MissionEventModels
                            .FirstOrDefault(model => model.MstEventId == mstEventId)?
                            .UserMissionEventModels ?? new List<UserMissionEventModel>();

                        var userEventAchievementModels = CreateUserMissionEventModels(
                                eventAchievementIds, 
                                userMissionEventModels)
                            .ToList();
                        
                        return new MissionEventModel(
                            mstEventId,
                            userEventAchievementModels);
                    });

            return new MissionEventCacheModel(eventMissionDictionary);
        }
        
        IReadOnlyList<UserMissionEventModel> CreateUserMissionEventModels(
            List<MasterDataId> mstMissionEventIds,
            List<UserMissionEventModel> userMissionEventModels)
        {
            return mstMissionEventIds.Select(id =>
            {
                var userModel = userMissionEventModels?.Find(user => user.MstMissionEventId == id);
                return userModel ?? UserMissionEventModel.EmptyWithId(id);
            })
            .ToList();
        }
    }
}
