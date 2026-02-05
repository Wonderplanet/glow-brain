using GLOW.Core.Domain.ValueObjects.Gacha; 

namespace GLOW.Scenes.GachaRatio.Domain.Model
{
    public record GachaRatioDialogUseCaseModel(
        GachaRatioPageModel NormalPrizePageModel,
        GachaRatioPageModel NormalPrizeInFixedPageModel,
        GachaRatioPageModel UpperPrizeInMaxRarityPageModel,
        GachaRatioPageModel UpperPrizeInPickupPageModel,
        GachaFixedPrizeDescription GachaFixedPrizeDescription)
    {
        public static GachaRatioDialogUseCaseModel Empty { get; } = new(
            GachaRatioPageModel.Empty,
            GachaRatioPageModel.Empty,
            GachaRatioPageModel.Empty,
            GachaRatioPageModel.Empty,
            GachaFixedPrizeDescription.Empty);

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
    };
}