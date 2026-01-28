using Cysharp.Text;
using WondlerPlanet.CheatProtectKit.ObscuredTypes;

namespace GLOW.Core.Domain.ValueObjects.Pass
{
    public record PassIconAssetPath(ObscuredString Value)
    {
        const string AssetPathFormat = "shop_pass_icon_{0}";
        public static PassIconAssetPath Empty { get; } = new (string.Empty);

        public static PassIconAssetPath FromAssetKey(PassAssetKey passAssetKey)
        {
            return new PassIconAssetPath(ZString.Format(AssetPathFormat, passAssetKey.ToString()));
        }
        
        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
    }
}