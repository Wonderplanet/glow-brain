using System.Collections.Generic;
using Cysharp.Text;
using GLOW.Scenes.Home.Domain.Models;
using WondlerPlanet.CheatProtectKit.ObscuredTypes;

namespace GLOW.Scenes.Home.Presentation.ViewModels
{
    public record HomeMainKomaSettingViewModel(
        HomeMainKomaSettingIndex InitialSelectedIndex,
        IReadOnlyList<HomeMainKomaPatternViewModel> HomeMainKomaPatternViewModels);
}
