using System;
using WondlerPlanet.CheatProtectKit.ObscuredTypes;

namespace GLOW.Core.Domain.ValueObjects.Mission
{
    public record MstArtworkPanelMissionEndDate(ObscuredDateTimeOffset Value)
    {
        public static MstArtworkPanelMissionEndDate Empty { get; } = new(DateTimeOffset.MinValue);

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
    }
}