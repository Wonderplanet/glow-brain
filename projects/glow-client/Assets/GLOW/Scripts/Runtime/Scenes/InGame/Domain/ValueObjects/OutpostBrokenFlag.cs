namespace GLOW.Scenes.InGame.Domain.ValueObjects
{
    public record OutpostBrokenFlag(bool Flag)
    {
        public static OutpostBrokenFlag Empty { get; } = new OutpostBrokenFlag(false);
        public static OutpostBrokenFlag True { get; } = new OutpostBrokenFlag(true);
        public static OutpostBrokenFlag False { get; } = new OutpostBrokenFlag(false);

        public bool IsTrue()
        {
            return this == True;
        }
    }
}
