using System;

namespace GLOW.Core.Domain.ValueObjects
{
    public record AgreementUrl(string Value)
    {
        public static AgreementUrl Empty { get; } = new(string.Empty);
    }
}
