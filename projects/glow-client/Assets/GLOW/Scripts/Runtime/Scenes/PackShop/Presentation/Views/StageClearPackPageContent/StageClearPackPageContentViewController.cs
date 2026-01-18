using GLOW.Scenes.PackShop.Presentation.ViewModels.StageClearPackPageContent;
using UIKit;

namespace GLOW.Scenes.PackShop.Presentation.Views.StageClearPackPageContent
{
    public class StageClearPackPageContentViewController : UIViewController<StageClearPackPageContentView>
    {
        public void SetViewModel(StageClearPackPageContentViewModel viewModel)
        {
            ActualView.Cell.Setup(viewModel.ViewModel, viewModel.BuyEvent, viewModel.InfoEvent);
        }
    }
}
