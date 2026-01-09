namespace GLOW.Scenes.GachaRatio.Presentation.ViewModels
{
    public record GachaRatioPageViewModel(
        GachaRatioByRarityViewModel ByRarityViewModel,
        GachaRatioLineupListViewModel GachaRatioPickupListViewModel,
        GachaRatioLineupListViewModel GachaRatioLineupListViewModel)
    {
        public static GachaRatioPageViewModel Empty { get; } = new GachaRatioPageViewModel(
            GachaRatioByRarityViewModel.Empty,
            GachaRatioLineupListViewModel.Empty,
            GachaRatioLineupListViewModel.Empty
        );

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
    }
}
