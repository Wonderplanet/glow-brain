using GLOW.Core.Domain.ValueObjects.InGame;
using WondlerPlanet.CheatProtectKit.ObscuredTypes;

namespace GLOW.Core.Domain.ValueObjects
{
    public record AdventBattleAssetKey(ObscuredString Value)
    {
        public static AdventBattleAssetKey Empty { get; } = new(string.Empty);

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
        
        public InGameAssetKey ToInGameAssetKey()
        {
            return new InGameAssetKey(Value);
        }

        public KomaBackgroundAssetKey ToKomaBackgroundAssetKey()
        {
            return new KomaBackgroundAssetKey(Value);
        }
    }
}