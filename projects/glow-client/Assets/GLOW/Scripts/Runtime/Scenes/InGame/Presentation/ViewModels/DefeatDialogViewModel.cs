using GLOW.Scenes.InGame.Presentation.ValueObjects;

namespace GLOW.Scenes.InGame.Presentation.ViewModels
{
    public record DefeatDialogViewModel(DefeatDescription Description)
    {
        public static DefeatDialogViewModel Empty { get; } = new (DefeatDescription.Empty);

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
    }
}
