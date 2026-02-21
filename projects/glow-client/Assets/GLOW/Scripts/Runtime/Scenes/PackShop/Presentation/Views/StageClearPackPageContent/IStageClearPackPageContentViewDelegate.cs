using System;
using GLOW.Core.Domain.ValueObjects.Shop;

namespace GLOW.Scenes.PackShop.Presentation.Views.StageClearPackPageContent
{
    public interface IStageClearPackPageContentViewDelegate
    {
        void OnViewDidLoad();
        TimeSpan GetRemainCountDown(EndDateTime endTime);
    }
}
