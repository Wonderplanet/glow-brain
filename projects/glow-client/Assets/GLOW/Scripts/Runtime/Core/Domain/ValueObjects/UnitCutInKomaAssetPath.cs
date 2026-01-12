using Cysharp.Text;
using WondlerPlanet.CheatProtectKit.ObscuredTypes;

namespace GLOW.Core.Domain.ValueObjects
{
    public record UnitCutInKomaAssetPath(ObscuredString Value)
    {
        const string AssetPathFormat = "unit_cutin_koma_{0}";

        public static UnitCutInKomaAssetPath Empty { get; } = new UnitCutInKomaAssetPath(string.Empty);
        
        public static UnitCutInKomaAssetPath FromAssetKey(UnitAssetKey assetKey)
        {
            return new UnitCutInKomaAssetPath(ZString.Format(AssetPathFormat, assetKey.Value));
        }

        public override string ToString()
        {
            return Value.ToString();
        }

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
    }
}