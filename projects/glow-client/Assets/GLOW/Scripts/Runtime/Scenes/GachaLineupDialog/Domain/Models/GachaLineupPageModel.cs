namespace GLOW.Scenes.GachaLineupDialog.Domain.Models
{
    public record GachaLineupPageModel(
        GachaLineupListModel GachaLineupPickupListModel,
        GachaLineupListModel GachaLineupListModel)
    {
        public static GachaLineupPageModel Empty { get; } = new GachaLineupPageModel(
            GachaLineupListModel.Empty,
            GachaLineupListModel.Empty
        );

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
    }
}