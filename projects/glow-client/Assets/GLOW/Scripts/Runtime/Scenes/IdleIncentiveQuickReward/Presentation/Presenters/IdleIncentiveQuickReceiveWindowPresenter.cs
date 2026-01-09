using System.Collections;
using System.Linq;
using System.Threading;
using Cysharp.Threading.Tasks;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.IdleIncentive;
using GLOW.Core.Modules.Advertising;
using GLOW.Core.Modules.Advertising.AdfurikunAgent;
using GLOW.Core.Presentation.Presenters;
using GLOW.Core.Presentation.Translators;
using GLOW.Core.Presentation.Views.InAppAdvertisingConfirmView;
using GLOW.Core.Presentation.Wireframe;
using GLOW.Modules.CommonReceiveView.Presentation.Presenters;
using GLOW.Scenes.DiamondConsumeConfirm.Domain.Enumerable;
using GLOW.Scenes.DiamondConsumeConfirm.Presentation.Views;
using GLOW.Scenes.Home.Presentation.Interface;
using GLOW.Scenes.IdleIncentiveQuickReward.Domain.Moels;
using GLOW.Scenes.IdleIncentiveQuickReward.Domain.UseCases;
using GLOW.Scenes.IdleIncentiveQuickReward.Presentation.ViewModels;
using GLOW.Scenes.IdleIncentiveQuickReward.Presentation.Views;
using GLOW.Scenes.IdleIncentiveTop.Domain.UseCase;
using GLOW.Scenes.IdleIncentiveTop.Presentation.ViewModels;
using GLOW.Scenes.PassShop.Domain.UseCase;
using GLOW.Scenes.PassShop.Presentation.Translator;
using GLOW.Scenes.Shop.Domain.Calculator;
using UnityEngine;
using WonderPlanet.UniTaskSupporter;
using WPFramework.Presentation.InteractionControls;
using WPFramework.Presentation.Modules;
using Zenject;
using AmountFormatter = GLOW.Core.Presentation.Modules.AmountFormatter;

namespace GLOW.Scenes.IdleIncentiveQuickReward.Presentation.Presenters
{
    public class IdleIncentiveQuickReceiveWindowPresenter : IIdleIncentiveQuickReceiveWindowViewDelegate
    {
        [Inject] IdleIncentiveQuickReceiveWindowViewController ViewController { get; }
        [Inject] GetIdleIncentiveQuickReceiveModelUseCase GetIdleIncentiveQuickReceiveModelUseCase { get; }
        [Inject] GetIdleIncentiveRewardUseCase GetIdleIncentiveRewardUseCase { get; }
        [Inject] ReceiveIdleIncentiveRewardUseCase ReceiveRewardUseCase { get; }
        [Inject] UpdateIdleIncentiveAdRewardInfoUseCase UpdateAdRewardInfoUseCase { get; }
        [Inject] CommonReceiveWireFrame CommonReceiveWireFrame { get; }
        [Inject] IViewFactory ViewFactory { get; }
        [Inject] IScreenInteractionControl ScreenInteractionControl { get; }
        [Inject] IHomeHeaderDelegate HomeHeaderDelegate { get; }
        [Inject] InAppAdvertisingWireframe InAppAdvertisingWireframe { get; }
        [Inject] GetHeldAdSkipPassInfoUseCase GetHeldAdSkipPassInfoUseCase { get; }


        ItemAmount _requireDiamond;
        IdleIncentiveRewardListViewModel _quickReceiveWindowViewModel;
        IdleIncentiveRemainCount _adCount;

        public void ViewDidLoad()
        {
            var model = GetIdleIncentiveQuickReceiveModelUseCase.GetModel();
            _requireDiamond = model.RequireDiamondAmount;
            _adCount = model.AdCount;
            var viewModel = Translate(model);
            ViewController.Setup(viewModel);
            ViewController.View.StartCoroutine(AutoUpdateAdQuickReceiveInfo());
        }

        public void ViewDidAppear()
        {
            ViewController.PlayCellAppearanceAnimation();
        }

        public void OnReceiveByAd()
        {
            PlayRewardAd();
        }

        void PlayRewardAd()
        {
            if (GetHeldAdSkipPassInfoUseCase.GetHeldAdSkipPassInfo().IsEmpty())
            {
                //広告表示
                var vc = CreateAdConfirmView();
                ViewController.PresentModally(vc);
            }
            else
            {
                DoAsync.Invoke(ViewController.View.gameObject, ScreenInteractionControl, async cancellationToken =>
                {
                    await ReceiveQuickRewardByAd(cancellationToken);
                });
            }
        }
        InAppAdvertisingConfirmViewController CreateAdConfirmView()
        {
            var vc = ViewFactory.Create<InAppAdvertisingConfirmViewController>();
            vc.SetUp(
                IAARewardFeatureType.IdleIncentive,
                string.Empty, //未使用
                0, //未使用
                _adCount.Value,
                () =>
                {
                    DoAsync.Invoke(ViewController.View.gameObject, ScreenInteractionControl, async cancellationToken =>
                    {
                        var result =
                            await InAppAdvertisingWireframe.ShowAdAsync(IAARewardFeatureType.IdleIncentive, cancellationToken);
                        // 広告キャンセルされたら何もしない
                        if (result == AdResultType.Cancelled) return;

                        await ReceiveQuickRewardByAd(cancellationToken);
                    });
                });

            return vc;
        }

        async UniTask ReceiveQuickRewardByAd(CancellationToken cancellationToken)
        {
            var models = await ReceiveRewardUseCase.ReceiveQuickRewardByAd(cancellationToken);

            HomeHeaderDelegate.UpdateStatus();

            var viewModels = models
                .Select(m => CommonReceiveResourceViewModelTranslator.TranslateToCommonReceiveViewModel(m))
                .ToList();

            CommonReceiveWireFrame.Show(viewModels, onClosed: () => { HomeHeaderDelegate.PlayExpGaugeAnimation(); });
            ViewController.Dismiss();
        }


        public void OnReceiveAtDiamond()
        {
            var argument = new DiamondConsumeConfirmViewController.Argument(
                new TotalDiamond(_requireDiamond.Value),
                ConsumeType.QuickIdleIncentive,
                QuickReceiveAtDiamond,
                () => { });
            var viewController = ViewFactory.Create<
                DiamondConsumeConfirmViewController,
                DiamondConsumeConfirmViewController.Argument>(argument);
            ViewController.PresentModally(viewController);
        }

        void QuickReceiveAtDiamond()
        {
            DoAsync.Invoke(ViewController.View.gameObject, ScreenInteractionControl, async cancellationToken =>
            {
                var models = await ReceiveRewardUseCase.ReceiveQuickRewardByDiamond(cancellationToken);

                HomeHeaderDelegate.UpdateStatus();

                var viewModels = models
                    .Select(m => CommonReceiveResourceViewModelTranslator.TranslateToCommonReceiveViewModel(m))
                    .ToList();
                CommonReceiveWireFrame.Show(viewModels, onClosed: () => { HomeHeaderDelegate.PlayExpGaugeAnimation(); });
                ViewController.Dismiss();
            });
        }

        public void OnClose()
        {
            ViewController.Dismiss();
        }

        IEnumerator AutoUpdateAdQuickReceiveInfo()
        {
            var updateAdRewardInfo = UpdateAdRewardInfoUseCase.CalcReceivableTime();
            var heldAdSkipPassInfoViewModel = HeldAdSkipPassInfoViewModelTranslator.ToHeldAdSkipPassInfoViewModel(
                updateAdRewardInfo.HeldAdSkipPassInfoModel);
            var intervalMinute = updateAdRewardInfo.RemainingTimeAtReceivable;

            while (intervalMinute.HasValue())
            {
                var waitTime = updateAdRewardInfo.RemainingTimeAtReceivable.Value.Milliseconds * 0.001f;
                ViewController.UpdateQuickAdReceiveInterval(
                    AmountFormatter.FormatSecond(intervalMinute),
                    heldAdSkipPassInfoViewModel);
                yield return new WaitForSeconds(waitTime);

                updateAdRewardInfo = UpdateAdRewardInfoUseCase.CalcReceivableTime();
                intervalMinute = updateAdRewardInfo.RemainingTimeAtReceivable;
            }

            ViewController.UpdateQuickAdReceiveInterval(
                null,
                heldAdSkipPassInfoViewModel);
        }


        IdleIncentiveQuickReceiveWindowViewModel Translate(IdleIncentiveQuickReceiveWindowModel model)
        {
            var rewardList = GetIdleIncentiveRewardUseCase.GetRewardList(model.QuickRewardTimeSpan);
            var rewardListViewModel = IdleIncentiveRewardListViewModelTranslator.TranslateRewardList(rewardList);
            _quickReceiveWindowViewModel = rewardListViewModel;
            return new IdleIncentiveQuickReceiveWindowViewModel(
                model.AdCount,
                model.ConsumeItemCount,
                model.RequireDiamondAmount,
                model.IsEnoughRequireItem,
                rewardListViewModel,
                model.QuickRewardTimeSpan,
                HeldAdSkipPassInfoViewModelTranslator.ToHeldAdSkipPassInfoViewModel(model.HeldAdSkipPassInfoModel));
        }
    }
}
