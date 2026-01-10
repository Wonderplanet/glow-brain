namespace GLOW.Scenes.InGame.Domain.ValueObjects
{
    public record StateEffectId(int Value)
    {
        public static StateEffectId Empty { get; } = new(0);

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
    }
}
