using Cysharp.Text;

namespace GLOW.Core.Domain.ValueObjects
{
    public record UnitImageGetAttackAssetPath(string Value)
    {
        const string AttackAssetPathFormat = "unit_image_get_{0}_02";
      
        public static UnitImageGetAttackAssetPath Empty { get; } = new UnitImageGetAttackAssetPath(string.Empty);
        
        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
        
        public static UnitImageGetAttackAssetPath FromAssetKey(UnitAssetKey assetKey)
        {
            return new UnitImageGetAttackAssetPath(ZString.Format(AttackAssetPathFormat, assetKey.Value));
        }
	}
}