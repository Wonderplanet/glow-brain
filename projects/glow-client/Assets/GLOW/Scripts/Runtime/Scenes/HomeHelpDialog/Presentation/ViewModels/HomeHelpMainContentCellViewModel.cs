using System.Collections.Generic;
using GLOW.Scenes.HomeHelpDialog.Domain.ValueObjects;

namespace GLOW.Scenes.HomeHelpDialog.Presentation.ViewModels
{
    public record HomeHelpMainContentCellViewModel(HomeHelpTitle Header, IReadOnlyList<HomeHelpSubContentCellViewModel> SubContents);
}
