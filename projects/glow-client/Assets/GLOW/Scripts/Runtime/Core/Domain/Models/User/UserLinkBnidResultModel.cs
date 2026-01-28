using System;
using GLOW.Core.Domain.ValueObjects;

namespace GLOW.Core.Domain.Models
{
    public record UserLinkBnIdResultModel(
        BnIdToken BnIdToken,
        DateTimeOffset BnIdLinkedAt)
    {
        public static UserLinkBnIdResultModel Empty { get; } = new(
            BnIdToken.Empty,
            DateTimeOffset.MinValue);
    }
}
