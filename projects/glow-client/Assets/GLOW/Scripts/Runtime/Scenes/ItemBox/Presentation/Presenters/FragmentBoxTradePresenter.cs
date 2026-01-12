using System.Collections.Generic;
using System.Linq;
using System.Threading;
using Cysharp.Threading.Tasks;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Presentation.Modules;
using GLOW.Core.Presentation.Translators;
using GLOW.Core.Presentation.ViewModels;
using GLOW.Modules.CommonReceiveView.Presentation.Presenters;
using GLOW.Modules.CommonReceiveView.Presentation.ViewModel;
using GLOW.Modules.CommonToast.Presentation;
using GLOW.Scenes.Home.Presentation.Interface;
using GLOW.Scenes.ItemBox.Domain.Evaluator;
using GLOW.Scenes.ItemBox.Domain.UseCases;
using GLOW.Scenes.ItemBox.Domain.ValueObjects;
using GLOW.Scenes.ItemBox.Presentation.Translators;
using GLOW.Scenes.ItemBox.Presentation.Views;
using Zenject;

namespace GLOW.Scenes.ItemBox.Presentation.Presenters
{
    public class FragmentBoxTradePresenter : IFragmentBoxTradeViewDelegate
    {
        [Inject] FragmentBoxTradeViewController ViewController { get; }
        [Inject] FragmentBoxTradeViewController.Argument Argument { get; }
        [Inject] FragmentBoxTradeWireFrame FragmentBoxTradeWireFrame { get; }
        [Inject] ShowFragmentBoxTradeUseCase ShowFragmentBoxTradeUseCase { get; }
        [Inject] ConsumeItemUseCase ConsumeItemUseCase { get; }
        [Inject] CommonReceiveWireFrame CommonReceiveWireFrame { get; }
        [Inject] IHomeHeaderDelegate HomeHeaderDelegate { get; }
        [Inject] CheckFragmentBoxTradableUseCase CheckFragmentBoxTradableUseCase { get; }
        [Inject] ActiveItemWireFrame ActiveItemWireFrame { get; }
        [Inject] ActiveItemUseCase ActiveItemUseCase { get; }

        void IFragmentBoxTradeViewDelegate.OnViewDidLoad()
        {
            UpdateFragmentBoxTradeView();
            ViewController.PlayShowAnimation();
        }

        void IFragmentBoxTradeViewDelegate.OnTradeButtonTapped(MasterDataId offerItemId, ItemAmount receiveItemAmount)
        {
            if (!ActiveItemUseCase.IsActiveItem(Argument.FragmentItemModel.Id))
            {
                ActiveItemWireFrame.ShowInactiveItemMessage(ViewController, FragmentBoxTradeWireFrame.OnUserItemUpdated);
                return;
            }

            var tradableStatus = CheckFragmentBoxTradableUseCase.EvaluateFragmentBoxTradableStatus(offerItemId, receiveItemAmount);
            switch (tradableStatus)
            {
                case TradeStatus.ShortageFragment:
                    CommonToastWireFrame.ShowScreenCenterToast("交換するために必要なかけらの数が足りません。");
                    return;
                case TradeStatus.TradeLimit:
                    CommonToastWireFrame.ShowScreenCenterToast("交換できる上限数を超えています。");
                    return;
                default:
                    break;
            }

            UniTask<IReadOnlyList<CommonReceiveResourceViewModel>> ConsumeItemFunc(CancellationToken cancellationToken)
            {
                var task = UniTask.Create(async () =>
                {
                    var models = await ConsumeItemUseCase.ConsumeItem(
                        cancellationToken,
                        offerItemId,
                        receiveItemAmount);

                    // ヘッダー更新
                    HomeHeaderDelegate.UpdateStatus();


                    var viewModels = models
                        .Select(m =>
                            CommonReceiveResourceViewModelTranslator.TranslateToCommonReceiveViewModel(m))
                        .ToList();

                    return PlayerResourceMerger.MergeCommonReceiveResourceModel(viewModels);
                });

                return task;
            }

            CommonReceiveWireFrame.AsyncShowReceived(ConsumeItemFunc, () =>
            {
                // 画面更新
                UpdateFragmentBoxTradeView();

                // アイテムBOX側の情報更新
                FragmentBoxTradeWireFrame.OnUserItemUpdated();
            });
        }

        void IFragmentBoxTradeViewDelegate.OnCancelButtonTapped()
        {
            ViewController.PlayCloseAnimation();
            FragmentBoxTradeWireFrame.CloseFragmentBoxTradeView();
        }

        void IFragmentBoxTradeViewDelegate.OnItemIconTapped(MasterDataId itemId)
        {
            FragmentBoxTradeWireFrame.ShowItemDetailView(itemId, ViewController);
        }

        void UpdateFragmentBoxTradeView()
        {
            var model = ShowFragmentBoxTradeUseCase.GetFragmentBoxTradeModel(Argument.FragmentItemModel);
            var viewModel = FragmentBoxTradeViewModelTranslator.ToFragmentBoxTradeViewModel(model);
            ViewController.SetUpFragmentBoxTradeView(viewModel);
        }
    }
}
