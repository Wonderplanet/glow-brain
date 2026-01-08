using Cysharp.Threading.Tasks;
using GLOW.Scenes.DiamondPurchaseHistory.Domain;
using WonderPlanet.UniTaskSupporter;
using Zenject;

namespace GLOW.Scenes.DiamondPurchaseHistory.Presentation
{
    public class DiamondPurchaseHistoryPresenter : IDiamondPurchaseHistoryViewDelegate
    {
        [Inject] DiamondPurchaseHistoryViewController ViewController { get; }
        [Inject] DiamondPurchaseHistoryUseCase UseCase { get; }

        void IDiamondPurchaseHistoryViewDelegate.OnViewDidLoad()
        {
            DoAsync.Invoke(ViewController.View, async ct =>
            {
                var model = await UseCase.GetModel(ViewController.ActualView.destroyCancellationToken);
                ViewController.SetUpView(CreateViewModel(model));
            });
        }

        DiamondPurchaseHistoryViewModel CreateViewModel(DiamondPurchaseHistoryUseCaseModel model)
        {
            var viewModel = DiamondPurchaseHistoryViewModelTranslator.ToDiamondPurchaseHistoryViewModel(model);
            return viewModel;
        }
    }
}
