using System.Collections.Generic;
using System.Threading;
using Cysharp.Threading.Tasks;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.OutpostEnhance;
using GLOW.Modules.Tutorial.Domain.Definitions;
using GLOW.Modules.Tutorial.Domain.UseCases;
using GLOW.Modules.TutorialTipDialog.Presentation.View;
using GLOW.Scenes.EncyclopediaArtworkDetail.Presentation.Views;
using GLOW.Scenes.Home.Presentation.Interface;
using GLOW.Scenes.Home.Presentation.Views;
using GLOW.Scenes.OutpostEnhance.Domain.UseCases;
using GLOW.Scenes.OutpostEnhance.Presentation.Translator;
using GLOW.Scenes.OutpostEnhance.Presentation.ViewModels;
using GLOW.Scenes.OutpostEnhance.Presentation.Views;
using GLOW.Scenes.OutpostEnhanceLevelUpDialog.Presentation.Views;
using GLOW.Scenes.UnitTab.Domain.UseCase;
using GLOW.Scenes.UnitTab.Presentation.Interface;
using WonderPlanet.UniTaskSupporter;
using WPFramework.Presentation.Modules;
using Zenject;

namespace GLOW.Scenes.OutpostEnhance.Presentation.Presenters
{
    public class OutpostEnhancePresenter : IOutpostEnhanceViewDelegate
    {
        [Inject] OutpostEnhanceViewController ViewController { get; }
        [Inject] GetOutpostEnhanceModelUseCase GetOutpostEnhanceModelUseCase { get; }
        [Inject] OutpostEnhanceUseCase OutpostEnhanceUseCase { get; }
        [Inject] GetOutpostEnhanceArtworkListUseCase GetOutpostEnhanceArtworkListUseCase { get; }
        [Inject] GetCurrentOutpostArtworkUseCase GetCurrentOutpostArtworkUseCase { get; }
        [Inject] ChangeOutpostArtworkUseCase ChangeOutpostArtworkUseCase { get; }
        [Inject] GetOutpostNoticeUseCase GetOutpostNoticeUseCase { get; }
        [Inject] InitializeOutpostArtworkCacheUseCase InitializeOutpostArtworkCacheUseCase { get; }
        [Inject] ApplyUpdatedOutpostArtworkUseCase ApplyUpdatedOutpostArtworkUseCase { get; }
        [Inject] UpdateDisplayedArtworkUseCase UpdateDisplayedArtworkUseCase { get; }
        [Inject] IViewFactory ViewFactory { get; }
        [Inject] IHomeViewNavigation HomeViewNavigation { get; }
        [Inject] IHomeViewDelegate HomeViewDelegate { get; }
        [Inject] IHomeHeaderDelegate HomeHeaderDelegate { get; }
        [Inject] IHomeFooterDelegate HomeFooterDelegate { get; }
        [Inject] IUnitTabDelegate UnitTabDelegate { get; }
        [Inject] TutorialTipDialogViewWireFrame TutorialTipDialogViewWireFrame { get; }
        [Inject] CompleteFreePartTutorialUseCase CompleteFreePartTutorialUseCase { get; }
        [Inject] CheckFreePartTutorialCompletedUseCase CheckFreePartTutorialCompletedUseCase { get; }

        CancellationTokenSource _enhanceEffectAnimationCancellationTokenSource;
        CancellationTokenSource _enhanceWindowAnimationCancellationTokenSource;

        bool _isEnhanceEffectAnimationCompleted;
        bool _isEnhanceWindowAnimationCompleted;
        bool _isShowingArtworkDetail;

        void IOutpostEnhanceViewDelegate.OnViewWillAppear()
        {
            InitializeOutpostArtworkCacheUseCase.InitializeOutpostArtworkCache();
            InitializeOutpostEnhance();
            UpdateOutpostArtwork();
            UpdateNewOutpostArtworkBadge();

            ViewController.HideArtworkList();
            ViewController.HideEnhanceWindow();
            ViewController.PlayEnhanceButtonListCellAppearanceAnimation();

            // 初回遷移時にダイアログ表示をする
            ShowTutorialDialogIfNeed();
        }

        void IOutpostEnhanceViewDelegate.OnViewWillDisappear()
        {
            _enhanceEffectAnimationCancellationTokenSource?.Cancel();
            _enhanceWindowAnimationCancellationTokenSource?.Cancel();
            ApplyUpdatedOutpostArtworkUseCase.AsyncApply();
        }

        void IOutpostEnhanceViewDelegate.OnGateTypeButtonSelected(OutpostEnhanceTypeButtonViewModel buttonViewModel)
        {
            ViewController.SetupOutpostEnhanceWindow(buttonViewModel);
        }

        void IOutpostEnhanceViewDelegate.OnEnhanceButtonSelected(OutpostEnhanceTypeButtonViewModel buttonViewModel)
        {
            var args = new OutpostEnhanceLevelUpDialogViewController.Argument(
                buttonViewModel.EnhanceId,
                (isLevelUp, afterLevel) =>
                {
                    if (isLevelUp && afterLevel != null)
                    {
                        PlayEnhanceAnimation(buttonViewModel, afterLevel);
                    }
                });

            var viewController = ViewFactory.Create<
                OutpostEnhanceLevelUpDialogViewController,
                OutpostEnhanceLevelUpDialogViewController.Argument>(args);

            ViewController.PresentModally(viewController);
        }

        void IOutpostEnhanceViewDelegate.ChangeArtworkSelection(MasterDataId mstArtworkId)
        {
            ChangeOutpostArtworkUseCase.ChangeArtwork(mstArtworkId);

            UpdateOutpostArtwork();
            ViewController.UpdateArtworkSelection(mstArtworkId);
        }

        void IOutpostEnhanceViewDelegate.ShowArtworkDetail(
            MasterDataId mstArtworkId,
            IReadOnlyList<MasterDataId> mstArtworkIds)
        {
            if (_isShowingArtworkDetail) return;

            _isShowingArtworkDetail = true;
            DoAsync.Invoke(ViewController.View, async ct =>
            {
                await ApplyUpdatedOutpostArtworkUseCase.Apply();

                var argument = new EncyclopediaArtworkDetailViewController.Argument(mstArtworkIds, mstArtworkId);

                var viewController = ViewFactory.Create<
                    EncyclopediaArtworkDetailViewController,
                    EncyclopediaArtworkDetailViewController.Argument>(argument);

                HomeViewNavigation.TryPush(viewController, HomeContentDisplayType.BottomOverlap);

                await UniTask.WaitUntil(viewController.View.IsDestroyed, cancellationToken: ct);

                _isShowingArtworkDetail = false;
                // 原画詳細で更新された情報の更新
                UpdateOutpostArtwork();
                ViewController.UpdateArtworkSelection(GetCurrentOutpostArtworkUseCase.GetMstArtworkId());
            });
        }

        void IOutpostEnhanceViewDelegate.ShowArtworkList()
        {
            UpdateArtworkList();
            UpdateDisplayedArtworkUseCase.UpdateDisplayedArtwork();
        }

        void IOutpostEnhanceViewDelegate.ShowEnhanceList()
        {
            ViewController.HideArtworkList();

            HomeFooterDelegate.UpdateBadgeStatus();
            UnitTabDelegate.UpdateTabBadge();
            UpdateNewOutpostArtworkBadge();
        }

        void UpdateArtworkList()
        {
            var model = GetOutpostEnhanceArtworkListUseCase.GetArtworkListModel();
            var viewModel = OutpostEnhanceViewModelTranslator.TranslateArtworkListViewModel(model);
            ViewController.ShowArtworkList(viewModel);
        }

        void PlayEnhanceAnimation(OutpostEnhanceTypeButtonViewModel buttonViewModel, OutpostEnhanceLevel afterValue)
        {
            _enhanceEffectAnimationCancellationTokenSource = new CancellationTokenSource();
            _enhanceWindowAnimationCancellationTokenSource = new CancellationTokenSource();

            DoAsync.Invoke(ViewController.View, async cancellationToken =>
            {
                // touchガード
                ViewController.SetTouchGuard(true);
                ViewController.SetGrayOut(true);
                // バックキーの無効化
                HomeViewDelegate.SetBackKeyEnabled(false);

                // UseCaseを呼んでAPI叩く
                var result = await OutpostEnhanceUseCase.OutpostEnhance(
                    cancellationToken,
                    buttonViewModel.Id,
                    buttonViewModel.EnhanceId,
                    afterValue);

                HomeHeaderDelegate.UpdateStatus();

                var resultViewModel = new OutpostEnhanceResultViewModel(
                    buttonViewModel.Name,
                    result.UserOutpostEnhanceLevelResultModel.BeforeLevel,
                    result.UserOutpostEnhanceLevelResultModel.AfterLevel,
                    buttonViewModel.IconAssetPath,
                    result.UserOutpostEnhanceLevelResultModel.AfterLevel.Value == buttonViewModel.MaxLevel.Value);

                ViewController.HideEnhanceWindow(isArtworkChangeButtonInteractable:false);
                ViewController.SetSkipButtonAction(SkipEnhanceAnimation);
                ViewController.SetGrayOut(false);

                await PlayEnhanceEffectAnimation(cancellationToken);
                await PlayEnhanceWindowAnimation(resultViewModel, cancellationToken);

                ViewController.SetInteractableArtworkChangeButton(true);
            });
        }

        async UniTask PlayEnhanceEffectAnimation(CancellationToken cancellationToken)
        {
            var enhanceEffectAnimationCancellationToken = CancellationTokenSource.CreateLinkedTokenSource(
                cancellationToken, _enhanceEffectAnimationCancellationTokenSource.Token).Token;

            var enhanceEffectAnimationCanceled = await ViewController
                .PlayEnhanceEffectAnimation(enhanceEffectAnimationCancellationToken)
                .SuppressCancellationThrow();

            cancellationToken.ThrowIfCancellationRequested();

            if (enhanceEffectAnimationCanceled)
            {
                _isEnhanceEffectAnimationCompleted = true;
                ViewController.SkipEnhanceEffectAnimation();
            }

            _isEnhanceEffectAnimationCompleted = true;
        }

        async UniTask PlayEnhanceWindowAnimation(OutpostEnhanceResultViewModel model, CancellationToken cancellationToken)
        {
            var enhanceWindowAnimationCancellationToken = CancellationTokenSource.CreateLinkedTokenSource(
                cancellationToken, _enhanceWindowAnimationCancellationTokenSource.Token).Token;

            var enhanceWindowAnimationCanceled = await ViewController
                .PlayEnhanceWindowAnimation(model, enhanceWindowAnimationCancellationToken)
                .SuppressCancellationThrow();

            cancellationToken.ThrowIfCancellationRequested();

            if (enhanceWindowAnimationCanceled)
            {
                _isEnhanceWindowAnimationCompleted = true;
                EndEnhanceAnimation();
            }

            _isEnhanceWindowAnimationCompleted = true;

            EndEnhanceAnimation();
        }

        void EndEnhanceAnimation()
        {
            ViewController.SetTouchGuard(false);
            _enhanceEffectAnimationCancellationTokenSource?.Dispose();
            _enhanceWindowAnimationCancellationTokenSource?.Dispose();
            _enhanceEffectAnimationCancellationTokenSource = null;
            _enhanceWindowAnimationCancellationTokenSource = null;
            _isEnhanceEffectAnimationCompleted = false;
            _isEnhanceWindowAnimationCompleted = false;
            // バックキーの有効化
            HomeViewDelegate.SetBackKeyEnabled(true);

            ViewController.EndAnimation();
            UpdateOutpostEnhance();
        }

        void SkipEnhanceAnimation()
        {
            if (!_isEnhanceEffectAnimationCompleted && !_isEnhanceWindowAnimationCompleted)
            {
                _enhanceEffectAnimationCancellationTokenSource?.Cancel();
            }
            else if (_isEnhanceEffectAnimationCompleted && !_isEnhanceWindowAnimationCompleted)
            {
                _enhanceWindowAnimationCancellationTokenSource?.Cancel();
            }
        }

        void InitializeOutpostEnhance()
        {
            // モデル更新
            var model = GetOutpostEnhanceModelUseCase.GetOutpostEnhanceModel();
            var viewModel = OutpostEnhanceViewModelTranslator.ToOutpostEnhanceViewModel(model);

            // view更新
            ViewController.Setup(viewModel);
            ViewController.SetOutpostHp(viewModel.OutpostHp);
        }

        void UpdateOutpostEnhance()
        {
            // モデル更新
            var model = GetOutpostEnhanceModelUseCase.GetOutpostEnhanceModel();
            var viewModel = OutpostEnhanceViewModelTranslator.ToOutpostEnhanceViewModel(model);

            ViewController.UpdateButtons(viewModel);
            ViewController.SetOutpostHp(viewModel.OutpostHp);
        }

        void UpdateOutpostArtwork()
        {
            var path = GetCurrentOutpostArtworkUseCase.GetArtworkPath();
            ViewController.SetOutpostArtwork(path);
        }

        void UpdateNewOutpostArtworkBadge()
        {
            var notificationBadge = GetOutpostNoticeUseCase.GetUnitNotification();
            ViewController.SetNewOutpostArtworkBadge(notificationBadge);
        }

        void ShowTutorialDialogIfNeed()
        {
            // チュートリアルが完了している場合は早期リターン
            var tutorialFunctionName = TutorialFreePartIdDefinitions.TransitOutpostEnhance;
            if (CheckFreePartTutorialCompletedUseCase.CheckFreePartTutorialCompleted(tutorialFunctionName))
            {
                return;
            }

            DoAsync.Invoke(ViewController.ActualView.destroyCancellationToken, async cancellationToken =>
            {
                // 描画待機のため1f待つ
                await UniTask.DelayFrame(1, cancellationToken: cancellationToken);

                TutorialTipDialogViewWireFrame.ShowTutorialTipDialog(ViewController, tutorialFunctionName);

                await CompleteFreePartTutorialUseCase.CompleteFreePartTutorial(cancellationToken, tutorialFunctionName);
            });
        }
    }
}
