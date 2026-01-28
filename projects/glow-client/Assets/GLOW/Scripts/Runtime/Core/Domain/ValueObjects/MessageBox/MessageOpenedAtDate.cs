using System;

namespace GLOW.Core.Domain.ValueObjects.MessageBox
{
    public record MessageOpenedAtDate(DateTimeOffset Value)
    {
        public static MessageOpenedAtDate Empty { get; } = new MessageOpenedAtDate(DateTimeOffset.MinValue);

        public string ToShortDateString()
        {
            return Value.ToString("yyyy-MM-dd");
        }
        
        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
    }
}