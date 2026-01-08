namespace GLOW.Modules.Tutorial.Domain.ValueObject
{
    public record TutorialCallbackActionIdentifier(string Value)
    {
        public static TutorialCallbackActionIdentifier Empty { get; }= new TutorialCallbackActionIdentifier(string.Empty);

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
    }
}
