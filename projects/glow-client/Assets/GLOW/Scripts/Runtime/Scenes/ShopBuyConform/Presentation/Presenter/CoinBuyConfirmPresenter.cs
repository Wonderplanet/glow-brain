using GLOW.Scenes.ShopBuyConform.Presentation.View;
using WPFramework.Modules.Log;
using Zenject;

namespace GLOW.Scenes.ShopBuyConform.Presentation.Presenter
{
    public class CoinBuyConfirmPresenter : ICoinBuyConfirmViewDelegate
    {
        [Inject] CoinBuyConfirmViewController ViewController { get; }
        [Inject] CoinBuyConfirmViewController.Argument Argument { get; }

        public void OnViewDidLoad()
        {
            ApplicationLog.Log(nameof(CoinBuyConfirmPresenter), nameof(OnViewDidLoad));

            ViewController.SetViewModel(Argument.ViewModel);
        }

        public void OnViewDidUnload()
        {
            ApplicationLog.Log(nameof(CoinBuyConfirmPresenter), nameof(OnViewDidUnload));
        }

        public void OnTradeSelected()
        {
            ApplicationLog.Log(nameof(CoinBuyConfirmPresenter), nameof(OnTradeSelected));

            ViewController.Dismiss(completion:Argument.OnOkSelected);
        }

        public void OnCloseSelected()
        {
            ApplicationLog.Log(nameof(CoinBuyConfirmPresenter), nameof(OnCloseSelected));

            ViewController.Dismiss(completion:Argument.OnCloseSelected);
        }
    }
}
