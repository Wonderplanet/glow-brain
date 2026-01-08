using GLOW.Scenes.GachaRatio.Domain.Model;
using GLOW.Core.Domain.ValueObjects.Gacha; 

namespace GLOW.Scenes.GachaLineupDialog.Domain.Models
{
    public record GachaLineupDialogUseCaseModel(
        GachaLineupPageModel NormalPrizePageModel,
        GachaLineupPageModel NormalPrizeInFixedPageModel,
        GachaLineupPageModel UpperPrizeInMaxRarityPageModel,
        GachaLineupPageModel UpperPrizeInPickupPageModel,
        GachaFixedPrizeDescription GachaFixedPrizeDescription)
    {
        public static GachaLineupDialogUseCaseModel Empty { get; } = new(
            GachaLineupPageModel.Empty,
            GachaLineupPageModel.Empty,
            GachaLineupPageModel.Empty,
            GachaLineupPageModel.Empty,
            GachaFixedPrizeDescription.Empty);

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
    };
}