using GLOW.Core.Domain.ValueObjects;

namespace GLOW.Core.Domain.Models
{
    public record MstConfigModel(MstConfigKey Key, MstConfigValue Value)
    {
        public static MstConfigModel Empty { get; } = new MstConfigModel(MstConfigKey.Empty, MstConfigValue.Empty);

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }

        public UnitLevel ToUnitLevel()
        {
            return new UnitLevel(Value.ToInt());
        }
    }
}
