namespace GLOW.Scenes.GachaLineupDialog.Presentation.ViewModels
{
    public record GachaLineupPageViewModel(
        GachaLineupListViewModel GachaPickupListViewModel,
        GachaLineupListViewModel GachaLineupListViewModel)
    {
        public static GachaLineupPageViewModel Empty { get; } = new GachaLineupPageViewModel(
            GachaLineupListViewModel.Empty,
            GachaLineupListViewModel.Empty
        );

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
    }
}