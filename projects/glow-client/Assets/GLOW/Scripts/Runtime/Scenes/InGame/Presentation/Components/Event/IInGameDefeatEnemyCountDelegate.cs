using GLOW.Scenes.InGame.Domain.ValueObjects;

namespace GLOW.Scenes.InGame.Presentation.Components
{
    public interface IInGameDefeatEnemyCountDelegate
    {
        void UpdateDefeatEnemyCount(DefeatEnemyCount defeatedCount, DefeatEnemyCount endCondition);
    }
}
