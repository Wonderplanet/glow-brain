using System.Collections.Generic;
using GLOW.Core.Domain.Models.Gacha;

namespace GLOW.Core.Domain.Models
{
    public record GachaDrawResultModel(
        IReadOnlyList<GachaResultModel> GachaResultModels,
        IReadOnlyList<UserUnitModel> UserUnitModels,
        IReadOnlyList<UserItemModel> UserItemModels,
        UserParameterModel UserParameterModel,
        IReadOnlyList<UserDrawCountThresholdModel> UserDrawCountThresholdModels,
        UserGachaModel UserGachaModel
    );
}
