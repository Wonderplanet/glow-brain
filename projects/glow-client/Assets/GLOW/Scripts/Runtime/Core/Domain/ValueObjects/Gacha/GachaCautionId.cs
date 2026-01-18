using System;
using WondlerPlanet.CheatProtectKit.ObscuredTypes;

namespace GLOW.Core.Domain.ValueObjects.Gacha
{
    public record GachaCautionId(ObscuredString Value) : IComparable<GachaCautionId>
    {
        public static GachaCautionId Empty { get; } = new GachaCautionId(string.Empty);

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
        
        public int CompareTo(GachaCautionId id)
        {
            return string.Compare(Value, id.Value, StringComparison.Ordinal);
        }
    }
}