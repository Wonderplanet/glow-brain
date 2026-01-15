using GLOW.Core.Presentation.ViewModels;
using GLOW.Scenes.BoxGachaLineupDialog.Domain.UseCase;
using GLOW.Scenes.BoxGachaLineupDialog.Presentation.Translator;
using GLOW.Scenes.BoxGachaLineupDialog.Presentation.View;
using GLOW.Scenes.ItemDetail.Presentation.Presenters;
using Zenject;

namespace GLOW.Scenes.BoxGachaLineupDialog.Presentation.Presenter
{
    public class BoxGachaLineupDialogPresenter : IBoxGachaLineupDialogViewDelegate
    {
        [Inject] BoxGachaLineupDialogViewController ViewController { get; }
        [Inject] BoxGachaLineupDialogViewController.Argument Argument { get; }
        [Inject] ShowBoxGachaLineupUseCase ShowBoxGachaLineupUseCase { get; }
        [Inject] IItemDetailWireFrame ItemDetailWireFrame { get; }
        
        public void OnViewDidLoad()
        {
            var model = ShowBoxGachaLineupUseCase.GetLineup(Argument.MstBoxGachaId, Argument.CurrentBoxLevel);
            var viewModel = BoxGachaLineupModelTranslator.ToBoxGachaLineupDialogViewModel(model);
            ViewController.SetUpViewModel(viewModel);
        }

        void IBoxGachaLineupDialogViewDelegate.OnPrizeIconTapped(PlayerResourceIconViewModel playerResourceIconViewModel)
        {
            ItemDetailWireFrame.ShowNoTransitionLayoutItemDetailView(playerResourceIconViewModel, ViewController);
        }

        void IBoxGachaLineupDialogViewDelegate.OnCloseButtonTapped()
        {
            ViewController.Dismiss();
        }
    }
}