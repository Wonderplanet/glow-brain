using GLOW.Scenes.GachaHistoryDetailDialog.Presentation.ViewModels;
using GLOW.Scenes.GachaHistoryDetailDialog.Presentation.Views;
using GLOW.Scenes.GachaHistoryDialog.Domain.UseCases;
using GLOW.Scenes.GachaHistoryDialog.Presentation.Translator;
using GLOW.Scenes.GachaHistoryDialog.Presentation.ViewModels;
using GLOW.Scenes.GachaHistoryDialog.Presentation.Views;
using UIKit;
using WonderPlanet.UniTaskSupporter;
using WPFramework.Presentation.InteractionControls;
using WPFramework.Presentation.Modules;
using Zenject;

namespace GLOW.Scenes.GachaHistoryDialog.Presentation.Presenters
{
    public class GachaHistoryWireFrame
    {
        [Inject] IViewFactory ViewFactory { get; }
        [Inject] GachaHistoryDialogUseCase GachaHistoryDialogUseCase { get; }
        [Inject] IScreenInteractionControl ScreenInteractionControl { get; }

        UIViewController _parentViewController;
        GachaHistoryDialogViewController.Argument _argument;
        
        public void ShowGachaHistoryDialogView(UIView view, UIViewController parentViewController)
        {
            DoAsync.Invoke(view, ScreenInteractionControl, async ct =>
            {
                _parentViewController = parentViewController;

                var useCaseModel = await GachaHistoryDialogUseCase.GetGachaHistoryDialogUseCaseModel(ct);
                var viewModel = GachaHistoryDialogViewModelTranslator.Translate(useCaseModel);

                _argument = new GachaHistoryDialogViewController.Argument(viewModel);
                var controller = ViewFactory.Create<
                    GachaHistoryDialogViewController,
                    GachaHistoryDialogViewController.Argument>(_argument);

                // 閉じたときに引数をクリアする、履歴詳細を開く際には呼ばれない
                controller.OnClose = () =>
                {
                    _argument = null;
                    _parentViewController = null;
                };

                parentViewController.PresentModally(controller);
            });
        }
        
        public void ShowGachaHistoryDetailDialogView(
            GachaHistoryCellViewModel cellViewModel,
            GachaHistoryDetailDialogViewModel detailViewModel,
            int currentPage = 1,
            float targetPos = 1f)
        {
            var argument = new GachaHistoryDetailDialogViewController.Argument(cellViewModel, detailViewModel);
            var controller = ViewFactory.Create<
                GachaHistoryDetailDialogViewController, 
                GachaHistoryDetailDialogViewController.Argument>(argument);
            
            controller.OnClose 
                = () =>
                {
                    var newArgument = new GachaHistoryDialogViewController.Argument(_argument.ViewModel, currentPage);
                    
                    // 閉じた際に元の画面を再表示する
                    var gachaHistoryController = ViewFactory.Create<
                        GachaHistoryDialogViewController, 
                        GachaHistoryDialogViewController.Argument>(newArgument);
                    _parentViewController.PresentModally(gachaHistoryController);
                    
                    // スクロール・ページを合わせるようにする
                    gachaHistoryController.ScrollToPage(targetPos);
                };
            
            _parentViewController.PresentModally(controller);
        }
    }
}