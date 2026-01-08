using System.Collections.Generic;
using System.Linq;
using GLOW.Core.Domain.Modules.Time;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Extensions;
using GLOW.Core.Presentation.Components;
using GLOW.Core.Presentation.ViewModels;
using GLOW.Scenes.QuestContentTop.Domain.enums;
using GLOW.Scenes.QuestContentTop.Domain.UseCaseModel;
using GLOW.Scenes.QuestContentTop.Domain.ValueObject;
using GLOW.Scenes.QuestContentTop.Presentation.ViewModel;
using UIKit;
using UnityEngine;
using UnityEngine.UI;
using WPFramework.Presentation.Modules;

namespace GLOW.Scenes.QuestContentTop.Presentation
{
    public class QuestContentCell : UICollectionViewCell
    {
        const string ReleaseTrigger = "OnRelease";

        [SerializeField] Button _cellButton;
        [Header("キャンペンバルーン")]
        [SerializeField] CampaignBalloonMultiSwitcherComponent _campaignBalloonSwitcher;
        [SerializeField] EventCampaignBalloon _eventCampaignBalloon;
        [Header("タイトル")]
        [SerializeField] GameObject _adventBattleTitle;
        [SerializeField] GameObject _enhanceTitle;
        [SerializeField] GameObject _pvpTitle;
        [Header("タイトル/イベント")]
        [SerializeField] GameObject _eventTitle;
        [SerializeField] UIText _eventTitleText;
        [SerializeField] UIText _eventTitleShadowText;
        [Header("バナー")]
        [SerializeField] UIImage _bannerImage;
        [SerializeField] Sprite _adventBattleBanner;
        [SerializeField] Sprite _enhanceBanner;
        [SerializeField] Sprite _pvpBanner;

        [Header("残り挑戦回数(挑戦回復時間)")]
        [SerializeField] UIText _challengeCountText;
        [SerializeField] GameObject _pvpChallengeAtTicketObj;
        [SerializeField] UIText _pvpChallengeAtTicketText;

        [Header("残り時間")]
        [SerializeField] GameObject _limitTimeObj;
        [SerializeField] UIText _limitTimeText;
        [Header("ランキングボタン")]
        [SerializeField] Button _rankingButton;
        [SerializeField] GameObject _rankingBadge;
        [Header("未開催表示")]
        [SerializeField] GameObject _offObj;
        [SerializeField] GameObject _noChallengeCountText;
        [Header("未開催表示/詳細")]
        [SerializeField] GameObject _lockObj;
        [SerializeField] GameObject _limitChallengeObj;
        [SerializeField] GameObject _totalizedObj;
        [SerializeField] GameObject _outOfTimeObj;

        [Header("バナー通知バッジ")]
        [SerializeField] GameObject _bannerBadge;

        [Header("チュートリアル用")]
        [SerializeField] GameObject _animationObject;
        [SerializeField] Animator _releaseAnimation;

        // cellのタイトル横に表示する一覧format
        string _challengeCountTextFormat = "本日あと<color={0}><size=22>{1}</size>回</color>挑戦可能";
        string _pvpTicketChallengeCountTextFormat = "であと<color={0}><size=22>{1}</size>回</color>挑戦可能";
        string _totalizationText = "集計結果までしばらくお待ちください";

        // チュートリアル用 セルのタイプ
        public QuestContentTopElementType QuestContentTopElementType { get; private set; }

        protected override void Awake()
        {
            base.Awake();
            AddButton(_rankingButton, "ranking");
        }

        public void SetContentCell(QuestContentCellViewModel model)
        {
            _cellButton.interactable = model.IsButtonInteractable();

            SetUpCampaignBalloons(model.CampaignViewModels, model.ElementType);

            // タイトル
            SetTitle(model.ElementType, model.EventName);

            // バナー
            SetBannerSprite(model.ElementType, model.BannerAssetPath);

            // 挑戦回数・リセット情報
            if (model.ElementType
                is QuestContentTopElementType.Enhance
                or QuestContentTopElementType.AdventBattle
                or QuestContentTopElementType.Pvp)
            {
                // 強化クエストは挑戦回数を表示しない
                // 降臨バトルは挑戦回数を表示しない
                // PVPは挑戦回数を表示しない
                _noChallengeCountText.SetActive(false);
            }
            else
            {
                _noChallengeCountText.SetActive(!model.IsOpening);
            }

            SetPvpTicketChallengeText(model.IsPvpTicketChallengeTextVisible(), model.ChallengeCount);

            SetChallengeCountText(
                model.IsChallengeTextVisible(),
                model.OpeningStatusModel,
                model.ChallengeResetTime,
                model.ChallengeCount);

            //制限時間
            _limitTimeObj.SetActive(model.IsLimitTimeVisible);
            _limitTimeText.SetText(model.LimitTimeText);

            // ランキングボタン
            _rankingButton.gameObject.SetActive(model.IsShowPrevRanking);
            _rankingBadge.SetActive(model.HasRankingNotification);

            // 挑戦不可オブジェクト類
            _offObj.SetActive(model.IsGrayOutVisible());
            _lockObj.SetActive(model.ShouldShowLockObj());
            _limitChallengeObj.SetActive(model.ShouldShowLimitChallengeObj());
            _totalizedObj.SetActive(
                !model.ShouldShowLockObj() && //集計中とロックアイコン重複するのを防ぐ
                model.IsClosedDueTo(QuestContentOpeningStatusAtTimeType.Totalizing));
            _outOfTimeObj.SetActive(
                !model.ShouldShowLockObj() &&
                model.IsClosedDueTo(QuestContentOpeningStatusAtTimeType.OutOfLimit));

            // ミッション通知バッジ
            _bannerBadge.SetActive(model.HasBannerBadgeNotification);

            QuestContentTopElementType = model.ElementType;
        }
        
        public void SetNotificationBadge(NotificationBadge badge)
        {
            _bannerBadge.SetActive(badge);
        }

        public void PlayReleaseAnimation()
        {
            _animationObject.SetActive(true);
            _releaseAnimation.SetTrigger(ReleaseTrigger);
            _offObj.SetActive(true);
            _cellButton.interactable = false;
        }

        public void SetInActiveAnimation()
        {
            _animationObject.SetActive(false);
            _offObj.SetActive(false);
            _cellButton.interactable = true;
        }

        void SetUpCampaignBalloons(IReadOnlyList<CampaignViewModel> viewModels, QuestContentTopElementType type)
        {
            if (!viewModels.Any())
            {
                _campaignBalloonSwitcher.Hidden = true;
                _eventCampaignBalloon.Hidden = true;
                return;
            }

            _eventCampaignBalloon.Hidden = type != QuestContentTopElementType.Event;
            _campaignBalloonSwitcher.Hidden = type == QuestContentTopElementType.Event;

            if (type == QuestContentTopElementType.Event)
            {
                var targetViewModel = viewModels.MaxBy(x => x.RemainingTimeSpan.Value);
                _eventCampaignBalloon.SetRemainingTimeText(targetViewModel.RemainingTimeSpan);
                return;
            }

            _campaignBalloonSwitcher.SetUpCampaignBalloons(viewModels);
        }

        void SetTitle(QuestContentTopElementType type, EventName eventName)
        {
            if (!eventName.IsEmpty())
            {
                _eventTitleText.SetText(eventName.Value);
                _eventTitleShadowText.SetText(eventName.Value);
            }
            else
            {
                _eventTitleText.SetText("");
                _eventTitleShadowText.SetText("");
            }

            _adventBattleTitle.SetActive(type == QuestContentTopElementType.AdventBattle);
            _enhanceTitle.SetActive(type == QuestContentTopElementType.Enhance);
            _eventTitle.SetActive(type == QuestContentTopElementType.Event);
            _pvpTitle.SetActive(type == QuestContentTopElementType.Pvp);
        }

        void SetPvpTicketChallengeText(bool isPvpTicketChallengeVisible, IQuestChallengeCountable challengeCount)
        {
            _pvpChallengeAtTicketObj.SetActive(isPvpTicketChallengeVisible);
            _pvpChallengeAtTicketText.SetText(_pvpTicketChallengeCountTextFormat, ColorCodeTheme.TextRed, challengeCount.Value);
        }

        void SetChallengeCountText(
            bool isChallengeTextVisible,
            QuestContentOpeningStatusModel model,
            QuestChallengeResetTime resetTime,
            IQuestChallengeCountable challengeCount)
        {
            _challengeCountText.gameObject.SetActive(isChallengeTextVisible);

            if (model.OpeningStatusAtTimeType == QuestContentOpeningStatusAtTimeType.Opening)
            {
                _challengeCountText.SetText(_challengeCountTextFormat, ColorCodeTheme.TextRed, challengeCount.Value);
            }

            if (model.OpeningStatusAtUserStatus == QuestContentOpeningStatusAtUserStatus.OverLimitChallengeCount)
            {
                _challengeCountText.SetText(TimeSpanFormatter.FormatUntilRecovery(resetTime.Value));
            }

            if (model.OpeningStatusAtTimeType == QuestContentOpeningStatusAtTimeType.Totalizing)
            {
                _challengeCountText.SetText(_totalizationText);
            }
        }

        void SetBannerSprite(QuestContentTopElementType modelElementType, EventContentBannerAssetPath assetPath)
        {
            if (!assetPath.IsEmpty())
            {
                UISpriteUtil.LoadSpriteWithFadeIfNotLoaded(_bannerImage.Image, assetPath.Value);
                return;
            }

            _bannerImage.Sprite = modelElementType switch
            {
                QuestContentTopElementType.AdventBattle => _adventBattleBanner,
                QuestContentTopElementType.Enhance => _enhanceBanner,
                QuestContentTopElementType.Pvp => _pvpBanner,
                _ => null
            };
        }
    }
}
