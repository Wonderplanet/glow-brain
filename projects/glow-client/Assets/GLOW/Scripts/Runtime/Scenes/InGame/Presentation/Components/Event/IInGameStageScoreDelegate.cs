using GLOW.Scenes.InGame.Domain.ValueObjects;

namespace GLOW.Scenes.InGame.Presentation.Components
{
    public interface IInGameStageScoreDelegate
    {
        void UpdateScore(InGameScore score);
    }
}
