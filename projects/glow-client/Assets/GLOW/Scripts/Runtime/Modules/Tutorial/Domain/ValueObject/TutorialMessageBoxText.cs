namespace GLOW.Modules.Tutorial.Domain.ValueObject
{
    public record TutorialMessageBoxText(string Value)
    {
        public static TutorialMessageBoxText Empty { get; }= new TutorialMessageBoxText(string.Empty);

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
    }
}
