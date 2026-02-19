using System.Collections.Generic;
using GLOW.Core.Presentation.ViewModels;

namespace GLOW.Scenes.ItemBox.Presentation.ViewModels
{
    public record ItemBoxIconListViewModel(IReadOnlyList<ItemIconViewModel> IconViewModels);
}
