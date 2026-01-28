namespace GLOW.Scenes.ArtworkFragment.Presentation.ViewModels
{
    public record ArtworkPanelViewModel(ArtworkFragmentPanelViewModel FragmentPanelViewModel)
    {
        public static ArtworkPanelViewModel Empty { get; } = new(
            ArtworkFragmentPanelViewModel.Empty);
    }
}
