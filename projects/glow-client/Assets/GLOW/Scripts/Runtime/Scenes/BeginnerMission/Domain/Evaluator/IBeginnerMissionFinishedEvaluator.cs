using GLOW.Scenes.Home.Domain.Models;

namespace GLOW.Scenes.BeginnerMission.Domain.UseCase
{
    public interface IBeginnerMissionFinishedEvaluator
    {
        BeginnerMissionFinishedFlag CheckBeginnerMissionAllCompleted();
    }
}