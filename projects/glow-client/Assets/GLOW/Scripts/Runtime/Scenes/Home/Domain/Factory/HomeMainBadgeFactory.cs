using System.Linq;
using GLOW.Core.Domain.Calculator;
using GLOW.Core.Domain.Repositories;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Extensions;
using GLOW.Scenes.Home.Domain.Models;
using WonderPlanet.UnityStandard.Extension;
using Zenject;

namespace GLOW.Scenes.Home.Domain.UseCases
{
    public class HomeMainBadgeFactory : IHomeMainBadgeFactory
    {
        [Inject] IGameRepository GameRepository { get; }
        [Inject] ITimeProvider TimeProvider { get; }
        [Inject] IMstIdleIncentiveRepository MstIdleIncentiveRepository { get; }
        [Inject] IAnnouncementCacheRepository AnnouncementCacheRepository { get; }
        [Inject] IAnnouncementPreferenceRepository AnnouncementPreferenceRepository { get; }
        [Inject] IMstUnitEncyclopediaRewardDataRepository MstUnitEncyclopediaRewardDataRepository { get; }
        [Inject] IMstConfigRepository MstConfigRepository { get; }
        [Inject] IMstCharacterDataRepository MstCharacterDataRepository { get; }
        [Inject] IMstEnemyCharacterDataRepository MstEnemyCharacterDataRepository { get; }
        [Inject] IMstArtworkDataRepository MstArtworkDataRepository { get; }
        [Inject] IMstEmblemRepository MstEmblemDataRepository { get; }
        [Inject] IMstSeriesDataRepository MstSeriesDataRepository { get; }
        [Inject] IMstEventDataRepository MstEventDataRepository { get; }

        public HomeMainBadgeModel GetHomeMainBadgeModel()
        {
            return new HomeMainBadgeModel(
                CreateDailyMissionBadge(),
                CreateEventMissionBadge(),
                CreateBeginnerMissionBadge(),
                CreateEncyclopediaBadge(),
                CreateIdleIncentiveBadge(),
                CreateAnnouncementBadge(),
                CreateMessageBoxBadge());
        }

        NotificationBadge CreateDailyMissionBadge()
        {
            var unreceivedMissionRewardCount = GameRepository.GetGameFetch().BadgeModel.UnreceivedMissionRewardCount;
            return new NotificationBadge(!unreceivedMissionRewardCount.IsZero());
        }
        NotificationBadge CreateEventMissionBadge()
        {
            var latestMstEventModel = MstEventDataRepository.GetEvents()
                .Where(m => CalculateTimeCalculator.IsValidTime(TimeProvider.Now, m.StartAt, m.EndAt))
                .MaxBy(m =>m.StartAt);

            if(latestMstEventModel == null)
            {
                return NotificationBadge.False;
            }

            var unreceivedMissionRewardCount =
                GameRepository.GetGameFetch().BadgeModel.UnreceivedMissionEventRewardCountById(latestMstEventModel.Id);

            return new NotificationBadge(!unreceivedMissionRewardCount.IsZero());
        }
        NotificationBadge CreateBeginnerMissionBadge()
        {
            var unreceivedMissionBeginnerRewardCount = GameRepository.GetGameFetch().BadgeModel.UnreceivedMissionBeginnerRewardCount;
            return new NotificationBadge(!unreceivedMissionBeginnerRewardCount.IsZero());
        }
        NotificationBadge CreateIdleIncentiveBadge()
        {
            // チュートリアル中はバッジ表示なし
            if (!GameRepository.GetGameFetchOther().TutorialStatus.IsCompleted())
            {
                return NotificationBadge.False;
            }

            var mstIdleIncentive = MstIdleIncentiveRepository.GetMstIdleIncentive();
            var userIdleIncentive = GameRepository.GetGameFetchOther().UserIdleIncentiveModel;
            var targetTime = userIdleIncentive.IdleStartedAt + mstIdleIncentive.InitialRewardReceiveMinutes;
            var shouldShowIdleIncentive = targetTime < TimeProvider.Now;

            return new NotificationBadge(shouldShowIdleIncentive);
        }

        public NotificationBadge CreateEncyclopediaBadge()
        {
            var gameFetchOther = GameRepository.GetGameFetchOther();
            var defaultOutPostArtworkId = MstConfigRepository
                .GetConfig(MstConfigKey.DefaultOutpostArtworkId).Value
                .ToMasterDataId();

            // 未取得の図鑑報酬
            var unitGrade = UnitEncyclopediaEffectCalculator.CalculateUnitEncyclopediaGrade(gameFetchOther.UserUnitModels);
            var userReceivedRewards = gameFetchOther.UserReceivedUnitEncyclopediaRewardModels;
            var mstRewards = MstUnitEncyclopediaRewardDataRepository.GetUnitEncyclopediaRewards();
            var isUnReceived = mstRewards
                .Where(mst => mst.UnitEncyclopediaRank.Value <= unitGrade.Value)
                .Any(mst => userReceivedRewards.All(receivedReward => receivedReward.MstUnitEncyclopediaRewardId != mst.Id));

            var mstCharacters = MstCharacterDataRepository.GetCharacters();
            var mstEnemyCharacters = MstEnemyCharacterDataRepository.GetEnemyCharacters();
            var mstArtworks = MstArtworkDataRepository.GetArtworks();
            var mstEmblems = MstEmblemDataRepository.GetMstEmblems();
            var mstSeriesIds = MstSeriesDataRepository
                .GetMstSeriesModels()
                .Select(mstSeries => mstSeries.Id)
                .ToHashSet();

            // 未表示の図鑑コンテンツ
            // NOTE: MstSeriesに登録されていない作品は除外する
            var isNewPlayerUnit = gameFetchOther.UserUnitModels
                .Where(userUnit => userUnit.IsNewEncyclopedia)
                .Join(mstCharacters,
                    userUnit => userUnit.MstUnitId,
                    mstCharacter => mstCharacter.Id,
                    (userUnit, mstUnit) => mstUnit)
                .Any(mstUnit =>
                    mstSeriesIds.Contains(mstUnit.MstSeriesId));

            var isNewEnemyUnit = gameFetchOther.UserEnemyDiscoverModels
                .Where(enemyDiscover => enemyDiscover.IsNewEncyclopedia)
                .Join(mstEnemyCharacters,
                    enemyDiscover => enemyDiscover.MstEnemyCharacterId,
                    mstEnemyCharacter => mstEnemyCharacter.Id,
                    (enemyDiscover, mstEnemyCharacter) => mstEnemyCharacter)
                .Any(mstEnemyCharacter =>
                    mstEnemyCharacter.VisibleOnEncyclopediaFlag
                    && mstSeriesIds.Contains(mstEnemyCharacter.MstSeriesId));

            var isNewArtwork = gameFetchOther.UserArtworkModels
                // デフォルトアートワークの報酬獲得のための画面遷移ヒントが無く、バッジ非表示化が難しいので
                // バッジ表示選定から除外する
                .Where(userArtwork => userArtwork.IsNewEncyclopedia
                                      && userArtwork.MstArtworkId != defaultOutPostArtworkId)
                .Join(mstArtworks,
                    userArtwork => userArtwork.MstArtworkId,
                    mstArtwork => mstArtwork.Id,
                    (userArtwork, mstArtwork) => mstArtwork)
                .Any(mstArtwork =>
                    mstSeriesIds.Contains(mstArtwork.MstSeriesId));

            var isNewEmblem = gameFetchOther.UserEmblemModel
                .Where(userEmblem => userEmblem.IsNewEncyclopedia)
                .Join(mstEmblems,
                    userEmblem => userEmblem.MstEmblemId,
                    mstEmblem => mstEmblem.Id,
                    (userEmblem, mstEmblem) => mstEmblem)
                .Any(mstEmblem =>
                    mstSeriesIds.Contains(mstEmblem.MstSeriesId));

            var isNewEncyclopediaContents = isNewPlayerUnit || isNewEnemyUnit || isNewArtwork || isNewEmblem;

            return new NotificationBadge(isUnReceived || isNewEncyclopediaContents);
        }

        NotificationBadge CreateAnnouncementBadge()
        {
            // 最も更新が新しいお知らせを読んでいないかどうか
            bool unreadLatestUpdatedAnnouncementExists = false;
            var readLatestUpdatedAnnouncementDictionary = AnnouncementPreferenceRepository.ReadAnnouncementIdAndLastUpdated;
            if (readLatestUpdatedAnnouncementDictionary.IsEmpty())
            {
                // 一つも読んでいない場合は、最新のお知らせを未読として扱う
                unreadLatestUpdatedAnnouncementExists = true;
            }
            else
            {
                var informationLatestUpdatedTime = AnnouncementPreferenceRepository.ReadInformationLastUpdated;
                var operationLatestUpdatedTime = AnnouncementPreferenceRepository.ReadOperationLastUpdated;
                var announcementAllReadFlag = AnnouncementPreferenceRepository.AnnouncementAlreadyReadAll;

                // タブ毎の読んだお知らせの最終更新時間と、APIから取得した最終更新時間を比較する
                // その上で全てのお知らせを読んだ状態かどうかを見る
                // どちらかがAPIの方が最新の場合はまだ読んでいないお知らせがある(過去に読んでいたとしても更新されている場合がある)
                if (AnnouncementCacheRepository.GetInformationLastUpdated() > informationLatestUpdatedTime ||
                    AnnouncementCacheRepository.GetOperationLastUpdated() > operationLatestUpdatedTime ||
                    !announcementAllReadFlag)
                {
                    unreadLatestUpdatedAnnouncementExists = true;
                }
            }

            return new NotificationBadge(unreadLatestUpdatedAnnouncementExists);
        }

        NotificationBadge CreateMessageBoxBadge()
        {
            var unreceivedMessage = GameRepository.GetGameFetch().BadgeModel.UnopenedMessageCount;
            return new NotificationBadge(!unreceivedMessage.IsZero());
        }
    }
}
