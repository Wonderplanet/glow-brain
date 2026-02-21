using System.Collections.Generic;

namespace GLOW.Scenes.GachaAnim.Presentation.ViewModels
{
    public record GachaAnimViewModel(
        GachaAnimStartViewModel GachaAnimStartViewModel,
        List<GachaAnimResultViewModel> GashaAnimResultViewModelList
    );
}
