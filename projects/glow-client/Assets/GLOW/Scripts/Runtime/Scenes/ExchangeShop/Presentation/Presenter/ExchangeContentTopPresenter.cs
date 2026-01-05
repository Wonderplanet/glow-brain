using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.Repositories;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Modules.MessageView.Presentation;
using GLOW.Scenes.ExchangeShop.Domain.UseCase;
using GLOW.Scenes.ExchangeShop.Domain.ValueObject;
using GLOW.Scenes.ExchangeShop.Presentation.Translator;
using GLOW.Scenes.ExchangeShop.Presentation.View;
using GLOW.Scenes.Home.Domain.Constants;
using GLOW.Scenes.Home.Presentation.Views;
using GLOW.Scenes.TradeShop.Presentation.View;
using WPFramework.Presentation.Modules;
using Zenject;

namespace GLOW.Scenes.ExchangeShop.Presentation.Presenter
{
    public class ExchangeContentTopPresenter : IExchangeContentTopViewDelegate
    {
        [Inject] ExchangeContentTopViewController ViewController { get; }
        [Inject] GetActiveExchangeContentUseCase GetActiveExchangeContentUseCase { get; }
        [Inject] IHomeViewNavigation HomeNavigation { get; }
        [Inject] IViewFactory ViewFactory { get; }
        [Inject] ITimeProvider TimeProvider { get; }
        [Inject] IMessageViewUtil MessageViewUtil { get; }

        void IExchangeContentTopViewDelegate.OnViewDidLoad()
        {
            ViewController.InitializeCollectionView();

            var useCaseModels = GetActiveExchangeContentUseCase.GetActiveTradeContents();
            ViewController.SetUpView(ExchangeContentTopViewModelTranslator.Translate(useCaseModels));
        }

        bool IExchangeContentTopViewDelegate.IsOpeningExchangeShop(ExchangeShopEndTime endAt)
        {
            // 開催期間が無期限の場合は常に開催中とみなす
            if(endAt.IsUnlimited()) return true;

            return TimeProvider.Now < endAt.Value;
        }

        void IExchangeContentTopViewDelegate.ShowExchangeShop(MasterDataId mstExchangeId, ExchangeTradeType tradeType)
        {
            // キャラのかけらBOX交換所は専用画面へ遷移
            if (tradeType == ExchangeTradeType.CharacterFragmentExchangeTrade)
            {
                var controller = ViewFactory.Create<FragmentTradeShopTopViewController>();
                HomeNavigation.TryPush(controller, HomeContentDisplayType.BottomOverlap);
            }
            else
            {
                var argument = new ExchangeShopTopViewController.Argument(mstExchangeId);
                var controller = ViewFactory.Create<ExchangeShopTopViewController, ExchangeShopTopViewController.Argument>(argument);
                HomeNavigation.TryPush(controller, HomeContentDisplayType.BottomOverlap);
            }
        }

        void IExchangeContentTopViewDelegate.OnBackButtonTapped()
        {
            HomeNavigation.TryPop();
        }

        void IExchangeContentTopViewDelegate.ShowBackToHomeMessage()
        {
            MessageViewUtil.ShowMessageWithButton(
                "確認",
                "交換所の掲載期間が終了しました。\nホームに戻ります。",
                "",
                "はい",
                TransitHomeTop);
        }

        void TransitHomeTop()
        {
            if (HomeNavigation.CurrentContentType == HomeContentTypes.Main)
            {
                HomeNavigation.TryPopToRoot();
            }
            else
            {
                HomeNavigation.Switch(HomeContentTypes.Main);
            }
        }
    }
}
