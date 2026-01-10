using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Scenes.Home.Presentation.Views;
using GLOW.Scenes.ItemBox.Domain.UseCases;
using GLOW.Scenes.ItemBox.Presentation.Navigation;
using GLOW.Scenes.ItemBox.Presentation.Views;
using GLOW.Scenes.ItemBox.Domain.ValueObjects;
using GLOW.Scenes.ItemBox.Presentation.Translators;
using GLOW.Scenes.ItemDetail.Presentation.Presenters;
using WPFramework.Modules.Log;
using WPFramework.Presentation.Modules;
using Zenject;

namespace GLOW.Scenes.ItemBox.Presentation.Presenters
{
    public sealed class ItemBoxPresenter : IItemBoxViewDelegate
    {
        static readonly ItemAmount LimitUseAmount = new(100);

        [Inject] ItemBoxViewController ViewController { get; }
        [Inject] IHomeViewNavigation HomeViewNavigation { get; }
        [Inject] IGachaTransitionNavigator GachaTransitionNavigator { get; }
        [Inject] IViewFactory ViewFactory { get; }
        [Inject] GetItemBoxItemListUseCase GetItemBoxItemListUseCase { get; }
        [Inject] GetItemBoxItemUseCase GetItemBoxItemUseCase { get; }
        [Inject] SelectionFragmentBoxWireFrame SelectionFragmentBoxWireFrame { get; }
        [Inject] IItemDetailWireFrame ItemDetailWireFrame { get; }

        ItemBoxTabType _currentItemBoxTabType;
        RandomFragmentBoxViewController _randomFragmentBoxViewController;

        void IItemBoxViewDelegate.OnViewDidLoad()
        {
            ApplicationLog.Log(nameof(ItemBoxPresenter), nameof(IItemBoxViewDelegate.OnViewDidLoad));

            _currentItemBoxTabType = ItemBoxTabType.Item;
            ViewController.InitializeItemList();
            SetupItemList(ItemBoxTabType.Item);
            ViewController.PlayCellAppearanceAnimation(ItemBoxTabType.Item);
        }

        void IItemBoxViewDelegate.ViewDidUnload()
        {
            ApplicationLog.Log(nameof(ItemBoxPresenter), nameof(IItemBoxViewDelegate.ViewDidUnload));
            _randomFragmentBoxViewController?.Dismiss(false);
            _randomFragmentBoxViewController = null;
        }

        void IItemBoxViewDelegate.OnBackSelected()
        {
            HomeViewNavigation.TryPop();
        }

        void IItemBoxViewDelegate.OnItemGroupSelected(ItemBoxTabType itemBoxTabType)
        {
            // 現在のタブと同じ場合は何もしない
            if (_currentItemBoxTabType == itemBoxTabType) return;

            _currentItemBoxTabType = itemBoxTabType;

            SetupItemList(itemBoxTabType);
            ViewController.PlayCellAppearanceAnimation(itemBoxTabType);
        }

        void IItemBoxViewDelegate.OnItemSelected(MasterDataId itemId)
        {
            var itemModel = GetItemBoxItemUseCase.GetItem(itemId);
            if (itemModel.IsEmpty()) return;

            switch (itemModel.Type)
            {
                case ItemType.RandomFragmentBox:
                    ShowRandomFragmentBoxView(itemModel);
                    break;
                case ItemType.SelectionFragmentBox:
                    ShowSelectionFragmentBoxView(itemModel);
                    break;
                case ItemType.GachaTicket:
                case ItemType.GachaMedal:
                    ShowGachaCostItemDetailView(itemModel);
                    break;
                default:
                    ShowItemDetailView(itemModel);
                    break;
            }
        }

        void ShowItemDetailView(ItemModel itemModel)
        {
            ItemDetailWireFrame.ShowItemDetailView(
                ResourceType.Item,
                itemModel.Id,
                new PlayerResourceAmount(itemModel.Amount.Value),
                ViewController,
                true);
        }

        void ShowGachaCostItemDetailView(ItemModel itemModel)
        {
            ItemDetailWireFrame.ShowGachaCostItemDetailView(itemModel.Id, ViewController);
        }

        void ShowRandomFragmentBoxView(ItemModel randomFragmentBoxItemModel)
        {
            var argument = new RandomFragmentBoxViewController.Argument(
                randomFragmentBoxItemModel,
                LimitUseAmount,
                () => SetupItemList(_currentItemBoxTabType),
                () => TryReShowRandomFragmentBoxView(randomFragmentBoxItemModel.Id));

            _randomFragmentBoxViewController = ViewFactory.Create<
                RandomFragmentBoxViewController,
                RandomFragmentBoxViewController.Argument>(argument);
            ViewController.PresentModally(_randomFragmentBoxViewController);
        }

        void TryReShowRandomFragmentBoxView(MasterDataId itemId)
        {
            // 交換後、モーダル再表示するための処理
            var itemModel = GetItemBoxItemUseCase.GetItem(itemId);
            if (itemModel.IsEmpty() || itemModel.Amount.IsZero()) return;
            ShowRandomFragmentBoxView(itemModel);
        }

        void ShowSelectionFragmentBoxView(ItemModel itemModel)
        {
            var argument = new SelectionFragmentBoxViewController.Argument(
                itemModel,
                LimitUseAmount,
                () => SetupItemList(_currentItemBoxTabType),
                () => TryReshowSelectionFragmentBoxView(itemModel.Id),
                MasterDataId.Empty);

            SelectionFragmentBoxWireFrame.ShowSelectionFragmentBoxViewController(argument,ViewController);
        }

        void TryReshowSelectionFragmentBoxView(MasterDataId mstItemId)
        {
            // 交換後、モーダル再表示するための処理
            var itemModel = GetItemBoxItemUseCase.GetItem(mstItemId);
            if (itemModel.IsEmpty() || itemModel.Amount.IsZero()) return;
            ShowSelectionFragmentBoxView(itemModel);
        }

        void SetupItemList(ItemBoxTabType itemBoxTabType)
        {
            var itemBoxModels = GetItemBoxItemListUseCase.GetItemList(itemBoxTabType);
            var itemBoxIconListViewModelViewModel =
                ItemBoxViewModelTranslator.ToItemBoxIconListViewModel(itemBoxModels, itemBoxTabType);

            ViewController.SetupItemListAndReload(itemBoxTabType, itemBoxIconListViewModelViewModel);
        }

        void TransitionToGachaList()
        {
            GachaTransitionNavigator.ShowGachaListView();
        }
    }
}
