using System.Collections.Generic;
using DG.Tweening;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.Quest;
using GLOW.Core.Domain.ValueObjects.Stage;
using GLOW.Core.Presentation.Components;
using GLOW.Core.Presentation.CustomCarousel;
using GLOW.Core.Presentation.ViewModels;
using GLOW.Scenes.EventQuestTop.Presentation.Components;
using GLOW.Scenes.Home.Presentation.ViewModels;
using GLOW.Scenes.Home.Presentation.Views;
using GLOW.Scenes.QuestContentTop.Domain.ValueObject;
using GLOW.Scenes.QuestSelect.Presentation.QuestDifficultySelect.Component;
using UIKit;
using UnityEngine;
using Wonderplanet.UIHaptics.Presentation;
using WPFramework.Presentation.Modules;

namespace GLOW.Scenes.MainQuestTop.Presentation
{
    public class MainQuestTopView : UIView
    {
        [Header("ヘッダー/タイトル")]
        [SerializeField] UIText _headerText;
        [Header("扉絵/カルーセル")]
        [SerializeField] HomeMainQuestView _homeMainQuestView;
        [Header("難易度ボタンリスト")]
        [SerializeField] QuestDifficultyButtonListComponent _difficultyButtonListComponent;
        [Header("難易度ボタンリスト/キャンペーン")]
        [SerializeField] CampaignBalloonMultiSwitcherComponent _normalCampaignBalloonSwitcher;
        [SerializeField] CampaignBalloonMultiSwitcherComponent _hardCampaignBalloonSwitcher;
        [SerializeField] CampaignBalloonMultiSwitcherComponent _extraCampaignBalloonSwitcher;
        [Space(20)]
        [Header("ステージ/開放アニメーション")]
        [SerializeField] ContentsReleaseAnimation _releaseAnimation;
        [SerializeField] RectTransform _invertMaskRect;

        [Header("編成・Start/ボタン")]
        [SerializeField] UIText _stageConsumeStaminaText;
        [Header("編成・Start/スピードアタック記録")]
        [SerializeField] HomeMainSpeedAttackRecord _speedAttackRecord;
        [Header("編成・Start/選択パーティ")]
        [SerializeField] UIText _currentPartyName;
        [Header("編成・Start/スタミナブースト")]
        [SerializeField] BattleStartButtonOverlappingUIComponent overlappingUIComponent;
        [SerializeField] UIObject _staminaBoostBalloon;
        [SerializeField] UIObject _staminaBoostFirstClearBalloon;
        [Header("クエスト情報/ぼかし背景")]
        [SerializeField] UIImage _questThumbnailBackGroundImage;
        [SerializeField] CanvasGroup _questThumbnailLoadCanvasGroup;
        [SerializeField] UILightBlurTextureComponent _uiLightBlurTextureComponent;


        public ContentsReleaseAnimation ReleaseAnimation => _releaseAnimation;
        public RectTransform InvertMaskRect => _invertMaskRect;

        public HomeMainQuestView HomeMainQuestView => _homeMainQuestView;

        public BattleStartButtonOverlappingUIComponent OverlappingUIComponent => overlappingUIComponent;

        // 難易度
        public QuestDifficultyButtonListComponent DifficultyButtonListComponent => _difficultyButtonListComponent;
        public CampaignBalloonMultiSwitcherComponent NormalCampaignBalloonSwitcher => _normalCampaignBalloonSwitcher;
        public CampaignBalloonMultiSwitcherComponent HardCampaignBalloonSwitcher => _hardCampaignBalloonSwitcher;
        public CampaignBalloonMultiSwitcherComponent ExtraCampaignBalloonSwitcher => _extraCampaignBalloonSwitcher;


        // チュートリアル用
        public bool CloseQuestReleaseAnimation { get; set; }


        public void InitializeView()
        {
            _speedAttackRecord.Hidden = true;
            overlappingUIComponent.gameObject.SetActive(false);

            _homeMainQuestView.SetActiveCampaignBalloon(false);
            _homeMainQuestView.SetupQuestTimeLimit(QuestLimitTime.Empty);
            _homeMainQuestView.SetRecommendedLevel(0, false);
            _homeMainQuestView.QuestLoadImageCanvasGroup.alpha = 1;
            _questThumbnailLoadCanvasGroup.alpha = 1;
        }

        public void InitializeCarousel(
            IGlowCustomCarouselViewDataSource dataSource,
            IGlowCustomCarouselViewDelegate viewDelegate,
            IHapticsPresenter hapticsPresenter)
        {
            _homeMainQuestView.CarouselView.DataSource = dataSource;
            _homeMainQuestView.CarouselView.ViewDelegate = viewDelegate;
            _homeMainQuestView.CarouselView.HapticsPresenter = hapticsPresenter;
        }

        public void ReloadCarouselData()
        {
            _homeMainQuestView.CarouselView.ReloadData();
        }

        public EventQuestTopStageCell DequeueReusableCell()
        {
            return _homeMainQuestView.CarouselView.DequeueReusableCell<EventQuestTopStageCell>();
        }


        public void SetUpView(QuestName questName, IReadOnlyList<CampaignViewModel> campaignViewModels)
        {
            // ヘッダー
            _headerText.SetText(questName.Value);
            // キャンペーン
            SetUpCampaignBalloons(campaignViewModels);
        }

        public void SetUpStageSelect(
            StageRecommendedLevel level,
            bool recommendedVisible,
            bool isLeftButtonActive,
            bool isRightButtonActive,
            StageConsumeStamina consumeStamina,
            ClearableCount dailyPlayableCount,
            StageClearCount dailyClearCount,
            bool? isSpecialRuleButtonVisible,
            StaminaBoostBalloonType? staminaBoostBalloonType,
            SpeedAttackViewModel speedAttackViewModel,
            IReadOnlyList<CampaignViewModel> campaignViewModel)
        {
            // 推奨レベル
            _homeMainQuestView.SetRecommendedLevel(level.Value, recommendedVisible);

            // 左右ボタン
            _homeMainQuestView.LeftButton.gameObject.SetActive(isLeftButtonActive);
            _homeMainQuestView.RightButton.gameObject.SetActive(isRightButtonActive);

            // スタートボタン
            if (dailyPlayableCount.IsEmpty())
            {
                _stageConsumeStaminaText.SetText("×{0}", consumeStamina.Value);
            }
            else
            {
                var format = "×{0} あと<color=red>{1}回</color>挑戦可能";
                _stageConsumeStaminaText.SetText(
                    format,
                    consumeStamina.Value,
                    dailyPlayableCount.Value - dailyClearCount.Value);
            }

            // ローテションバルーン設定
            // 特別ルールボタン
            // スタミナブーストバルーン
            // 順番依存1
            overlappingUIComponent.gameObject.SetActive(true);
            overlappingUIComponent.SetOverlappingUIParameters(
                isSpecialRuleButtonVisible,
                staminaBoostBalloonType);
            // 順番依存2, 3
            overlappingUIComponent.InitializeOverlappingUIDisplayAnimation();
            overlappingUIComponent.StartRotateOverlappingUIAnimationIfNeeded();

            // スピードアタック記録
            _speedAttackRecord.Hidden = speedAttackViewModel.IsEmpty();
            _speedAttackRecord.Setup(speedAttackViewModel.ClearTimeMs, speedAttackViewModel.NextGoalTime);

            // キャンペーンバルーン
            SetUpCampaignBalloons(campaignViewModel);
        }

        public void SetCurrentPartyName(PartyName partyName)
        {
            _currentPartyName.SetText(partyName.Value);
        }

        void SetUpCampaignBalloons(IReadOnlyList<CampaignViewModel> viewModels)
        {
            _homeMainQuestView.SetUpCampaignBalloons(viewModels);
        }

        public void SetActiveCampaignBalloon(bool isActive)
        {
            _homeMainQuestView.SetActiveCampaignBalloon(isActive);

            _normalCampaignBalloonSwitcher.Hidden = !isActive;
            _hardCampaignBalloonSwitcher.Hidden = !isActive;
            _extraCampaignBalloonSwitcher.Hidden = !isActive;
        }

        public void SetQuestBackGroundImage(QuestImageAssetPath path)
        {
            //背面(ぼかし)画像の設定
            UISpriteUtil.LoadSpriteWithFadeIfNotLoaded(
                _questThumbnailBackGroundImage.Image,
                path.Value,
                () =>
                {
                    //Tweenでフェードアウト
                    _homeMainQuestView.QuestLoadImageCanvasGroup.alpha = 1;
                    _questThumbnailLoadCanvasGroup.alpha = 1;
                    _homeMainQuestView.QuestLoadImageCanvasGroup.DOFade(0, 0.3f);
                    _questThumbnailLoadCanvasGroup.DOFade(0, 0.3f);

                    //NOTE: RawImageを使いたいがUISpriteUtilなどからAssetBundle化された画像を直接RawImageで取得する手段がないため
                    //一旦Imageで取得してからRawImage(_uiLightBlurTextureComponent)に設定をする。
                    //_uiLightBlurTextureComponentは設定後表示し、仲介のImageは非表示にする。
                    if (!_uiLightBlurTextureComponent ||
                        !_questThumbnailBackGroundImage)
                    {
                        return;
                    }
                    _uiLightBlurTextureComponent.Hidden = false;
                    _uiLightBlurTextureComponent.SetTexture(_questThumbnailBackGroundImage.Image.sprite.texture);

                    _questThumbnailBackGroundImage.Hidden = true;
                });
        }
    }
}
