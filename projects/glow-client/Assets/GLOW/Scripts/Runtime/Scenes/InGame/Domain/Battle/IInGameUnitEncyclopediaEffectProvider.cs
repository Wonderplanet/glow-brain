using GLOW.Core.Domain.ValueObjects;

namespace GLOW.Scenes.InGame.Domain.Battle
{
    public static class InGameUnitEncyclopediaBindIds
    {
        public const string Player = "UnitEncyclopediaEffect_Player";
        public const string PvpOpponent = "UnitEncyclopediaEffect_PvpOpponent";
    }

    public interface IInGameUnitEncyclopediaEffectProvider
    {
        PercentageM GetHpEffectPercentage();
        PercentageM GetAttackPowerEffectPercentage();
        PercentageM GetHealEffectPercentage();
    }
}
