using GLOW.Core.Domain.ValueObjects;
using GLOW.Scenes.ArtworkFragmentAcquisition.Domain.Models;

namespace GLOW.Scenes.UnitEnhance.Domain.Models
{
    public record UnitEnhanceGradeUpResultModel(
        UserDataId UserUnitId,
        UnitGrade BeforeGrade,
        UnitGrade AfterGrade,
        ArtworkFragmentAcquisitionModel ArtworkFragmentAcquisitionModel);
}
