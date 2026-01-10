using System.Collections.Generic;

namespace GLOW.Scenes.HomeHelpDialog.Presentation.ViewModels
{
    public record HomeHelpViewModel(IReadOnlyList<HomeHelpMainContentCellViewModel> MainContents);
}
