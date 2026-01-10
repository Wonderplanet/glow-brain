using GLOW.Scenes.InGame.Domain.Constants;

namespace GLOW.Scenes.InGame.Domain.Battle.InGameInitializers
{
    public interface IBattleSpeedInitializer
    {
        BattleSpeedInitializationResult Initialize(BattleSpeed preferenceBattleSpeed);
    }
}