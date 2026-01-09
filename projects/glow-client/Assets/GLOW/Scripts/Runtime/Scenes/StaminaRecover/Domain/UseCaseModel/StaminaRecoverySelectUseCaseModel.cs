using System.Collections.Generic;
using GLOW.Scenes.StaminaRecover.Domain.ValueObject;

namespace GLOW.Scenes.StaminaRecover.Domain.UseCaseModel
{
    public record StaminaRecoverySelectUseCaseModel(
        StaminaShortageFlag IsStaminaShortage,
        IReadOnlyList<StaminaRecoverySelectCellUseCaseModel> CellUseCaseModels);
}
