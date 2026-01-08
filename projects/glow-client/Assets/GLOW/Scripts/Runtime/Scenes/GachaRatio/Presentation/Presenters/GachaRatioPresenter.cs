using GLOW.Scenes.GachaRatio.Domain.Constants;
using GLOW.Scenes.GachaRatio.Presentation.Views;
using Zenject;

namespace GLOW.Scenes.GachaRatio.Presentation.Presenters
{
    /// <summary>
    /// 71-1_ガシャ
    /// 　71-1-12_提供割合表示(BNEレギュ)
    /// </summary>
    public class GachaRatioPresenter : IGachaRatioDialogViewDelegate
    {
        [Inject] GachaRatioDialogViewController ViewController { get; }
        [Inject] GachaRatioDialogViewController.Argument Args { get; }
        [Inject] GachaWireFrame.Presentation.Presenters.GachaWireFrame GachaWireFrame { get; }

        void IGachaRatioDialogViewDelegate.OnViewDidLoad()
        {
            ViewController.Args = Args;
            ViewController.Setup(Args.ViewModel);
            ViewController.GachaRatioPageSetUp(ViewController.CurrentTab);
        }

        void IGachaRatioDialogViewDelegate.OnClosed()
        {
            GachaWireFrame.OnCloseGachaRatioDialogViewAndInvokeAction();
        }

        void IGachaRatioDialogViewDelegate.OnNormalRatioTabSelected()
        {
            ViewController.GachaRatioPageUpdate(GachaRatioTabType.NormalRatioTab);
        }

        void IGachaRatioDialogViewDelegate.OnSSRRatioTabSelected()
        {
            ViewController.GachaRatioPageUpdate(GachaRatioTabType.SSRRatioTab);
        }

        void IGachaRatioDialogViewDelegate.OnURRatioTabSelected()
        {
            ViewController.GachaRatioPageUpdate(GachaRatioTabType.URRatioTab);
        }

        void IGachaRatioDialogViewDelegate.OnPickupRatioTabSelected()
        {
            ViewController.GachaRatioPageUpdate(GachaRatioTabType.PickupRatioTab);
        }
    }
}
