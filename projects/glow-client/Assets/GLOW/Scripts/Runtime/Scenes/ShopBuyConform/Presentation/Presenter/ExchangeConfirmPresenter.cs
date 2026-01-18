using GLOW.Scenes.ShopBuyConform.Presentation.View;
using WPFramework.Modules.Log;
using Zenject;

namespace GLOW.Scenes.ShopBuyConform.Presentation.Presenter
{
    public class ExchangeConfirmPresenter : IExchangeConfirmViewDelegate
    {
        [Inject] ExchangeConfirmViewController ViewController { get; }
        [Inject] ExchangeConfirmViewController.Argument Argument { get; }

        public void OnViewDidLoad()
        {
            ApplicationLog.Log(nameof(ExchangeConfirmPresenter), nameof(OnViewDidLoad));

            ViewController.Setup(Argument.ViewModel);
        }

        public void OnViewDidUnload()
        {
            ApplicationLog.Log(nameof(ExchangeConfirmPresenter), nameof(OnViewDidUnload));
        }

        public void OnExchangeSelected()
        {
            ApplicationLog.Log(nameof(ExchangeConfirmPresenter), nameof(OnExchangeSelected));

            ViewController.Dismiss(completion:Argument.OnExchangeSelected);
        }

        public void OnCancelSelected()
        {
            ApplicationLog.Log(nameof(ExchangeConfirmPresenter), nameof(OnCancelSelected));
            ViewController.Dismiss(completion:Argument.OnCancelSelected);
        }
    }
}
