using GLOW.Core.Domain.Models;
using GLOW.Scenes.Home.Domain.Models;

namespace GLOW.Scenes.EventQuestTop.Domain.UseCases
{
    public interface ISpeedAttackUseCaseModelFactory
    {
        SpeedAttackUseCaseModel Create(UserStageEventModel targetUserStageEventModel);
    }
}