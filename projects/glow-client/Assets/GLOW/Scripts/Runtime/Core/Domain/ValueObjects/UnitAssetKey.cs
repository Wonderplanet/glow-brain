using GLOW.Debugs.InGame.Domain.ValueObjects;
using WondlerPlanet.CheatProtectKit.ObscuredTypes;

namespace GLOW.Core.Domain.ValueObjects
{
    public record UnitAssetKey(ObscuredString Value)
    {
        public static UnitAssetKey Empty { get; } = new UnitAssetKey(string.Empty);

        public PlayerResourceAssetKey ToPlayerResourceAssetKey()
        {
            return new PlayerResourceAssetKey(Value);
        }

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
        
#if GLOW_INGAME_DEBUG
        public DamageDebugLogTargetName ToDamageDebugLogTargetName()
        {
            return new DamageDebugLogTargetName(Value);
        }
#endif
    }
}
