using System.Collections.Generic;
using System.Data;
using WondlerPlanet.CheatProtectKit.ObscuredTypes;

namespace GLOW.Scenes.HomeMainKomaSettingUnitSelect.Presentation
{
    public record HomeMainKomaSettingUnitSelectViewModel(
        IReadOnlyList<HomeMainKomaSettingUnitSelectItemViewModel> Units
        );
}
