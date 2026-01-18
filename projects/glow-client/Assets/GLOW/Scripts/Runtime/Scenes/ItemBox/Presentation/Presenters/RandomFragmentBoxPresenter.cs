using System.Collections.Generic;
using System.Linq;
using System.Threading;
using Cysharp.Threading.Tasks;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Presentation.Modules;
using GLOW.Core.Presentation.Translators;
using GLOW.Core.Presentation.ViewModels;
using GLOW.Modules.CommonReceiveView.Presentation.Presenters;
using GLOW.Modules.CommonReceiveView.Presentation.ViewModel;
using GLOW.Scenes.FragmentProvisionRatio.Presentation;
using GLOW.Scenes.Home.Presentation.Interface;
using GLOW.Scenes.ItemBox.Domain.Evaluator;
using GLOW.Scenes.ItemBox.Domain.UseCases;
using GLOW.Scenes.ItemBox.Presentation.ViewModels;
using GLOW.Scenes.ItemBox.Presentation.Views;
using GLOW.Scenes.ShopBuyConform.Presentation.View;
using GLOW.Scenes.ShopBuyConform.Presentation.ViewModel;
using WPFramework.Modules.Log;
using Zenject;

namespace GLOW.Scenes.ItemBox.Presentation.Presenters
{
    public class RandomFragmentBoxPresenter : IRandomFragmentBoxViewDelegate
    {
        [Inject] RandomFragmentBoxViewController ViewController { get; }
        [Inject] RandomFragmentBoxViewController.Argument Argument { get; }
        [Inject] ConsumeItemUseCase ConsumeItemUseCase { get; }
        [Inject] GetFragmentLineupUseCase GetFragmentLineupUseCase { get; }
        [Inject] CommonReceiveWireFrame CommonReceiveWireFrame { get; }
        [Inject] RandomFragmentBoxWireFrame WireFrame { get; }
        [Inject] IHomeHeaderDelegate HomeHeaderDelegate { get; }
        [Inject] ActiveItemWireFrame ActiveItemWireFrame { get; }
        [Inject] ActiveItemUseCase ActiveItemUseCase { get; }

        void IRandomFragmentBoxViewDelegate.OnViewDidLoad()
        {
            ApplicationLog.Log(nameof(RandomFragmentBoxPresenter), nameof(IRandomFragmentBoxViewDelegate.OnViewDidLoad));

            WireFrame.RegisterRandomFragmentBoxViewController(ViewController);

            var itemModel = Argument.RandomFragmentBoxItemModel;

            var itemDetailViewModel = ItemViewModelTranslator.ToItemDetailViewModel(itemModel);

            var randomFragmentBoxViewModel = new RandomFragmentBoxViewModel(
                itemModel.Id,
                itemDetailViewModel,
                Argument.LimitUseAmount);

            ViewController.Setup(randomFragmentBoxViewModel);
            ViewController.PlayShowAnimation();
        }

        void IRandomFragmentBoxViewDelegate.OnViewDidUnload()
        {
            ApplicationLog.Log(nameof(RandomFragmentBoxPresenter), nameof(IRandomFragmentBoxViewDelegate.OnViewDidUnload));
            WireFrame.UnregisterRandomFragmentBoxViewController();
        }

        void IRandomFragmentBoxViewDelegate.OnCancelSelected()
        {
            ViewController.PlayCloseAnimation();
            ViewController.Dismiss();
        }

        void IRandomFragmentBoxViewDelegate.OnUseSelected(ItemAmount amount)
        {
            var itemModel = Argument.RandomFragmentBoxItemModel;
            if (!ActiveItemUseCase.IsActiveItem(itemModel.Id))
            {
                ActiveItemWireFrame.ShowInactiveItemMessage(ViewController, Argument.OnUserItemUpdated);
                return;
            }

            var itemDetailViewModel = ItemViewModelTranslator.ToItemDetailViewModel(itemModel);

            var viewModel = new ExchangeConfirmViewModel(
                itemDetailViewModel.Name,
                itemDetailViewModel.ItemIconAssetPath,
                amount,
                itemDetailViewModel.Amount,
                itemDetailViewModel.Amount - amount,
                new ItemName("キャラのかけら"));

            var argument = new ExchangeConfirmViewController.Argument(
                viewModel,
                () => ConsumeItem(itemModel.Id, amount),
                () => { });

            WireFrame.OnUseSelected(argument);
        }

        void IRandomFragmentBoxViewDelegate.OnLineupSelected()
        {
            var argument = new FragmentProvisionRatioViewController.Argument(
                GetFragmentLineupUseCase.GetMstFragmentGroupId(Argument.RandomFragmentBoxItemModel.Id),
                Argument.RandomFragmentBoxItemModel.Id);
            WireFrame.OnProvisionRatio(argument);
        }

        void ConsumeItem(MasterDataId mstItemId, ItemAmount amount)
        {
            UniTask<IReadOnlyList<CommonReceiveResourceViewModel>> ConsumeItemFunc(CancellationToken cancellationToken)
            {
                var task = UniTask.Create(async () =>
                {
                    var models =
                        await ConsumeItemUseCase.ConsumeItem(cancellationToken, mstItemId, amount);

                    // ヘッダー更新
                    HomeHeaderDelegate.UpdateStatus();

                    var rewards = models
                        .Select(m =>
                            CommonReceiveResourceViewModelTranslator.TranslateToCommonReceiveViewModel(m))
                        .ToList();
                    return PlayerResourceMerger.MergeCommonReceiveResourceModel(rewards);
                });
                return task;
            }

            CommonReceiveWireFrame.AsyncShowReceived(ConsumeItemFunc, () =>
            {
                ViewController.Dismiss();
                Argument.OnUserItemUpdated?.Invoke();
                Argument.OnTryReshowView?.Invoke();
            });
        }
    }
}
