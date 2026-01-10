using GLOW.Scenes.InGame.Domain.Models;

namespace GLOW.Scenes.InGame.Presentation.Components
{
    public interface IInGameStageTimeDelegate
    {
        void UpdateTimeLimit(StageTimeModel model);
    }
}
