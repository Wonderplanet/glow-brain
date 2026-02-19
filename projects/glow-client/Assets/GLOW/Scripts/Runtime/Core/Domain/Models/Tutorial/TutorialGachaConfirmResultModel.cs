using System.Collections.Generic;

namespace GLOW.Core.Domain.Models.Tutorial
{
    public record TutorialGachaConfirmResultModel(
        TutorialStatusModel TutorialStatusModel,
        IReadOnlyList<UserUnitModel> UserUnitModels,
        IReadOnlyList<UserItemModel> UserItemModels,
        UserParameterModel UserParameterModel);
}
