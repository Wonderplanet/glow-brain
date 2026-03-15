using System.Collections.Generic;
using GLOW.Scenes.HomeHelpDialog.Domain.ValueObjects;

namespace GLOW.Scenes.HomeHelpDialog.Presentation.ViewModels
{
    public record HomeHelpSubContentCellViewModel(HomeHelpTitle Header, IReadOnlyList<HomeHelpArticleViewModel> Articles);
}
