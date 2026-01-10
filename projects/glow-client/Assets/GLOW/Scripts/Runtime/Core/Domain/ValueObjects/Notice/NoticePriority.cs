using System;
using WondlerPlanet.CheatProtectKit.ObscuredTypes;

namespace GLOW.Core.Domain.ValueObjects.Notice
{
    public record NoticePriority(ObscuredInt Value)  : IComparable<NoticePriority>
    {
        public int CompareTo(NoticePriority other) => Value.CompareTo(other.Value);
    }
}