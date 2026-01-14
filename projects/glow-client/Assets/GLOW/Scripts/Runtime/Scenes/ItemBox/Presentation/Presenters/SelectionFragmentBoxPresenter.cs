using System;
using System.Collections.Generic;
using System.Linq;
using System.Threading;
using Cysharp.Threading.Tasks;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Extensions;
using GLOW.Core.Presentation.Modules;
using GLOW.Core.Presentation.Translators;
using GLOW.Core.Presentation.ViewModels;
using GLOW.Modules.CommonReceiveView.Presentation.Presenters;
using GLOW.Modules.CommonReceiveView.Presentation.ViewModel;
using GLOW.Scenes.Home.Presentation.Interface;
using GLOW.Scenes.ItemBox.Domain.Evaluator;
using GLOW.Scenes.ItemBox.Domain.UseCases;
using GLOW.Scenes.ItemBox.Presentation.ViewModels;
using GLOW.Scenes.ItemBox.Presentation.Views;
using GLOW.Scenes.ItemDetail.Domain.Models;
using GLOW.Scenes.ItemDetail.Presentation.Translator;
using GLOW.Scenes.ItemDetail.Presentation.Views;
using GLOW.Scenes.ShopBuyConform.Presentation.View;
using GLOW.Scenes.ShopBuyConform.Presentation.ViewModel;
using WPFramework.Modules.Log;
using Zenject;

namespace GLOW.Scenes.ItemBox.Presentation.Presenters
{
    public class SelectionFragmentBoxPresenter : ISelectionFragmentBoxViewDelegate
    {
        [Inject] SelectionFragmentBoxViewController ViewController { get; }
        [Inject] SelectionFragmentBoxViewController.Argument Argument { get; }
        [Inject] GetFragmentLineupUseCase GetFragmentLineupUseCase { get; }
        [Inject] ExchangeToSelectedItemUseCase ExchangeToSelectedItemUseCase { get; }
        [Inject] CommonReceiveWireFrame CommonReceiveWireFrame { get; }
        [Inject] SelectionFragmentBoxWireFrame WireFrame { get; }
        [Inject] IHomeHeaderDelegate HomeHeaderDelegate { get; }
        [Inject] ActiveItemWireFrame ActiveItemWireFrame { get; }
        [Inject] ActiveItemUseCase ActiveItemUseCase { get; }

        IReadOnlyList<MstItemModel> _lineupItemModelList;
        ItemDetailAvailableLocationViewModel _availableLocationViewModel;

        void ISelectionFragmentBoxViewDelegate.OnViewDidLoad()
        {
            ApplicationLog.Log(nameof(SelectionFragmentBoxPresenter), nameof(ISelectionFragmentBoxViewDelegate.OnViewDidLoad));

            var itemModel = Argument.ItemModel;

            var itemDetailViewModel = ItemViewModelTranslator.ToItemDetailViewModel(itemModel);
            var model = GetFragmentLineupUseCase.GetFragmentLineup(itemModel.Id, Argument.SelectedMstItemId);

            _lineupItemModelList = model.LineupItemModelList;
            _availableLocationViewModel =
                ItemDetailWithTransitViewModelTranslator.ToItemDetailAvailableLocationViewModel(model.AvailableLocationModel);
            var lineupViewModelList = _lineupItemModelList
                .Select(CreateLineupFragmentViewModel)
                .ToList();

            var selectionFragmentBoxViewModel = new SelectionFragmentBoxViewModel(
                itemModel.Id,
                itemDetailViewModel,
                Argument.LimitUseAmount,
                lineupViewModelList,
                _availableLocationViewModel);

            ViewController.Setup(selectionFragmentBoxViewModel);
        }

        void ISelectionFragmentBoxViewDelegate.OnViewDidAppear()
        {
            ApplicationLog.Log(nameof(SelectionFragmentBoxPresenter), nameof(ISelectionFragmentBoxViewDelegate.OnViewDidAppear));

            ViewController.PlayCellAppearanceAnimation();
        }

        void ISelectionFragmentBoxViewDelegate.OnViewDidUnload()
        {
            ApplicationLog.Log(nameof(SelectionFragmentBoxPresenter), nameof(ISelectionFragmentBoxViewDelegate.OnViewDidUnload));
        }

        void ISelectionFragmentBoxViewDelegate.OnCancelSelected()
        {
            WireFrame.OnCloseSelectionFragmentBoxView();
        }

        void ISelectionFragmentBoxViewDelegate.OnUseSelected(MasterDataId selectedItemId, ItemAmount amount)
        {
            if (!ActiveItemUseCase.IsActiveItem(Argument.ItemModel.Id))
            {
                ActiveItemWireFrame.ShowInactiveItemMessage(ViewController, Argument.OnUserItemUpdated);
                return;
            }

            if (selectedItemId.IsEmpty())
            {
                WireFrame.ShowMessageForEmptySelection();
                return;
            }

            ConfirmConsumption(
                selectedItemId,
                amount,
                () => ExchangeItem(Argument.ItemModel.Id, selectedItemId, amount));
        }

        void ConfirmConsumption(MasterDataId selectedItemId, ItemAmount amount, Action onOkSelected)
        {
            var fragmentBox = Argument.ItemModel;

            var fragment = _lineupItemModelList
                .FirstOrDefault(x => x.Id == selectedItemId, MstItemModel.Empty);

            var viewModel = new ExchangeConfirmViewModel(
                fragmentBox.Name,
                ItemIconAssetPath.FromAssetKey(fragmentBox.ItemAssetKey),
                amount,
                fragmentBox.Amount,
                fragmentBox.Amount - amount,
                fragment.Name);

            var argument = new ExchangeConfirmViewController.Argument(
                viewModel,
                onOkSelected,
                () => { });

            WireFrame.ShowConfirmConsumption(argument, ViewController);
        }

        void ISelectionFragmentBoxViewDelegate.OnTapInfoButton()
        {
            WireFrame.OnTapInfoButton(ViewController, _availableLocationViewModel);
        }

        SelectableLineupFragmentViewModel CreateLineupFragmentViewModel(MstItemModel item)
        {
            return new SelectableLineupFragmentViewModel(
                item.Id,
                ItemIconAssetPath.FromAssetKey(item.ItemAssetKey),
                item.Rarity,
                item.Name,
                Argument.SelectedMstItemId == item.Id);
        }

        void ExchangeItem(MasterDataId mstItemId, MasterDataId selectedItemId, ItemAmount amount)
        {
            UniTask<IReadOnlyList<CommonReceiveResourceViewModel>> ExchangeToSelectedItemFunc(CancellationToken cancellationToken)
            {
                var task = UniTask.Create(async () =>
                {
                    var models = await ExchangeToSelectedItemUseCase
                        .ExchangeItem(cancellationToken, mstItemId, selectedItemId, amount);
                    HomeHeaderDelegate.UpdateStatus();

                    var viewModels = models
                        .Select(m =>CommonReceiveResourceViewModelTranslator.TranslateToCommonReceiveViewModel(m))
                        .ToList();
                    return PlayerResourceMerger.MergeCommonReceiveResourceModel(viewModels);
                });
                return task;
            }

            CommonReceiveWireFrame.AsyncShowReceived(ExchangeToSelectedItemFunc, () =>
            {
                WireFrame.OnCloseSelectionFragmentBoxView();
                Argument.OnUserItemUpdated?.Invoke();
                Argument.OnTryReshowView?.Invoke();
            });
        }
    }
}
