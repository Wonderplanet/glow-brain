namespace GLOW.Scenes.InGame.Domain.ValueObjects
{
    public record AttackId(int Value)
    {
        public static AttackId Empty { get; } = new(0);

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }

    }
}
