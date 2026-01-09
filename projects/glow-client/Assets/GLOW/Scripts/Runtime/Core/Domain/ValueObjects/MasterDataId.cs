using System;
using WondlerPlanet.CheatProtectKit.ObscuredTypes;

namespace GLOW.Core.Domain.ValueObjects
{
    public record MasterDataId(ObscuredString Value) : IComparable<MasterDataId>
    {
        public static MasterDataId Empty { get; } = new MasterDataId(string.Empty);

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
        
        public int CompareTo(MasterDataId id)
        {
            return string.Compare(Value, id.Value, StringComparison.Ordinal);
        }

        public override string ToString()
        {
            return Value;
        }
    }
}
