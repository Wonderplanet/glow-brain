using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.ValueObjects;

namespace GLOW.Scenes.ArtworkEnhance.Domain.UseCaseModel
{
    public record RequiredEnhanceItemUseCaseModel(
        PlayerResourceModel IconModel,
        ItemAmount PossessionAmount,
        ItemAmount ConsumeAmount)
    {
        public static RequiredEnhanceItemUseCaseModel Empty { get; } =
            new RequiredEnhanceItemUseCaseModel(
                PlayerResourceModel.Empty,
                ItemAmount.Empty,
                ItemAmount.Empty);

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
    }
}
