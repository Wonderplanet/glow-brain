using System.Collections.Generic;
using System.Threading;
using Cysharp.Threading.Tasks;
using DG.Tweening;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Presentation.Components;
using GLOW.Scenes.GameModeSelect.Presentation;
using GLOW.Scenes.Home.Presentation.Components;
using GLOW.Scenes.PassShop.Presentation.ViewModel;
using UIKit;
using UnityEngine;
using WPFramework.Presentation.Modules;

namespace GLOW.Scenes.Home.Presentation.Views
{
    public sealed class HomeMainView : UIView
    {
        [Header("クエスト")]
        [SerializeField] HomeMainQuestView _homeMainQuestView;
        [Header("Stageボタン")]
        [SerializeField] UIText _stageConsumeStaminaText;

        [Header("通知バッヂ/左側")]
        [SerializeField] GameObject _dailyMissionBadge;
        [SerializeField] GameObject _beginnerMissionBadge;
        [SerializeField] GameObject _eventMissionBadge;
        [SerializeField] UIObject _encyclopediaBadge;
        [SerializeField] GameObject _idleIncentiveBadge;

        [Header("通知バッヂ/右側")]
        [SerializeField] GameObject _announcementBadge;
        [SerializeField] GameObject _messageBoxBadge;
        [Header("ステージ開放")]
        [SerializeField] ContentsReleaseAnimation _releaseAnimation;
        [SerializeField] RectTransform _invertMaskRect;
        [Header("ステージ挑戦表示")]
        [SerializeField] UIObject _tryStageText;

        [Header("ボタン/左側")]
        [SerializeField] GameObject _beginnerMissionButton;
        [SerializeField] UIObject _eventMissionButton;

        [Header("ボタン/右側")]
        [SerializeField] UIObject _comebackDailyBonusButton;

        [Header("選択パーティ表示")]
        [SerializeField] UIText _currentPartyName;
        [Header("ゲームモード選択")]
        [SerializeField] GameModeSelectView _gameModeselectView;
        [Header("イベントバルーン")]
        [SerializeField] GameObject _eventButton;
        [SerializeField] GameObject _eventBalloon;
        [SerializeField] SeriesLogoComponent _seriesLogoImage;
        [SerializeField] UIImage _eventUnitStandImage;
        [Header("ホームバナー")]
        [SerializeField] UIPageView bannerPageView;
        [Header("Startボタン")]
        [SerializeField] HomeMainSpeedAttackRecord _speedAttackRecord;
        [Header("特別ルール")]
        [SerializeField] GameObject _specialRuleButton;
        [Header("パス適用バナー")]
        [SerializeField] HomeHeldPassBannerComponent _heldPassBannerComponent;
        [Header("スタミナブースト可バルーン")]
        [SerializeField] UIObject _staminaBoostBalloon;
        [SerializeField] UIObject _staminaBoostFirstClearBalloon;

        bool _isTryStageTextVisible;
        bool _isSpecialRuleButtonVisible;
        StaminaBoostBalloonType _staminaBoostBalloonType = StaminaBoostBalloonType.None;
        CancellationTokenSource _overlappingUICancellation;

        public HomeMainQuestView HomeMainQuestView => _homeMainQuestView;
        public GameObject DailyMissionBadge => _dailyMissionBadge;
        public GameObject BeginnerMissionButton => _beginnerMissionButton;
        public UIObject EventMissionButton => _eventMissionButton;
        public GameObject BeginnerMissionBadge => _beginnerMissionBadge;
        public UIObject ComebackDailyBonusButton => _comebackDailyBonusButton;
        public GameObject EventMissionBadge => _eventMissionBadge;
        public UIObject EncyclopediaBadge => _encyclopediaBadge;
        public GameObject IdleIncentiveBadge => _idleIncentiveBadge;
        public GameObject AnnouncementBadge => _announcementBadge;
        public GameObject MessageBoxBadge => _messageBoxBadge;
        public ContentsReleaseAnimation ReleaseAnimation => _releaseAnimation;
        public RectTransform InvertMaskRect => _invertMaskRect;
        public GameModeSelectView GameModeSelectView => _gameModeselectView;
        public UIPageView BannerPageView => bannerPageView;
        public HomeMainSpeedAttackRecord SpeedAttackRecord => _speedAttackRecord;

        public UIText StageConsumeStaminaText => _stageConsumeStaminaText;

        public void InitializeView()
        {
            _releaseAnimation.gameObject.SetActive(false);

            // バッジ非表示
            _dailyMissionBadge.gameObject.SetActive(false);
            _beginnerMissionBadge.gameObject.SetActive(false);
            _eventMissionBadge.gameObject.SetActive(false);
            _encyclopediaBadge.gameObject.SetActive(false);
            _idleIncentiveBadge.gameObject.SetActive(false);
            _announcementBadge.gameObject.SetActive(false);
            _messageBoxBadge.gameObject.SetActive(false);

        }

        public void SetEventButton(EventBalloon eventBalloon)
        {
            _eventButton.SetActive(!eventBalloon.IsEmpty());
            _eventBalloon.SetActive(!eventBalloon.IsEmpty());

            _seriesLogoImage.gameObject.SetActive(!eventBalloon.SeriesLogoImagePath.IsEmpty());
            if (!eventBalloon.SeriesLogoImagePath.IsEmpty())
            {
                _seriesLogoImage.Setup(eventBalloon.SeriesLogoImagePath);
            }

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

        public void SetCurrentPartyName(PartyName partyName)
        {
            _currentPartyName.SetText(partyName.Value);
        }

        public void SetOverlappingUIParameters(
            bool? isSpecialRuleButtonVisible,
            StaminaBoostBalloonType? staminaBoostBalloonType)
        {
            if (isSpecialRuleButtonVisible.HasValue)
            {
                _isSpecialRuleButtonVisible = isSpecialRuleButtonVisible.Value;
            }

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

        void UpdateOverlappingUIDisplay()
        {
            _overlappingUICancellation?.Cancel();
            _overlappingUICancellation?.Dispose();
            _overlappingUICancellation = null;

            // すべてのUIを初期状態にリセット
            ResetOverlappingUI(_tryStageText.gameObject);
            ResetOverlappingUI(_specialRuleButton);
            ResetOverlappingUI(_staminaBoostBalloon.gameObject);
            ResetOverlappingUI(_staminaBoostFirstClearBalloon.gameObject);

            var visibleUIs = new List<GameObject>();

            if (_isTryStageTextVisible)
            {
                visibleUIs.Add(_tryStageText.gameObject);
            }

            if (_isSpecialRuleButtonVisible)
            {
                visibleUIs.Add(_specialRuleButton);
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
