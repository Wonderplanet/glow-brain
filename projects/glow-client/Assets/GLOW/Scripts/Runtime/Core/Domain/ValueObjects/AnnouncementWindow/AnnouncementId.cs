using System;
using WondlerPlanet.CheatProtectKit.ObscuredTypes;

namespace GLOW.Core.Domain.ValueObjects.AnnouncementWindow
{
    public record AnnouncementId(ObscuredString Value) : IComparable<AnnouncementId>
    {
        public static AnnouncementId Empty { get; } = new AnnouncementId(string.Empty);

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
        public int CompareTo(AnnouncementId id)
        {
            return string.Compare(Value, id.Value, StringComparison.Ordinal);
        }
    }
}