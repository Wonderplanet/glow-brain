using System.Collections.Generic;
using GLOW.Scenes.Home.Domain.Constants;
using GLOW.Scenes.Home.Presentation.Views;
using UIKit;

namespace GLOW.Core.Presentation.Presenters
{
    public record SceneWireFrameViewModel(IReadOnlyList<(UIViewController vc, HomeContentDisplayType showType)>VCs, HomeContentTypes HomeContentType);
}