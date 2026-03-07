using System.Collections.Generic;
using GLOW.Scenes.Home.Domain.ValueObjects;

namespace GLOW.Scenes.Home.Domain.Models
{
    public record HomeMainKomaSettingUnitSelectUseCaseModel(
        IReadOnlyList<HomeMainKomaSettingUnitSelectItemUseCaseModel> Units);
}
