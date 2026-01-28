using System;
using System.Collections.Generic;
using System.Threading;
using Cysharp.Text;
using Cysharp.Threading.Tasks;
using DG.Tweening;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.Stage;
using GLOW.Core.Extensions;
using GLOW.Core.Presentation.Components;
using GLOW.Core.Presentation.CustomCarousel;
using GLOW.Core.Presentation.ViewModels;
using GLOW.Scenes.ArtworkFragment.Domain.ValueObjects;
using GLOW.Scenes.BoxGacha.Domain.ValueObject;
using GLOW.Scenes.EventQuestTop.Presentation.Components;
using GLOW.Scenes.EventQuestTop.Presentation.ViewModels;
using GLOW.Scenes.Home.Presentation.ViewModels;
using GLOW.Scenes.Home.Presentation.Views;
using GLOW.Scenes.InGame.Presentation.Field;
using UIKit;
using UnityEngine;
using UnityEngine.UI;
using Wonderplanet.UIHaptics.Presentation;
using Random = UnityEngine.Random;

namespace GLOW.Scenes.EventQuestTop.Presentation.Views
{
    /// <summary>
    /// 42_イベントステージ
    /// 　42-1_イベントクエスト
    /// 　　42-1-3_イベントクエストステージ選択
    /// </summary>
    public class EventQuestTopView : UIView
    {
        [Header("ヘッダー/タイトル")]
        [SerializeField] UIText _headerText;
        [SerializeField] UIText _questCategoryText;
        [SerializeField] UIText _titleText;
        [Header("原画のかけら")]
        [SerializeField] UIObject _artworkFragmentObj;
        [SerializeField] UIText _artworkFragmentText;
        [Header("背景/キャラ")]
        [SerializeField] EventQuestTopUnitControl _unitControl;
        [SerializeField] UIImage _backgroundImage;
        [Header("イベントミッション")]
        [SerializeField] GameObject _eventMissionBadgeObj;
        [Header("イベント交換所")]
        [SerializeField] GameObject _eventExchangeShopObj;
        [Header("いいジャンくじ")]
        [SerializeField] UIObject _boxGachaButtonObj;
        [SerializeField] UIObject _boxGachaBadgeObj;
        [Header("残り期間")]
        [SerializeField] UIText _remainingTimeText;

        [Space(20)]
        [Header("ステージ選択カルーセル")]
        [SerializeField] GlowCustomInfiniteCarouselView _carouselView;

        [SerializeField] float _maxDistanceMargin = 2.5f;
        [SerializeField] float _cellSizeMargin = 0.3f;

        [Header("ステージ/左右選択ボタン")]
        [SerializeField] Button _leftButton;

        [SerializeField] Button _rightButton;

        [Header("ステージ/開放アニメーション")]
        [SerializeField] ContentsReleaseAnimation _releaseAnimation;

        [SerializeField] RectTransform _invertMaskRect;
        [Header("推奨レベル")]
        [SerializeField] GameObject _recommendedLevelAreaObj;
        [SerializeField] UIText _recommendedLevel;

        [Header("Startボタン")]
        [SerializeField] UIText _stageConsumeStaminaText;
        [SerializeField] HomeMainSpeedAttackRecord _speedAttackRecord;
        [Header("特別ルール")]
        [SerializeField] GameObject _specialRuleButton;
        [Header("編成/選択パーティ")]
        [SerializeField] UIText _currentPartyName;
        [Header("キャンペーン")]
        [SerializeField] CampaignBalloonMultiSwitcherComponent _campaignBalloonMultiSwitcherComponent;
        [Header("スタミナブースト")]
        [SerializeField] UIObject _staminaBoostBalloon;
        [SerializeField] UIObject _staminaBoostFirstClearBalloon;

        bool _isSpecialRuleButtonVisible;
        StaminaBoostBalloonType _staminaBoostBalloonType = StaminaBoostBalloonType.None;
        CancellationTokenSource _overlappingUICancellation;

        public ContentsReleaseAnimation ReleaseAnimation => _releaseAnimation;
        public RectTransform InvertMaskRect => _invertMaskRect;

        const string ArtworkFragmentTextFormat = "{0} / {1}";

        const float BackGroundBaseHeight = 800;   // プレハブレイアウトの関係で、800を基準にサイズを変更する

        public GlowCustomInfiniteCarouselView CarouselView => _carouselView;
        public RectTransform CarouselViewRect => _carouselView.RectTransform;
        public float MaxDistanceMargin => _maxDistanceMargin;
        public float CellSizeMargin => _cellSizeMargin;

        public void InitializeCarousel(
            IGlowCustomCarouselViewDataSource dataSource,
            IGlowCustomCarouselViewDelegate viewDelegate,
            IHapticsPresenter hapticsPresenter)
        {
            _carouselView.DataSource = dataSource;
            _carouselView.ViewDelegate = viewDelegate;
            _carouselView.HapticsPresenter = hapticsPresenter;
        }

        public void ReloadCarouselData()
        {
            _carouselView.ReloadData();
        }

        public EventQuestTopStageCell DequeueReusableCell()
        {
            return _carouselView.DequeueReusableCell<EventQuestTopStageCell>();
        }

        public bool OnMoveButtonIfNeed(CarouselDirection direction, int numberOfItems)
        {
            if (direction == CarouselDirection.Right)
            {
                //indexを見るのに対して、NumberOfItemsはCountであることに注意
                return _carouselView.CurrentIndex + 1 <= (numberOfItems - 1);
            }
            else
            {
                return 0 <= _carouselView.CurrentIndex - 1;
            }
        }
        public void MoveLeft()
        {
            if (_carouselView != null)
            {
                _carouselView.MoveLeft();
            }
        }

        public void MoveRight()
        {
            if (_carouselView != null)
            {
                _carouselView.MoveRight();
            }
        }

        public void SetMissionBadge(bool isActive)
        {
            _eventMissionBadgeObj.SetActive(isActive);
        }
        
        public void SetBoxGachaBadge(BoxGachaDrawableFlag isBoxGachaDrawable)
        {
            _boxGachaBadgeObj.IsVisible = isBoxGachaDrawable;
        }

        public void SetUpView(EventQuestTopViewModel viewModel)
        {
            // ヘッダー
            _headerText.SetText(viewModel.EventName.Value);
            // タイトル
            _questCategoryText.SetText(viewModel.QuestCategoryName.Value);
            _titleText.SetText(viewModel.QuestName.Value);
            // 原画のかけら
            SetUpArtworkFragmentText(viewModel.AcquiredArtworkFragmentNum, viewModel.GettableArtworkFragmentNum);
            // 残り時間
            _remainingTimeText.SetText(viewModel.RemainingTimeText);
            // キャンペーン
            SetUpCampaignBalloons(viewModel.CampaignViewModels);
            // いいジャンくじ
            _boxGachaButtonObj.IsVisible = !viewModel.MstBoxGachaId.IsEmpty();
        }

        public void SetEventExchangeShopButton(bool isActive)
        {
            _eventExchangeShopObj.SetActive(isActive);
        }

        public void SetUpBackGround(Sprite background)
        {
            if (background == null || background.rect.height <= 0) return;

            _backgroundImage.gameObject.SetActive(true);
            _backgroundImage.Image.sprite = background;

            float normalizeScale = 1 / (background.rect.height / BackGroundBaseHeight);
            _backgroundImage.RectTransform.localScale = new Vector3(normalizeScale, normalizeScale, 1);
        }

        void SetUpArtworkFragmentText(ArtworkFragmentNum acquired, ArtworkFragmentNum gettable)
        {
            var isGettable = !gettable.IsZero();
            _artworkFragmentObj.Hidden = !isGettable;
            if (isGettable)
            {
                _artworkFragmentText.SetText(ZString.Format(ArtworkFragmentTextFormat, acquired.Value, gettable.Value));
            }
        }

        public void SetUpUnits(IReadOnlyList<UnitImage> playerUnits)
        {
            _unitControl.Initialize(playerUnits);
        }

        public async UniTask TapUnit(int unitIndex, EventQuestTopUnitViewModel viewModel, float viewTime,CancellationToken ct)
        {
            if(viewModel.SpeechBalloonTexts.IsEmpty()) return;
            if (_unitControl.IsUnitActionPlaying(unitIndex)) return;

            _unitControl.SetUnitActionPlaying(unitIndex, true);
            _unitControl.UpdateAnimation(unitIndex);

            //吹き出し表示
            var index = 2 <= viewModel.SpeechBalloonTexts.Count
                ? Random.Range(0, viewModel.SpeechBalloonTexts.Count - 1)
                : 0;
            var balloonViewModel = viewModel.SpeechBalloonTexts[index];
            await _unitControl.CreateSpeechBalloon(
                unitIndex,
                balloonViewModel,
                viewTime,
                ct);

            _unitControl.SetUnitActionPlaying(unitIndex, false);
        }

        public void SetUpStageSelect(StageRecommendedLevel level, bool isLeftActive, bool isRightActive, bool isVisible)
        {
            if(isVisible)
            {
                var format = "<size=16>推奨キャラ</size><size=18>Lv.{0}</size>";
                _recommendedLevel.SetText(format, level.Value);
            }
            _leftButton.gameObject.SetActive(isLeftActive);
            _rightButton.gameObject.SetActive(isRightActive);
            _recommendedLevelAreaObj.SetActive(isVisible);
        }

        public void SetStageConsumeStaminaText(EventQuestTopElementViewModel cell)
        {
            if (cell.DailyPlayableCount.IsEmpty())
            {
                _stageConsumeStaminaText.SetText("×{0}", cell.StageConsumeStamina.Value);
            }
            else
            {
                var format = "×{0} あと<color=red>{1}回</color>挑戦可能";
                _stageConsumeStaminaText.SetText(
                    format,
                    cell.StageConsumeStamina.Value,
                    cell.DailyPlayableCount.Value - cell.DailyClearCount.Value);
            }
        }

        public void SetUpSpeedAttackRecord(SpeedAttackViewModel viewModel)
        {
            _speedAttackRecord.Hidden = viewModel.IsEmpty();
            _speedAttackRecord.Setup(viewModel.ClearTimeMs, viewModel.NextGoalTime);
        }

        public void SetVisibleSpecialRuleButton(bool isVisible)
        {
            SetOverlappingUIParameters(isVisible, null);
        }

        public void SetStaminaBoostBalloon(StaminaBoostBalloonType staminaBoostBalloonType)
        {
            SetOverlappingUIParameters(null, staminaBoostBalloonType);
        }

        public void SetCurrentPartyName(PartyName partyName)
        {
            _currentPartyName.SetText(partyName.Value);
        }

        void SetUpCampaignBalloons(IReadOnlyList<CampaignViewModel> viewModels)
        {
            _campaignBalloonMultiSwitcherComponent.SetUpCampaignBalloons(viewModels);
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

        void UpdateOverlappingUIDisplay()
        {
            _overlappingUICancellation?.Cancel();
            _overlappingUICancellation?.Dispose();
            _overlappingUICancellation = null;

            // すべてのUIを初期状態にリセット
            ResetOverlappingUI(_specialRuleButton);
            ResetOverlappingUI(_staminaBoostBalloon.gameObject);
            ResetOverlappingUI(_staminaBoostFirstClearBalloon.gameObject);

            var visibleUIs = new List<GameObject>();

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
                var targetCanvasGroup = visibleUIs[0].GetComponent<CanvasGroup>();
                targetCanvasGroup.alpha = 1f;
                visibleUIs[0].SetActive(true);
                return;
            }

            _overlappingUICancellation = new CancellationTokenSource();
            RotateOverlappingUIAsync(visibleUIs, _overlappingUICancellation.Token).Forget();
        }

        void ResetOverlappingUI(GameObject uiObject)
        {
            var targetCanvasGroup = uiObject.GetComponent<CanvasGroup>();
            targetCanvasGroup.DOKill();
            targetCanvasGroup.alpha = 0f;
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
