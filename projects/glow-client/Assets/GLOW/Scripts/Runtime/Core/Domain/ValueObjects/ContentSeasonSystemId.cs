using System;
using WondlerPlanet.CheatProtectKit.ObscuredTypes;

namespace GLOW.Core.Domain.ValueObjects
{
    public record ContentSeasonSystemId(ObscuredString Value) : IComparable<ContentSeasonSystemId>
    {
        public static ContentSeasonSystemId Empty { get; } = new (string.Empty);
        
        public static bool operator ==(MasterDataId a, ContentSeasonSystemId b)
        {
            return a.Value == b.Value;
        }

        public static bool operator !=(MasterDataId a, ContentSeasonSystemId b)
        {
            return !(a.Value == b.Value);
        }
        
        public static bool operator ==(ContentSeasonSystemId a, MasterDataId b)
        {
            return a.Value == b.Value;
        }

        public static bool operator !=(ContentSeasonSystemId a, MasterDataId b)
        {
            return !(a.Value == b.Value);
        }

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
        
        public int CompareTo(ContentSeasonSystemId id)
        {
            return string.Compare(Value, id.Value, StringComparison.Ordinal);
        }

        public override string ToString()
        {
            return Value;
        }
        
        public MasterDataId ToMasterDataId()
        {
            return new MasterDataId(Value);
        }
    }
}