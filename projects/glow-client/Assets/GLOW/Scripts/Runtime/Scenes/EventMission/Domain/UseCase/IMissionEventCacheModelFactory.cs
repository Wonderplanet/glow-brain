using GLOW.Core.Domain.Models.Mission;

namespace GLOW.Scenes.EventMission.Domain.UseCase
{
    public interface IMissionEventCacheModelFactory
    {
        MissionEventCacheModel Create(MissionEventUpdateAndFetchResultModel missionUpdateAndFetchResultModel);
    }
}