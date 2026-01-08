using GLOW.Scenes.InGame.Domain.ValueObjects;

namespace GLOW.Scenes.InGame.Presentation.Components
{
    public interface IInGameDefeatTargetEnemyDelegate
    {
        void UpdateRemainingEnemyCount(DefeatEnemyCount defeatCount, DefeatEnemyCount endCondition);
    }
}
