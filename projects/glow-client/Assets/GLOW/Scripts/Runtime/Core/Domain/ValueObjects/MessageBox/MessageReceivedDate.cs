using System;

namespace GLOW.Core.Domain.ValueObjects.MessageBox
{
    public record MessageReceivedDate(DateTimeOffset Value)
    {
        public static MessageReceivedDate Empty { get; } = new MessageReceivedDate(DateTimeOffset.MinValue);

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