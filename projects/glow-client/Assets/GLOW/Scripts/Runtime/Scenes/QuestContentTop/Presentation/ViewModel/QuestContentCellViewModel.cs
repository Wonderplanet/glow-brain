using System.Collections.Generic;
using GLOW.Core.Domain.Modules.Time;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Presentation.ViewModels;
using GLOW.Scenes.EventQuestSelect.Domain.ValueObject;
using GLOW.Scenes.QuestContentTop.Domain.enums;
using GLOW.Scenes.QuestContentTop.Domain.UseCaseModel;
using GLOW.Scenes.QuestContentTop.Domain.ValueObject;

namespace GLOW.Scenes.QuestContentTop.Presentation.ViewModel
{
    public record QuestContentCellViewModel(
        QuestContentTopElementType ElementType,
        QuestContentOpeningStatusModel OpeningStatusModel,
        IQuestChallengeCountable ChallengeCount,
        QuestContentTopChallengeType ChallengeType,
        QuestChallengeResetTime ChallengeResetTime,
        RemainingTimeSpan LimitTime,
        HasRankingFlag HasRanking,
        NotificationBadge HasRankingNotification,
        NotificationBadge HasBannerBadgeNotification,
        MasterDataId MstEventId,
        EventName EventName,
        EventContentBannerAssetPath BannerAssetPath,
        IReadOnlyList<CampaignViewModel> CampaignViewModels)
    {
        public static QuestContentCellViewModel Empty { get; } =
            new QuestContentCellViewModel(
                QuestContentTopElementType.Other,
                QuestContentOpeningStatusModel.Empty,
                new EventChallengeCount(0),
                QuestContentTopChallengeType.Normal,
                QuestChallengeResetTime.Empty,
                RemainingTimeSpan.Empty,
                HasRankingFlag.False,
                NotificationBadge.False,
                NotificationBadge.False,
                MasterDataId.Empty,
                EventName.Empty,
                EventContentBannerAssetPath.Empty,
                new List<CampaignViewModel>());

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }

        public bool IsLimitTimeVisible =>
            !LimitTime.IsEmpty() &&
            ElementType != QuestContentTopElementType.Enhance &&
            OpeningStatusModel.OpeningStatusAtTimeType == QuestContentOpeningStatusAtTimeType.Opening;

        public string LimitTimeText => TimeSpanFormatter.FormatUntilEnd(LimitTime.Value);

        public bool IsShowPrevRanking =>
            OpeningStatusModel.OpeningStatusAtTimeType == QuestContentOpeningStatusAtTimeType.OutOfLimit &&
            HasRanking;

        public bool IsOpening => OpeningStatusModel.OpeningStatusAtTimeType == QuestContentOpeningStatusAtTimeType.Opening &&
                                 OpeningStatusModel.OpeningStatusAtUserStatus == QuestContentOpeningStatusAtUserStatus.None;

        public bool IsAdventBattleTransitionable =>
            OpeningStatusModel.OpeningStatusAtTimeType == QuestContentOpeningStatusAtTimeType.Opening
            && (OpeningStatusModel.OpeningStatusAtUserStatus == QuestContentOpeningStatusAtUserStatus.None
                || OpeningStatusModel.OpeningStatusAtUserStatus == QuestContentOpeningStatusAtUserStatus.OverLimitChallengeCount);

        public bool IsGrayOutVisible()
        {
            if (ElementType == QuestContentTopElementType.Pvp)
            {
                // 挑戦回数0でもpvpは遷移可能
                return OpeningStatusModel.OpeningStatusAtTimeType != QuestContentOpeningStatusAtTimeType.Opening ||
                       OpeningStatusModel.OpeningStatusAtUserStatus == QuestContentOpeningStatusAtUserStatus.RankLocked ||
                       OpeningStatusModel.OpeningStatusAtUserStatus == QuestContentOpeningStatusAtUserStatus.StageLocked;
            }

            return !IsOpening || !IsAbleToTransitAdventBattle();
        }

        public bool IsButtonInteractable()
        {
            return ElementType switch
            {
                QuestContentTopElementType.AdventBattle =>
                    OpeningStatusModel.OpeningStatusAtTimeType == QuestContentOpeningStatusAtTimeType.Opening ||
                    OpeningStatusModel.OpeningStatusAtTimeType == QuestContentOpeningStatusAtTimeType.BeforeOpen,
                QuestContentTopElementType.Enhance =>
                    OpeningStatusModel.OpeningStatusAtTimeType == QuestContentOpeningStatusAtTimeType.Opening ||
                    OpeningStatusModel.OpeningStatusAtTimeType == QuestContentOpeningStatusAtTimeType.BeforeOpen,
                QuestContentTopElementType.Pvp =>
                    OpeningStatusModel.OpeningStatusAtUserStatus == QuestContentOpeningStatusAtUserStatus.StageLocked ||
                    OpeningStatusModel.OpeningStatusAtTimeType == QuestContentOpeningStatusAtTimeType.Totalizing ||
                    OpeningStatusModel.OpeningStatusAtUserStatus == QuestContentOpeningStatusAtUserStatus.OverLimitChallengeCount ||
                    IsOpening,
                _ => IsOpening
            };
        }

        public bool IsClosedDueTo(QuestContentOpeningStatusAtTimeType statusAtTimeType)
        {
            return !IsOpening && OpeningStatusModel.OpeningStatusAtTimeType == statusAtTimeType;
        }

        public bool IsClosedDueTo(QuestContentOpeningStatusAtUserStatus statusAtUserStatus)
        {
            return !IsOpening && OpeningStatusModel.OpeningStatusAtUserStatus == statusAtUserStatus;
        }

        bool IsAbleToTransitAdventBattle()
        {
            if (ElementType != QuestContentTopElementType.AdventBattle)
            {
                return true;
            }

            // 降臨バトルの場合、OpenとOverLimitChallengeCountの時は遷移可能
            return OpeningStatusModel.OpeningStatusAtTimeType == QuestContentOpeningStatusAtTimeType.Opening ||
                   OpeningStatusModel.OpeningStatusAtUserStatus == QuestContentOpeningStatusAtUserStatus.OverLimitChallengeCount;
        }

        public bool CanExecuteTappedAction()
        {
            // 開催・開放中
            var isOpening = IsOpening;

            // 降臨バトル
            var isAdventBattleOrOpening = ElementType == QuestContentTopElementType.AdventBattle;

            // コイン獲得クエスト
            var isEnhanceOrOpening = ElementType == QuestContentTopElementType.Enhance;

            // PVPの開放条件ステージ未クリア状態(未開放トースト表示用)
            var isPvpOrStageLocked =
                ElementType == QuestContentTopElementType.Pvp &&
                (OpeningStatusModel.OpeningStatusAtUserStatus == QuestContentOpeningStatusAtUserStatus.StageLocked ||
                 OpeningStatusModel.OpeningStatusAtTimeType == QuestContentOpeningStatusAtTimeType.Totalizing ||
                 OpeningStatusModel.OpeningStatusAtUserStatus == QuestContentOpeningStatusAtUserStatus.OverLimitChallengeCount);

            return isOpening || isAdventBattleOrOpening || isPvpOrStageLocked || isEnhanceOrOpening;
        }

        public bool ShouldShowLockObj()
        {
            return IsClosedDueTo(QuestContentOpeningStatusAtUserStatus.RankLocked) ||
                   IsClosedDueTo(QuestContentOpeningStatusAtUserStatus.StageLocked) ||
                   IsClosedDueTo(QuestContentOpeningStatusAtTimeType.BeforeOpen);
        }

        public bool ShouldShowLimitChallengeObj()
        {
            return !ShouldShowLockObj() &&
                   IsClosedDueTo(QuestContentOpeningStatusAtUserStatus.OverLimitChallengeCount) &&
                   !IsClosedDueTo(QuestContentOpeningStatusAtTimeType.Totalizing);
        }

        // 挑戦回数テキストの表示条件を判定するメソッド
        public bool IsChallengeTextVisible()
        {
            // イベントの場合
            if (ElementType == QuestContentTopElementType.Event)
            {
                return false;
            }

            if (ElementType == QuestContentTopElementType.Pvp && ChallengeType == QuestContentTopChallengeType.Ticket)
            {
                return false;
            }

            // ランキング集計中を除き、開催中以外かつ、挑戦回数制限オーバー以外の場合
            if (OpeningStatusModel.OpeningStatusAtTimeType != QuestContentOpeningStatusAtTimeType.Opening &&
                OpeningStatusModel.OpeningStatusAtTimeType != QuestContentOpeningStatusAtTimeType.Totalizing &&
                OpeningStatusModel.OpeningStatusAtUserStatus != QuestContentOpeningStatusAtUserStatus.OverLimitChallengeCount)
            {
                return false;
            }

            // 降臨バトルの場合は追加条件をチェック
            // ランキング集計中じゃない場合は残りの挑戦回数や挑戦回復時間を表示する可能性があるため分岐に入る
            if (ElementType == QuestContentTopElementType.AdventBattle &&
                OpeningStatusModel.OpeningStatusAtTimeType != QuestContentOpeningStatusAtTimeType.Totalizing)
            {
                // 挑戦回数リセット時間が設定されていて、かつ挑戦回数制限オーバーの場合、
                // 挑戦回数リセット時間とイベントの残り時間を比較し、イベントの残り時間の方が短ければ挑戦回数リセット時間を表示しない
                if (!ChallengeResetTime.IsEmpty &&
                    OpeningStatusModel.OpeningStatusAtUserStatus == QuestContentOpeningStatusAtUserStatus.OverLimitChallengeCount)
                {
                    return ChallengeResetTime.Value < LimitTime.Value;
                }
            }

            return true;
        }

        // 挑戦回数テキストの表示条件を判定するメソッド
        public bool IsPvpTicketChallengeTextVisible()
        {
            return ElementType == QuestContentTopElementType.Pvp && ChallengeType == QuestContentTopChallengeType.Ticket;
        }
    };
}
