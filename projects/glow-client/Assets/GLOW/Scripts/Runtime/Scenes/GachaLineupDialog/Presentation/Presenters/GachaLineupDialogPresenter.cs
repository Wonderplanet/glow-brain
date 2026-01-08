using GLOW.Scenes.GachaLineupDialog.Presentation.Views;
using GLOW.Scenes.GachaRatio.Domain.Constants;
using Zenject;

namespace GLOW.Scenes.GachaLineupDialog.Presentation.Presenters
{
    public class GachaLineupDialogPresenter : IGachaLineupDialogDelegate
    {
        [Inject] GachaLineupDialogViewController ViewController { get; }
        [Inject] GachaLineupDialogViewController.Argument Args { get; }
        [Inject] GachaWireFrame.Presentation.Presenters.GachaWireFrame GachaWireFrame { get; }

        void IGachaLineupDialogDelegate.OnViewDidLoad()
        {
            ViewController.Args = Args;
            ViewController.Setup(Args.ViewModel);
            ViewController.GachaRatioPageSetUp(ViewController.CurrentTab);
        }

        void IGachaLineupDialogDelegate.OnClosed()
        {
            GachaWireFrame.OnCloseGachaLineUpDialogViewAndInvokeAction();
        }

        void IGachaLineupDialogDelegate.OnNormalRatioTabSelected()
        {
            ViewController.GachaRatioPageUpdate(GachaRatioTabType.NormalRatioTab);
        }

        void IGachaLineupDialogDelegate.OnSSRRatioTabSelected()
        {
            ViewController.GachaRatioPageUpdate(GachaRatioTabType.SSRRatioTab);
        }

        void IGachaLineupDialogDelegate.OnURRatioTabSelected()
        {
            ViewController.GachaRatioPageUpdate(GachaRatioTabType.URRatioTab);
        }

        void IGachaLineupDialogDelegate.OnPickupRatioTabSelected()
        {
            ViewController.GachaRatioPageUpdate(GachaRatioTabType.PickupRatioTab);
        }
    }
}