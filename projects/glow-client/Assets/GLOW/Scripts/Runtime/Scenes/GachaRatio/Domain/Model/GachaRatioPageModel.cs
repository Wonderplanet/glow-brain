namespace GLOW.Scenes.GachaRatio.Domain.Model
{
    public record GachaRatioPageModel(
        GachaRatioRarityRatioModel RarityRatioModel,
        GachaRatioLineupListModel GachaRatioPickupListModel,
        GachaRatioLineupListModel GachaRatioLineupListModel)
    {
        public static GachaRatioPageModel Empty { get; } = new GachaRatioPageModel(
            GachaRatioRarityRatioModel.Empty,
            GachaRatioLineupListModel.Empty,
            GachaRatioLineupListModel.Empty
        );

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
    }
}
