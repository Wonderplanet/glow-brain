using System;

namespace GLOW.Core.Domain.ValueObjects.TitleBnIdLinkageResultDialog
{
    public record TitleBnIdLinkageResultTitle(string Value)
    {
        public static TitleBnIdLinkageResultTitle Empty { get; } = new(String.Empty);

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
    }
}
