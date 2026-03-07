using System.Collections.Generic;

namespace GLOW.Scenes.ArtworkEnhance.Domain.UseCaseModel
{
    public record ArtworkGradeContentsUseCaseModel(
        IReadOnlyList<ArtworkGradeContentCellUseCaseModel> CellUseCaseModels)
    {
        public static ArtworkGradeContentsUseCaseModel Empty { get; } =
            new ArtworkGradeContentsUseCaseModel(new List<ArtworkGradeContentCellUseCaseModel>());

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
    }
}
