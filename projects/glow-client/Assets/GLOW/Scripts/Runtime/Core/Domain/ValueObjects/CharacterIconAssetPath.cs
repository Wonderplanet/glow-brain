using System;
using Cysharp.Text;

namespace GLOW.Core.Domain.ValueObjects
{
    //あとでAssetKeyに変える
    public record CharacterIconAssetPath(string Value, string AssetKey)
    {
        const string AssetPathFormat = "unit_icon_{0}";
        const string AssetPathFormatL = "unit_icon_{0}_l";

        public static CharacterIconAssetPath Empty { get; } = new (string.Empty, string.Empty);

        public static CharacterIconAssetPath FromAssetKey(UnitAssetKey assetKey)
        {
            return new CharacterIconAssetPath(ZString.Format(AssetPathFormat, assetKey.Value),assetKey.Value);
        }

        public static CharacterIconAssetPath FromAssetKeyForLSize(UnitAssetKey assetKey)
        {
            return new CharacterIconAssetPath(ZString.Format(AssetPathFormatL, assetKey.Value),assetKey.Value);
        }

        public static CharacterIconAssetPath FromAssetKey(PlayerResourceAssetKey assetKey)
        {
            return new CharacterIconAssetPath(ZString.Format(AssetPathFormat, assetKey.Value),assetKey.Value);
        }

        public PlayerResourceIconAssetPath ToPlayerResourceIconAssetPath()
        {
            return new PlayerResourceIconAssetPath(Value);
        }
        public string ToAssetPath()
        {
            return ZString.Format(AssetPathFormat, AssetKey);
        }
        public string ToLongIconAssetPath()
        {
            return ZString.Format(AssetPathFormatL, AssetKey);
        }
        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
    }

    public record CharacterSpecialAttackIconAssetPath(string Value, string AssetKey)
    {
        const string AssetPathFormat = "unit_icon_sp_{0}";
        const string AssetPathFormatL = "unit_icon_sp_{0}_l";
        public static CharacterSpecialAttackIconAssetPath Empty { get; } = new (string.Empty, string.Empty);

        public static CharacterSpecialAttackIconAssetPath FromAssetKey(UnitAssetKey assetKey)
        {
            return new CharacterSpecialAttackIconAssetPath(ZString.Format(AssetPathFormat, assetKey.Value), assetKey.Value);
        }
        public static CharacterSpecialAttackIconAssetPath FromAssetKeyForLSize(UnitAssetKey assetKey)
        {
            return new CharacterSpecialAttackIconAssetPath(ZString.Format(AssetPathFormatL, assetKey.Value), assetKey.Value);
        }

        public string ToAssetPath()
        {
            return ZString.Format(AssetPathFormat, AssetKey);
        }
        public string ToLongIconAssetPath()
        {
            return ZString.Format(AssetPathFormatL, AssetKey);
        }
        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
    }

}
