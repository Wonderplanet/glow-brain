using System;

namespace GLOW.Core.Domain.ValueObjects
{
    public record BnIdToken(String Value)
    {
        public static BnIdToken Empty { get; } = new BnIdToken(string.Empty);

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
    }
}
