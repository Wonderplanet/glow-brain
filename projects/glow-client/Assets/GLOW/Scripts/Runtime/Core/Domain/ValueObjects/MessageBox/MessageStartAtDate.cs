using System;
using System.Globalization;
using GLOW.Core.Domain.Modules.Time;
using GLOW.Core.Extensions;
using WondlerPlanet.CheatProtectKit.ObscuredTypes;

namespace GLOW.Core.Domain.ValueObjects.MessageBox
{
    public record MessageStartAtDate(ObscuredDateTimeOffset Value)
    {
        public static MessageStartAtDate Empty { get; } = new MessageStartAtDate(DateTimeOffset.MinValue);

        public string ToShortDateString()
        {
            return DateTimeOffsetFormatter.FormatDate(Value.ToJst());
        }
        
        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
    }
}