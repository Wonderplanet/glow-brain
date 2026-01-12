using GLOW.Scenes.ArtworkFragment.Domain.ValueObjects;

namespace GLOW.Scenes.EventQuestTop.Domain.UseCases
{
    public record ArtworkFragmentStatusModel(ArtworkFragmentNum Gettable, ArtworkFragmentNum Acquired)
    {
        public static ArtworkFragmentStatusModel Empty { get; } = new ArtworkFragmentStatusModel(ArtworkFragmentNum.Empty, ArtworkFragmentNum.Empty);

    };
}