namespace GLOW.Core.Domain.ValueObjects
{
    public record IsEmblemDuplicated(bool Value)
    {
        public static IsEmblemDuplicated Empty { get; } = new IsEmblemDuplicated(false);

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
    }
}
