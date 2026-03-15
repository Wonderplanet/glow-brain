using System.Collections.Generic;
using System.Threading;
using Cysharp.Threading.Tasks;
using DG.Tweening;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Presentation.Components;
using GLOW.Scenes.GameModeSelect.Presentation;
using GLOW.Scenes.Home.Presentation.Components;
using GLOW.Scenes.Home.Presentation.ViewModels;
using GLOW.Scenes.Home.Presentation.Views.HomeMainKomaSetting;
using GLOW.Scenes.PassShop.Presentation.ViewModel;
using UIKit;
using UnityEngine;
using UnityEngine.UI;
using WPFramework.Presentation.Modules;

namespace GLOW.Scenes.Home.Presentation.Views
{
    public sealed class HomeMainView : UIView
    {
        [Header("前面ボタン/通知バッジ")]
        [SerializeField] GameObject _idleIncentiveBadge;
        [Header("メニューボタン/通知バッジ")]
        [SerializeField] GameObject _dailyMissionBadge;
        [SerializeField] GameObject _beginnerMissionBadge;
        [SerializeField] GameObject _eventMissionBadge;
        [SerializeField] GameObject _artworkPanelMissionBadge;
        [SerializeField] UIObject _encyclopediaBadge;
        [SerializeField] GameObject _announcementBadge;
        [SerializeField] GameObject _messageBoxBadge;
        [Header("メニューボタン")]
        [SerializeField] GameObject _beginnerMissionButton;
        [SerializeField] UIObject _eventMissionButton;
        [SerializeField] UIObject _comebackDailyBonusButton;
        [SerializeField] UIObject _artworkPanelMissionButton;

        [Header("ステージ挑戦表示")]
        [SerializeField] UIObject _tryStageText;

        [Header("pvpボタン")]
        [SerializeField] Button _pvpButton;
        [SerializeField] GameObject _pvpButtonGrayout;
        [SerializeField] GameObject _pvpButtonBadge;
        [Header("イベントバルーン")]
        [SerializeField] GameObject _eventButton;
        [SerializeField] GameObject _eventBalloon;
        [SerializeField] UIImage _eventUnitStandImage;
        [Header("ホームバナー")]
        [SerializeField] UIPageView bannerPageView;
        [Header("パス適用バナー")]
        [SerializeField] HomeHeldPassBannerComponent _heldPassBannerComponent;
        [Header("スタミナブースト可バルーン")]
        [SerializeField] UIObject _staminaBoostBalloon;
        [SerializeField] UIObject _staminaBoostFirstClearBalloon;
        [Header("コマ")]
        [SerializeField] RectTransform _homeMainKomaAreaRect;

        [Header("メニューボタン")]
        [SerializeField] GameObject _openingMenuButtonObj;
        [SerializeField] Animator _openingMenuAnimator;
        [SerializeField] GameObject _closingMenuButtonObj;

        const string OpenMenuAnimationName = "MenuBtnGroup_in";
        const string CloseMenuAnimationName = "MenuBtnGroup_out";

        bool _isTryStageTextVisible;
        StaminaBoostBalloonType _staminaBoostBalloonType = StaminaBoostBalloonType.None;
        HomeMainKomaPatternComponent _homeMainKomaPatternComponent;

        CancellationTokenSource _overlappingUICancellation;
        bool _isVisibleMenuRunning;

        public GameObject DailyMissionBadge => _dailyMissionBadge;
        public GameObject BeginnerMissionButton => _beginnerMissionButton;
        public UIObject EventMissionButton => _eventMissionButton;
        public UIObject ArtworkPanelMissionButton => _artworkPanelMissionButton;
        public GameObject BeginnerMissionBadge => _beginnerMissionBadge;
        public GameObject ArtworkPanelMissionBadge => _artworkPanelMissionBadge;
        public UIObject ComebackDailyBonusButton => _comebackDailyBonusButton;
        public GameObject EventMissionBadge => _eventMissionBadge;
        public UIObject EncyclopediaBadge => _encyclopediaBadge;
        public GameObject IdleIncentiveBadge => _idleIncentiveBadge;
        public GameObject AnnouncementBadge => _announcementBadge;
        public GameObject MessageBoxBadge => _messageBoxBadge;
        public Button PvpButton => _pvpButton;
        public GameObject PvpButtonGrayout => _pvpButtonGrayout;
        public GameObject PvpButtonBadge => _pvpButtonBadge;
        public UIPageView BannerPageView => bannerPageView;
        public RectTransform HomeMainKomaAreaRect => _homeMainKomaAreaRect;

        public void InitializeView()
        {
            // バッジ非表示
            _dailyMissionBadge.gameObject.SetActive(false);
            _beginnerMissionBadge.gameObject.SetActive(false);
            _eventMissionBadge.gameObject.SetActive(false);
            _encyclopediaBadge.gameObject.SetActive(false);
            _idleIncentiveBadge.gameObject.SetActive(false);
            _announcementBadge.gameObject.SetActive(false);
            _messageBoxBadge.gameObject.SetActive(false);

            // メニューボタン初期化
            _openingMenuButtonObj.SetActive(false);
            _closingMenuButtonObj.SetActive(true);
        }

        public void SetEventButton(EventBalloon eventBalloon)
        {
            _eventButton.SetActive(!eventBalloon.IsEmpty());
            _eventBalloon.SetActive(!eventBalloon.IsEmpty());

            _eventUnitStandImage.gameObject.SetActive(!eventBalloon.EventUnitStandImageAssetPath.IsEmpty());
            if (!eventBalloon.EventUnitStandImageAssetPath.IsEmpty())
            {
                UISpriteUtil.LoadSpriteWithFadeIfNotLoaded(
                    _eventUnitStandImage.Image,
                    eventBalloon.EventUnitStandImageAssetPath.Value);
            }
        }

        public void SetUpHeldPassBanners(IReadOnlyList<HeldPassEffectDisplayViewModel> viewModels)
        {
            _heldPassBannerComponent.SetUp(viewModels);
        }

        public void SetOverlappingUIParameters(
            bool? isSpecialRuleButtonVisible,
            StaminaBoostBalloonType? staminaBoostBalloonType)
        {
            if (staminaBoostBalloonType.HasValue)
            {
                _staminaBoostBalloonType = staminaBoostBalloonType.Value;
            }

            UpdateOverlappingUIDisplay();
        }

        public void SetVisibleTryStageText(bool isVisible)
        {
            _isTryStageTextVisible = isVisible;
        }


        public void SetHomeMainKomaPattern(
            GameObject patternComponent,
            IReadOnlyList<HomeMainKomaUnitViewModel> unitAssetPaths)
        {
            if(_homeMainKomaPatternComponent != null)
            {
                Destroy(_homeMainKomaPatternComponent.gameObject);
                _homeMainKomaPatternComponent = null;
            }

            _homeMainKomaPatternComponent =
                Instantiate(patternComponent, _homeMainKomaAreaRect)
                    .GetComponent<HomeMainKomaPatternComponent>();

            _homeMainKomaPatternComponent.InitializeView();
            _homeMainKomaPatternComponent.Setup(unitAssetPaths, false);
            _homeMainKomaPatternComponent.PlayShowAnimation();
        }

        public async UniTask VisibleMenuAsync(bool isVisible, CancellationToken cancellationToken)
        {
            // 既に実行中のタスクがある場合は早期return(連打対策)
            if (_isVisibleMenuRunning)
            {
                return;
            }

            _isVisibleMenuRunning = true;
            await VisibleMenuAsyncCore(isVisible, cancellationToken);

            _isVisibleMenuRunning = false;
        }

        async UniTask VisibleMenuAsyncCore(bool isVisible, CancellationToken cancellationToken)
        {
            if (isVisible)
            {
                // 表示して・再生
                _closingMenuButtonObj.SetActive(false);
                _openingMenuButtonObj.SetActive(true);
                _openingMenuAnimator.Play(OpenMenuAnimationName, 0, 0);
                // アニメーション完了まで待つ
                await UniTask.WaitUntil(() =>
                    _openingMenuAnimator.GetCurrentAnimatorStateInfo(0).normalizedTime >= 1.0f,
                    cancellationToken: cancellationToken);
            }
            else
            {
                // 再生して・非表示
                _openingMenuAnimator.Play(CloseMenuAnimationName, 0, 0);
                // アニメーション完了まで待つ
                await UniTask.WaitUntil(() =>
                    _openingMenuAnimator.GetCurrentAnimatorStateInfo(0).normalizedTime >= 1.0f,
                    cancellationToken: cancellationToken);
                _closingMenuButtonObj.SetActive(true);
                _openingMenuButtonObj.SetActive(false);
            }
        }


        void UpdateOverlappingUIDisplay()
        {
            _overlappingUICancellation?.Cancel();
            _overlappingUICancellation?.Dispose();
            _overlappingUICancellation = null;

            // すべてのUIを初期状態にリセット
            ResetOverlappingUI(_tryStageText.gameObject);
            ResetOverlappingUI(_staminaBoostBalloon.gameObject);
            ResetOverlappingUI(_staminaBoostFirstClearBalloon.gameObject);

            var visibleUIs = new List<GameObject>();

            if (_isTryStageTextVisible)
            {
                visibleUIs.Add(_tryStageText.gameObject);
            }

            if (_staminaBoostBalloonType == StaminaBoostBalloonType.DefaultBalloon)
            {
                visibleUIs.Add(_staminaBoostBalloon.gameObject);
            }

            if (_staminaBoostBalloonType == StaminaBoostBalloonType.FirstClearBalloon)
            {
                visibleUIs.Add(_staminaBoostFirstClearBalloon.gameObject);
            }

            if (visibleUIs.Count == 0)
            {
                return;
            }

            if (visibleUIs.Count == 1)
            {
                var canvasGroup = visibleUIs[0].GetComponent<CanvasGroup>();
                canvasGroup.alpha = 1f;
                visibleUIs[0].SetActive(true);
                return;
            }

            _overlappingUICancellation = new CancellationTokenSource();
            RotateOverlappingUIAsync(visibleUIs, _overlappingUICancellation.Token).Forget();
        }

        void ResetOverlappingUI(GameObject uiObject)
        {
            var canvasGroup = uiObject.GetComponent<CanvasGroup>();
            canvasGroup.DOKill();
            canvasGroup.alpha = 0f;
            uiObject.SetActive(false);
        }

        async UniTaskVoid RotateOverlappingUIAsync(List<GameObject> visibleUIs, CancellationToken cancellationToken)
        {
            const float displayDuration = 3f;
            const float fadeDuration = 0.5f;

            var currentIndex = 0;
            var isFirstDisplay = true;

            try
            {
                while (!cancellationToken.IsCancellationRequested)
                {
                    var currentUI = visibleUIs[currentIndex];
                    currentUI.SetActive(true);

                    var targetCanvasGroup = currentUI.GetComponent<CanvasGroup>();
                    await FadeInOverlappingUI(targetCanvasGroup, isFirstDisplay, fadeDuration, cancellationToken);
                    isFirstDisplay = false;

                    await UniTask.Delay((int)(displayDuration * 1000), cancellationToken: cancellationToken);

                    await targetCanvasGroup
                        .DOFade(0f, fadeDuration)
                        .SetEase(Ease.InOutQuad)
                        .ToUniTask(cancellationToken: cancellationToken);

                    currentUI.SetActive(false);

                    currentIndex = (currentIndex + 1) % visibleUIs.Count;
                }
            }
            catch (System.OperationCanceledException)
            {
                // キャンセル時は何もしない（UpdateOverlappingUIDisplayでリセット済み）
            }
        }

        async UniTask FadeInOverlappingUI(
            CanvasGroup targetCanvasGroup,
            bool isFirstDisplay,
            float fadeDuration,
            CancellationToken cancellationToken)
        {
            if (isFirstDisplay)
            {
                // 最初はパッと表示
                targetCanvasGroup.alpha = 1f;
            }
            else
            {
                // 2回目以降はフェードイン
                targetCanvasGroup.alpha = 0f;

                await targetCanvasGroup
                    .DOFade(1f, fadeDuration)
                    .SetEase(Ease.InOutQuad)
                    .ToUniTask(cancellationToken: cancellationToken);
            }
        }

        protected override void OnDestroy()
        {
            base.OnDestroy();
            _overlappingUICancellation?.Cancel();
            _overlappingUICancellation?.Dispose();
        }
    }
}
