using System;
using System.Collections;
using System.Collections.Generic;
using System.Linq;
using System.Threading;
using Cysharp.Threading.Tasks;
using DG.Tweening;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Presentation.Modules.Audio;
using GLOW.Modules.CommonToast.Presentation;
using GLOW.Modules.InvertMaskView.Presentation.ValueObject;
using GLOW.Modules.Tutorial.Domain.Context;
using GLOW.Scenes.BattleResult.Presentation.ViewModels;
using GLOW.Scenes.GachaList.Presentation.Views;
using GLOW.Scenes.Home.Domain.Constants;
using GLOW.Scenes.Home.Domain.Models;
using GLOW.Scenes.Home.Domain.UseCases;
using GLOW.Scenes.Home.Presentation.Interface;
using GLOW.Scenes.Home.Presentation.ValueObjects;
using GLOW.Scenes.Home.Presentation.ViewModels;
using GLOW.Scenes.QuestContentTop.Presentation;
using GLOW.Scenes.ShopTab.Presentation.View;
using GLOW.Scenes.UnitTab.Domain.Constants;
using GLOW.Scenes.UnitTab.Presentation.Views;
using UIKit;
using UnityEngine;
using WonderPlanet.ResourceManagement;
using WPFramework.Domain.Modules;
using WPFramework.Presentation.Extensions;
using WPFramework.Presentation.Modules;
using Zenject;

namespace GLOW.Scenes.Home.Presentation.Views
{
    public sealed class HomeViewController : UIViewController<HomeView>
        , IHomeViewController
        , IHomeViewNavigation
        , IHomeTapBlock
        , IEscapeResponder
        , IHomeBackgroundControl
    {
        [Inject] IAssetSource AssetSource { get; }
        [Inject] IHomeViewDelegate ViewDelegate { get; }
        [Inject] IHomeHeaderDelegate HomeHeaderViewDelegate { get; }
        [Inject] IHomeFooterDelegate HomeFooterDelegate { get; }
        [Inject] IEscapeResponderRegistry EscapeResponderRegistry { get; }
        [Inject] ISystemSoundEffectProvider SystemSoundEffectProvider { get; }
        [Inject] IViewFactory ViewFactory { get; }
        [Inject] IBackgroundMusicPlayable BackgroundMusicPlayable { get; }
        [Inject] ITutorialPlayingStatus TutorialPlayingStatus { get; }

        ViewContextController _viewContextController;
        public ViewContextController ViewContextController => _viewContextController;
        HomeContentTypes IHomeViewNavigation.CurrentContentType => _viewContextController.CurrentContentType;
        public UIViewController TopViewController => _viewContextController.TopViewController;

        // 画面遷移向け変数
        IEnumerator _runningViewNavigationCoroutine;
        public bool HasRunningViewNavigationCoroutine() => _runningViewNavigationCoroutine != null;

        bool _isEnableEscape = true;

        TutorialViewChangeMonitor _tutorialViewChangeMonitor = new();
        public TutorialViewChangeMonitor TutorialViewChangeMonitor => _tutorialViewChangeMonitor;

        public override void ViewDidLoad()
        {
            base.ViewDidLoad();
            ActualView.InitializeView();

            // NOTE: EscapeResponderRegistryを利用してEscapeキーを押した際に呼び出されるメソッドを登録している
            EscapeResponderRegistry.Bind(this, ActualView);

            // Contextの最初になる画面の作成
            var unitListArgument = new UnitTabViewController.Argument(UnitTabType.UnitList);
            _viewContextController = new ViewContextController(
                new ContextFirstViewController<HomeMainViewController>(
                    HomeContentTypes.Main,
                    ViewFactory.Create<HomeMainViewController>(),
                    HomeContentDisplayType.Default),
                new ContextFirstViewController<UnitTabViewController>(
                    HomeContentTypes.Character,
                    ViewFactory.Create<UnitTabViewController,
                        UnitTabViewController.Argument>(unitListArgument), HomeContentDisplayType.Default),
                new ContextFirstViewController<QuestContentTopViewController>(
                    HomeContentTypes.Content,
                    ViewFactory.Create<QuestContentTopViewController>(),
                    HomeContentDisplayType.Default),
                new ContextFirstViewController<ShopTabViewController>(
                    HomeContentTypes.Shop,
                    ViewFactory.Create<ShopTabViewController>(),
                    HomeContentDisplayType.Default),
                new ContextFirstViewController<GachaListViewController>(
                    HomeContentTypes.Gacha,
                    ViewFactory.Create<GachaListViewController>(),
                    HomeContentDisplayType.Default)
            );

            ViewDelegate.OnViewDidLoad();
        }

        public override void ViewWillAppear(bool animated)
        {
            base.ViewWillAppear(animated);
            ViewDelegate.OnViewWillAppear();
        }

        public override void ViewDidUnload()
        {
            base.ViewDidUnload();
            ViewDelegate.OnViewDidUnload();
        }

        public void Init(
            IReadOnlyList<(UIViewController vc, HomeContentDisplayType showType)> viewControllers,
            HomeContentTypes contentType)
        {
            UpdateCurrentContentType(contentType);
            AddChildControllers(viewControllers);

            var destinationController = viewControllers.Last().vc;
            destinationController.View.Hidden = false;
            _tutorialViewChangeMonitor.SetCurrentViewController(destinationController);

            HomeFooterDelegate.UpdateFooterBalloon();
        }

        void UpdateCurrentContentType(HomeContentTypes type)
        {
            _viewContextController.SetCurrentContentType(type);
            HomeFooterDelegate.UpdateBadgeStatus();
            ActualView.Footer.SetActiveContent(type);
        }

        void UpdateBgm(HomeContentTypes contentType)
        {
            if (contentType == HomeContentTypes.Content)
            {
                BackgroundMusicPlayable.Play(BGMAssetKeyDefinitions.BGM_quest_content_top);
            }
            else
            {
                BackgroundMusicPlayable.Play(BGMAssetKeyDefinitions.BGM_home);
            }
        }

        void IHomeTapBlock.ShowTapBlock(bool shouldShowGrayScale, RectTransform invertMaskRect, float duration)
        {
            ActualView.TapBlock.SetActive(true);
            View.UserInteraction = false;
            if (invertMaskRect != null)
            {
                ActualView.SoftMask.invertMask = true;
                ActualView.SoftMask.separateMask = invertMaskRect;
            }

            ActualView.TapBlockCanvasGroup.alpha = 0f;
            if (shouldShowGrayScale)
            {
                ActualView.TapBlockCanvasGroup
                    .DOFade(1f, duration)
                    // .SetEase(Ease.OutQuad)
                    .Play();
            }
        }

        void IHomeTapBlock.HideTapBlock(bool shouldShowGrayScale, float duration)
        {
            ActualView.TapBlockCanvasGroup.alpha = shouldShowGrayScale ? 1f : 0f;
            ActualView.TapBlockCanvasGroup
                .DOFade(0f, duration)
                // .SetEase(Ease.OutQuad)
                .OnComplete(() =>
                {
                    ActualView.SoftMask.invertMask = false;
                    View.UserInteraction = true;
                    ActualView.TapBlock.SetActive(false);
                })
                .Play();
        }


        public void SetFooterViewModel(HomeFooterViewModel viewModel)
        {
            ActualView.Footer.GachaBadge = viewModel.Gacha.Value;
            ActualView.Footer.ContentBadge = viewModel.Content.Value;
            ActualView.Footer.CharacterBadge = viewModel.Character.Value;
            ActualView.Footer.HomeBadge = viewModel.Home.Value;
            ActualView.Footer.ShopBadge = viewModel.Shop.Value;
        }

        public void SetFooterBalloon(HomeFooterBalloonUseCaseModel model)
        {
            ActualView.Footer.SetBalloons(
                model.IsShowGachaBanner,
                model.IsOpeningAdventBattle,
                model.IsOpeningPvp);
        }

        public void SetHeaderBadge(HomeHeaderBadgeModel model)
        {
            ActualView.Header.SetStaminaBadge(model.StaminaRecoverBadge.Value);
            ActualView.Header.SetAvatarBadge(model.UserAvatarBadge.Value);
            ActualView.Header.SetEmblemBadge(model.UserEmblemBadge.Value);
        }

        public void SetHeaderViewModel(HomeHeaderViewModel homeHeaderViewModel, HomeHeaderStaminaViewModel staminaViewModel)
        {
            ActualView.Header.SetCoin(homeHeaderViewModel.Coin);
            ActualView.Header.SetFreeDiamond(homeHeaderViewModel.FreeDiamond);
            ActualView.Header.SetPaidDiamond(homeHeaderViewModel.PaidDiamond);
            ActualView.Header.SetExp(homeHeaderViewModel.Exp, homeHeaderViewModel.NextExp);
            ActualView.Header.SetLevel(homeHeaderViewModel.Level);
            ActualView.Header.SetStamina(staminaViewModel);
            ActualView.Header.UserNameText = homeHeaderViewModel.UserName;

            if (!homeHeaderViewModel.UserAvatarPath.IsEmpty())
            {
                ActualView.Header.HomeHeaderAvatarImage.SetUp(homeHeaderViewModel.UserAvatarPath.Value);
            }
            else
            {
                ActualView.Header.HomeHeaderAvatarImage.ClearImage();
            }

            if (AssetSource.IsAddressExists(homeHeaderViewModel.EmblemIconAssetPath.Value))
            {
                ActualView.Header.HomeHeaderEmblemImage.SetUp(homeHeaderViewModel.EmblemIconAssetPath.Value);
            }
            else
            {
                ActualView.Header.HomeHeaderEmblemImage.SetDefaultSprite();
            }
        }

        public void SetHeaderStaminaViewModel(HomeHeaderStaminaViewModel viewModel)
        {
            ActualView.Header.SetStamina(viewModel);
        }

        public async UniTask PlayExpGaugeAnimation(
            CancellationToken cancellationToken,
            IReadOnlyList<UserExpGainViewModel> userExpGainViewModels,
            RelativeUserExp currentExp,
            RelativeUserExp nextExp)
        {
            SoundEffectPlayer.Play(SoundEffectId.SSE_053_001);

            for (var i = 0; i < userExpGainViewModels.Count; i++)
            {
                var model = userExpGainViewModels[i];
                await ActualView.Header.PlayExpGaugeAnimation(cancellationToken, model);
                if (model.IsLevelUp)
                {
                    if (userExpGainViewModels.Count <= i + 1) break;

                    ActualView.Header.SetLevel(userExpGainViewModels[i + 1].Level);
                    ActualView.Header.PlayLevelUpTextAnimation();

                    SoundEffectPlayer.Play(SoundEffectId.SSE_053_002);

                    await ActualView.Header.PlayLevelUpEffectAsync(
                        cancellationToken,
                        userExpGainViewModels[i + 1].NextLevelExp.IsZero());
                }
            }

            ActualView.Header.SetExp(currentExp, nextExp);
        }

        public void SetExpGauge(RelativeUserExp currentExp, RelativeUserExp nextExp)
        {
            ActualView.Header.SetExp(currentExp, nextExp);
        }

        public void SetLevel(UserLevel level)
        {
            ActualView.Header.SetLevel(level);
        }

        public void SetFooterActiveContent(HomeContentTypes types)
        {
            ActualView.Footer.SetActiveContent(types);
        }

        public void TapGachaButton()
        {
            ActualView.FooterGachaButton.onClick.Invoke();
        }

        public Vector2 GetGachaInvertMaskPosition()
        {
            var rect = ActualView.FooterGachaButton.GetComponent<RectTransform>();
            var screenPoint = RectTransformUtility.WorldToScreenPoint(Camera.main, rect.position);


            return new Vector2(screenPoint.x, screenPoint.y);
        }

        public InvertMaskSize GetGachaInvertMaskSize()
        {
            var buttonSize = ActualView.FooterGachaButton.GetComponent<RectTransform>().sizeDelta;
            return new InvertMaskSize(buttonSize.x, buttonSize.y);
        }

        public void SetBackKeyEnabled(bool isEnabled)
        {
            _isEnableEscape = isEnabled;
        }

        public void EnableHomeHeaderTap()
        {
            ActualView.Header.EnableTap();
        }

        public void DisableHomeHeaderTap()
        {
            ActualView.Header.DisableTap();
        }

        #region IHomeViewNavigation

        void AddController(UIViewController controller)
        {
            AddChild(controller);
            _viewContextController.AddCurrentStackViewController(controller);
        }

        void RemoveController(UIViewController controller)
        {
            RemoveChild(controller);
            _viewContextController.RemoveCurrentStackViewController(controller);
        }

        void SetTargetParent(UIViewController controller, HomeContentDisplayType homeContentDisplayType)
        {
            if (homeContentDisplayType == HomeContentDisplayType.BottomOverlap)
            {
                controller.View.transform.SetParent(ActualView.BottomOverlapContentRoot, false);
            }
            else if (homeContentDisplayType == HomeContentDisplayType.FullScreenOverlap)
            {
                controller.View.transform.SetParent(ActualView.FullScreenOverlapContentRoot, false);
            }
            else
            {
                controller.View.transform.SetParent(ActualView.ContentRoot, false);
            }
        }

        void IHomeViewNavigation.TryPush(
            UIViewController controller,
            HomeContentDisplayType homeContentDisplayType,
            bool animated,
            Action completion)
        {
            View.StartCoroutine(
                TryStartViewNavigationCoroutine(
                    Push(controller, homeContentDisplayType, animated),
                    completion));
        }

        void IHomeViewNavigation.PushUnmanagedView(
            UIViewController controller,
            HomeContentDisplayType homeContentDisplayType,
            bool animated,
            Action completion)
        {
            AddChild(controller);
            SetTargetParent(controller, homeContentDisplayType);
        }

        IEnumerator Push(
            UIViewController controller,
            HomeContentDisplayType homeContentDisplayType,
            bool animated = true)
        {
            //NOTE: VCを追加して、追加したVCを表示する
            yield return View.StartCoroutine(PushTask(controller, homeContentDisplayType, animated));
            HomeFooterDelegate.UpdateFooterBalloon();
        }

        IEnumerator PushTask(
            UIViewController controller,
            HomeContentDisplayType homeContentDisplayType,
            bool animated)
        {
            UIViewController sourceVc = _viewContextController.TopViewController;
            var destVc = controller;

            AddController(controller);
            SetTargetParent(destVc, homeContentDisplayType);

            yield return StartTransition(sourceVc, destVc, animated, null, null);
        }

        void IHomeViewNavigation.TryPop(bool animated, Action completion)
        {
            View.StartCoroutine(
                TryStartViewNavigationCoroutine(Pop(animated), completion));
        }

        void IHomeViewNavigation.TryPopToRoot(bool animated, Action completion)
        {
            View.StartCoroutine(
                TryStartViewNavigationCoroutine(PopToRoot(animated), completion));
        }

        IEnumerator TryStartViewNavigationCoroutine(IEnumerator coroutine, Action completion = null)
        {
            if (HasRunningViewNavigationCoroutine())
            {
                yield break;
            }

            _runningViewNavigationCoroutine = coroutine;
            yield return View.StartCoroutine(coroutine);
            _runningViewNavigationCoroutine = null;
            // Popのcompletionの中でPushを呼ぶケースがあるので、(Push, Switch含め)画面処理の完了をここで行う
            // ex: GachaListPresenter
            completion?.Invoke();
        }

        IEnumerator Pop(bool animated = true)
        {
            //NOTE: 既存のスタックVCの一番上を削除して、次のVCを表示する
            yield return View.StartCoroutine(PopTask(animated));
            HomeFooterDelegate.UpdateFooterBalloon();
        }

        IEnumerator PopTask(bool animated)
        {
            if (_viewContextController.CurrentStackViewControllers.Count <= 1)
            {
                yield break;
            }

            UIViewController sourceVc = _viewContextController.TopViewController;
            UIViewController destVc = _viewContextController.CurrentStackViewControllers[^2]; //最後から2番目の要素を取得

            RemoveController(sourceVc);

            yield return StartTransition(sourceVc, destVc, animated,
                () =>
                {
                    if (sourceVc != null) sourceVc.UnloadView();
                },
                () => { });
        }

        IEnumerator PopToRoot(bool animated = true)
        {
            //NOTE: スタックしているViewControllerをすべて削除して、最初のViewControllerまで戻る
            yield return View.StartCoroutine(PopToRootTask(animated));
            HomeFooterDelegate.UpdateFooterBalloon();
        }

        IEnumerator PopToRootTask(bool animated)
        {
            if (_viewContextController.CurrentStackViewControllers.Count <= 1)
            {
                yield break;
            }

            UIViewController sourceVc = _viewContextController.TopViewController;
            UIViewController destVc = _viewContextController.CurrentStackViewControllers[0]; // 最初の要素を取得

            // 最初のViewController以外をすべて削除
            var controllersToRemove = _viewContextController.CurrentStackViewControllers
                .Skip(1)
                .ToList();

            foreach (var controller in controllersToRemove)
            {
                RemoveController(controller);
            }

            yield return StartTransition(sourceVc, destVc, animated,
                () =>
                {
                    foreach (var controller in controllersToRemove)
                    {
                        if (controller != null) controller.UnloadView();
                    }
                },
                () => { });
        }

        IEnumerator StartTransition(
            UIViewController sourceVc,
            UIViewController destVc,
            bool animated,
            Action onSourceTransitionComplete,
            Action onDestTransitionComplete)
        {
            yield return Transition(sourceVc, destVc, animated, onSourceTransitionComplete, onDestTransitionComplete);
        }

        IEnumerator Transition(
            UIViewController sourceVc,
            UIViewController destVc,
            bool animated,
            Action onSourceTransitionComplete,
            Action onDestTransitionComplete)
        {
            if (animated) View.UserInteraction = false;

            //NOTE: 消したい画面の非表示処理
            if (sourceVc != null)
            {
                sourceVc.View.Hidden = false;
                // call UIViewController.ViewWillDisappear
                sourceVc.BeginAppearanceTransition(false, animated);
                if (animated)
                {
                    if (sourceVc.View.gameObject.activeInHierarchy)
                        yield return sourceVc.View.GetTransitionSchema().AppearanceTransition(false);
                }

                // call UIViewController.ViewDidDisappear
                sourceVc.EndAppearanceTransition();
                sourceVc.View.Hidden = true;
                onSourceTransitionComplete?.Invoke();
            }

            //NOTE: 表示したい画面の表示処理
            if (destVc != null)
            {
                destVc.View.Hidden = false;
                // call UIViewController.ViewWillAppear
                destVc.BeginAppearanceTransition(true, animated);
                if (animated)
                {
                    //yield returnで待つこともできる
                    if (destVc.View.gameObject.activeInHierarchy)
                        destVc.View.GetTransitionSchema().AppearanceTransition(true);
                }

                // call UIViewController.ViewDidAppear
                destVc.EndAppearanceTransition();
            }

            onDestTransitionComplete?.Invoke(); // 利用されていない

            // NOTE:チュートリアルの画面遷移監視向け処理
            _tutorialViewChangeMonitor.SetCurrentViewController(destVc);


            if (animated) View.UserInteraction = true;
        }

        void IHomeViewNavigation.Switch(HomeContentTypes contentType, bool animated, Action completion)
        {
            var controller = _viewContextController.GetFirstViewControllerAndShowHomeViewType(contentType);
            var list = new List<(UIViewController controller, HomeContentDisplayType viewType)>()
            {
                (controller.vc, controller.viewType)
            };
            View.StartCoroutine(Switch(list, contentType, animated, completion));
        }

        void IHomeViewNavigation.SwitchMultipleViewController(
            IReadOnlyList<(UIViewController controller, HomeContentDisplayType viewType)> controllers,
            HomeContentTypes contentType,
            bool animated,
            Action completion)
        {
            View.StartCoroutine(Switch(controllers, contentType, animated, completion));
        }

        IEnumerator Switch(
            IReadOnlyList<(UIViewController controller, HomeContentDisplayType viewType)> controllers,
            HomeContentTypes contentType,
            bool animated,
            Action completion)
        {
            //NOTE: フッターのボタン状態を切り替えつつ、スタックしているViewControllerを差し替える
            // コンテキストの順番守ること(List.index:0...下画面, List.index:max...上画面)
            UpdateCurrentContentType(contentType);
            UpdateBgm(contentType);
            yield return View.StartCoroutine(TryStartViewNavigationCoroutine(
                SwitchOperation(controllers, animated),
                completion));
            HomeFooterDelegate.UpdateFooterBalloon();
        }

        IEnumerator SwitchOperation(
            IReadOnlyList<(UIViewController vc, HomeContentDisplayType viewType)> controllers,
            bool animated)
        {
            ActualView.UserInteraction = false;

            var sourceController = _viewContextController.TopViewController;
            var shouldRemoveControllers = _viewContextController.CurrentStackViewControllers.ToList();
            _viewContextController.ClearCurrentStackViewControllers();

            //スタックの登録
            AddChildControllers(controllers);

            var destinationController = controllers.Last().vc;
            yield return StartTransition(sourceController, destinationController, animated, null, null);

            foreach (var controller in shouldRemoveControllers)
            {
                _viewContextController.RemoveCurrentStackViewController(controller);
                // 苦しい。もうちょっと良い案がほしい
                if (_viewContextController.IsFirstView(controller))
                {
                    controller.View.Hidden = true;
                }
                else
                {
                    controller.UnloadView();
                }
            }

            ActualView.UserInteraction = true;
        }

        void AddChildControllers(IReadOnlyList<(UIViewController vc, HomeContentDisplayType viewType)> controllers)
        {
            foreach (var controller in controllers)
            {
                AddController(controller.vc);
                SetTargetParent(controller.vc, controller.viewType);

                controller.vc.View.Hidden = true;
            }
        }

        #endregion

        bool IEscapeResponder.OnEscape()
        {
            if (!_isEnableEscape)
            {
                CommonToastWireFrame.ShowInvalidOperationMessage();
                return false;
            }

            if (!View.UserInteraction)
            {
                CommonToastWireFrame.ShowInvalidOperationMessage();
                return true;
            }


            if (_viewContextController.CurrentStackViewControllers.Count <= 1)
            {
                //フッター選択がHome以外の時はHome遷移を行う
                if (ShouldTitleBack())
                {
                    SystemSoundEffectProvider.PlaySeTap();
                    ViewDelegate.OnBackTitle();
                }
                else
                {
                    SystemSoundEffectProvider.PlaySeEscape();
                    ViewDelegate.OnContentSelected(HomeContentTypes.Main);
                }
            }
            else
            {
                SystemSoundEffectProvider.PlaySeEscape();
                View.StartCoroutine(TryStartViewNavigationCoroutine(Pop()));
            }

            return true;
        }

        bool ShouldTitleBack()
        {
            return _viewContextController.CurrentContentType == HomeContentTypes.Main;
        }

        #region IHomeBackgroundControl

        void IHomeBackgroundControl.ShowBasicBackground(BasicHomeBackgroundType type)
        {
            ActualView.Background.SetBasicBackground(type);
        }

        #endregion

        #region UIAction

        [UIAction]
        void OnUnitButtonTapped()
        {
            ViewDelegate.OnContentSelected(HomeContentTypes.Character);
        }

        [UIAction]
        void OnHomeButtonTapped()
        {
            ViewDelegate.OnContentSelected(HomeContentTypes.Main);
        }

        [UIAction]
        void OnContentButtonTapped()
        {
            ViewDelegate.OnContentSelected(HomeContentTypes.Content);
        }

        [UIAction]
        void OnShopButtonTapped()
        {
            ViewDelegate.OnShopButtonTapped();
        }

        [UIAction]
        void OnGachaButtonTapped()
        {
            ViewDelegate.OnGachaButtonTapped();
        }

        [UIAction]
        void OnAvatarTapped()
        {
            ViewDelegate.OnAvatarSelected();
        }

        [UIAction]
        void OnEmblemTapped()
        {
            ViewDelegate.OnEmblemSelected();
        }

        [UIAction]
        void OnStaminaTapped()
        {
            HomeHeaderViewDelegate.OnStaminaRecoverButton();
        }

        [UIAction]
        void OnDiamondTapped()
        {
            ViewDelegate.OnDiamondSelected();
        }

        #endregion
    }
}
