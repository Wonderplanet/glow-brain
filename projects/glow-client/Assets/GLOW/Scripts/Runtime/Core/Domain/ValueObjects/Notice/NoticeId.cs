using System;
using WondlerPlanet.CheatProtectKit.ObscuredTypes;

namespace GLOW.Core.Domain.ValueObjects.Notice
{
    public record NoticeId(ObscuredString Value) : IComparable<NoticeId>
    {
        public static NoticeId Empty { get; } = new NoticeId(string.Empty);

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
        public int CompareTo(NoticeId id)
        {
            return string.Compare(Value, id.Value, StringComparison.Ordinal);
        }
    }
}