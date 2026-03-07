using GLOW.Scenes.ArtworkFragment.Domain.ValueObjects;

namespace GLOW.Scenes.ArtworkFragment.Presentation.ViewModels
{
    public record ArtworkFragmentViewModel(
        ArtworkFragmentPositionNum PositionNum,
        ArtworkFragmentNum Number,
        bool IsUnlock)
    {
        public static ArtworkFragmentViewModel Empty { get; } = new(
            ArtworkFragmentPositionNum.Empty,
            ArtworkFragmentNum.Empty,
            false);

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
    }
}
