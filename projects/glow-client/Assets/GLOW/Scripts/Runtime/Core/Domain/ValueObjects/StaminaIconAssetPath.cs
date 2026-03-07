using Cysharp.Text;
using WondlerPlanet.CheatProtectKit.ObscuredTypes;

namespace GLOW.Core.Domain.ValueObjects
{
    public record StaminaIconAssetPath(ObscuredString Value)
    {
        const string AssetPathFormat = "player_resource_icon_{0}";

        public static StaminaIconAssetPath FromAssetKey(StaminaAssetKey assetKey)
        {
            return new StaminaIconAssetPath(ZString.Format(AssetPathFormat, assetKey.Value));
        }

        public static StaminaIconAssetPath FromAssetKey(PlayerResourceAssetKey assetKey)
        {
            return new StaminaIconAssetPath(ZString.Format(AssetPathFormat, assetKey.Value));
        }

        public PlayerResourceIconAssetPath ToPlayerResourceIconAssetPath()
        {
            return new PlayerResourceIconAssetPath(Value);
        }
    }
}