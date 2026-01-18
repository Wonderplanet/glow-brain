using System.Collections.Generic;
using System.Linq;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Presentation.Translators;
using GLOW.Scenes.ExchangeShop.Domain.UseCase;
using GLOW.Scenes.ExchangeShop.Presentation.View;
using GLOW.Scenes.ExchangeShop.Presentation.ViewModel;
using GLOW.Scenes.Home.Presentation.Views;
using GLOW.Scenes.ItemBox.Domain.UseCases;
using GLOW.Scenes.ItemBox.Presentation.Presenters;
using GLOW.Scenes.ItemBox.Presentation.Views;
using GLOW.Scenes.TradeShop.Presentation.View;
using Zenject;

namespace GLOW.Scenes.ExchangeShop.Presentation.Presenter
{
    public class FragmentTradeShopTopPresenter : IFragmentTradeShopTopViewDelegate
    {
        [Inject] FragmentTradeShopTopViewController ViewController { get; }
        [Inject] IHomeViewNavigation HomeViewNavigation { get; }
        [Inject] GetTradeFragmentUseCase GetTradeFragmentUseCase { get; }
        [Inject] GetItemBoxItemUseCase GetItemBoxItemUseCase { get; }
        [Inject] FragmentBoxTradeWireFrame FragmentBoxTradeWireFrame { get; }

        void IFragmentTradeShopTopViewDelegate.OnViewDidLoad()
        {
            ViewController.InitializeView();
            RefreshView();
        }

        void IFragmentTradeShopTopViewDelegate.ShowTradeConfirmView(MasterDataId itemMstId)
        {
            var itemModel = GetItemBoxItemUseCase.GetItem(itemMstId);
            if (itemModel.IsEmpty()) return;

            FragmentBoxTradeWireFrame.ShowFragmentBoxTradeView(
                new FragmentBoxTradeViewController.Argument(itemModel),
                () =>
                {
                    RefreshView();
                },
                ViewController);
        }

        void RefreshView()
        {
            var itemModels = GetTradeFragmentUseCase.GetTradeFragments();
            var viewModel = TranslateToViewModels(itemModels);
            ViewController.SetUpView(viewModel);
        }

        FragmentTradeShopTopViewModel TranslateToViewModels(IReadOnlyList<ItemModel> itemModels)
        {
            var viewModels = itemModels
                .Select(item => ItemViewModelTranslator.ToItemIconViewModel(item))
                .ToList();

            return new FragmentTradeShopTopViewModel(viewModels);
        }

        void IFragmentTradeShopTopViewDelegate.OnBackButtonTapped()
        {
            HomeViewNavigation.TryPop();
        }
    }
}
