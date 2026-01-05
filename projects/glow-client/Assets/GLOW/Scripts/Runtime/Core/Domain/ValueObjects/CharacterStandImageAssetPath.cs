using Cysharp.Text;

namespace GLOW.Core.Domain.ValueObjects
{
    public record CharacterStandImageAssetPath(string Value)
    {
        public static CharacterStandImageAssetPath FromAssetKey(UnitAssetKey assetKey)
        {
            return new CharacterStandImageAssetPath(ZString.Format("unit_stand_image_{0}", assetKey.Value));
        }
    }
}
