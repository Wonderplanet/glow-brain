using System;
using System.Collections.Generic;
using System.Globalization;
using System.Linq;
using GLOW.Core.Constants.LocalNotification;
using GLOW.Core.Data.Modules.Serializer;
using GLOW.Core.Domain.Constants.Shop;
using GLOW.Core.Domain.Models.AdventBattle;
using GLOW.Core.Domain.Models.Shop;
using GLOW.Core.Domain.Repositories;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.AnnouncementWindow;
using GLOW.Core.Domain.ValueObjects.AdventBattle;
using GLOW.Core.Domain.ValueObjects.InGame;
using GLOW.Core.Domain.ValueObjects.Notice;
using GLOW.Core.Domain.ValueObjects.Shop;
using GLOW.Modules.InAppReview.Domain.ValueObject;
using GLOW.Scenes.AnnouncementWindow.Domain.ValueObject;
using GLOW.Scenes.InGame.Domain.Constants;
using Newtonsoft.Json;
using Runtime.PlayerPrefs;


namespace GLOW.Core.Data.Repositories
{
    public class PreferenceRepository :
        IPreferenceRepository,
        IShopProductCacheRepository,
        IQuestStageReleaseAnimationRepository,
        IOutpostArtworkBadgeRepository,
        IUserProfileBadgeRepository,
        IUserEmblemBadgeRepository,
        IInGamePreferenceRepository,
        IDisplayedInGameNoticeRepository,
        IAdventBattlePreferenceRepository,
        IAnnouncementPreferenceRepository,
        IInAppReviewPreferenceRepository,
        IContentTopAccessPreferenceRepository,
        IOpenedMessagePreferenceRepository,
        IDeferredPurchaseCacheRepository
    {
        static string KeyLastCheckedNewShopProductsDateTimeOffset => "GLOW/KeyLastCheckedNewShopProductsDateTimeOffset";
        static string KeyDisplayedShopProductIds => "DisplayedShopProductIds";
        static string KeyDisplayedOprPackProductIds => "DisplayedOprPackProductIds";
        static string KeyCurrentPartyNo => "GLOW/CurrentPartyNo";
        static string KeyDisplayedOutpostArtworkIds => "DisplayedOutpostArtworkIds";
        static string KeyLastCheckedInGameNoticesDateTimeOffset => "GLOW/KeyLastCheckedInGameNoticesDateTimeOffset";
        static string KeyDisplayedDailyInGameNoticeIds => "GLOW/DisplayedDailyInGameNoticeIds";
        static string KeyDisplayedWeeklyInGameNoticeIds => "GLOW/DisplayedWeeklyInGameNoticeIds";
        static string KeyDisplayedMonthlyInGameNoticeIds => "GLOW/DisplayedMonthlyInGameNoticeIds";
        static string KeyDisplayedOnceInGameNoticeIds => "GLOW/DisplayedOnceInGameNoticeIds";
        static string KeyDisplayedUserProfileAvatarIds => "DisplayedUserProfileAvatarIds";
        static string KeyDisplayedUserEmblemIds => "DisplayedUserEmblemIds";
        static string KeyInAppPurchaseFakeStoreMode => "GLOW/InAppPurchaseFakeStoreMode";
        static string KeyReadAnnouncementIds => "GLOW/ReadAnnouncementIds";
        static string KeyBeginnerMissionReleaseDayNumber => "GLOW/BeginnerMissionReleaseDayNumber";
        static string KeyInGameBattleSpeed => "GLOW/InGameBattleSpeed";
        static string KeyInGameAutoEnabled => "GLOW/InGameAutoEnabled";
        static string KeyInGameContinueSelecting => "GLOW/InGameContinueSelecting";
        static string KeyAnnouncementLastDisplayDateTimeOffset => "GLOW/LastAnnouncementLastDisplayDateTimeOffset";
        static string KeyAnnouncementInformationLastUpdatedAt => "GLOW/AnnouncementInformationLastUpdatedAt";
        static string KeyAnnouncementOperationLastUpdatedAt => "GLOW/AnnouncementOperationLastUpdatedAt";
        static string KeyReadAnnouncementInformationLastUpdatedAt => "GLOW/ReadAnnouncementInformationLastUpdatedAt";
        static string KeyReadAnnouncementOperationLastUpdatedAt => "GLOW/ReadAnnouncementOperationLastUpdatedAt";
        static string KeyAnnouncementReadAll => "GLOW/AnnouncementReadAll";
        static string KeyAdventBattleRankingResultAnimationPlayedId => "GLOW/AdventBattleRankingResultAnimationPlayedId";
        static string KeyReceivedRaidRewardAdventBattleIdsEvaluated => "GLOW/ReceivedRaidRewardAdventBattleIdsEvaluated";
        static string KeyAdventBattleRaidTotalScoreRewardsEvaluated => "GLOW/AdventBattleRaidTotalScoreRewardsEvaluated";
        static string KeyLocalNotificationScheduledIdentifiers => "GLOW/LocalNotificationScheduledIdentifiers";
        static string LastPlayedEventAtMstQuestGroupIds => "GLOW/LastPlayedEventAtMstQuestGroupIds";
        static string KeyInAppReviewAfterGachaUrDrawn => "GLOW/KeyInAppReviewAfterGachaUrDrawn";
        static string KeyMyId => "GLOW/UserMyId";
        static string KeyShouldStartOutpostEnhanceTutorial => "GLOW/ShouldStartOutpostEnhanceTutorial";
        static string KeyContentTopAccessTime => "GLOW/ContentTopAccessTime";
        static string KeyOpenedMessageIds => "GLOW/OpenedMessageIds";
        static string KeyRestorePurchaseResult => "GLOW/RestorePurchaseResult";
        static string KeyDeferredPurchaseResult => "GLOW/DeferredPurchaseResult";
        static string KeyDeferredPurchaseErrorCode => "GLOW/DeferredPurchaseErrorCode";

        // キャッシュフィールド
        HashSet<MasterDataId> _cachedDisplayedShopProductIdHashSet;
        List<MasterDataId> _cachedDisplayedOprPackProductIds;
        Dictionary<AnnouncementId, AnnouncementLastUpdateAt> _cachedReadAnnouncementIdAndLastUpdated;
        List<MasterDataId> _cachedOpenedMessageIds;

        public PartyNo SelectPartyNo
        {
            get => new PartyNo(EncryptionPlayerPrefs.GetInt(KeyCurrentPartyNo, 1));
            set
            {
                EncryptionPlayerPrefs.SetInt(KeyCurrentPartyNo, value.Value);
                EncryptionPlayerPrefs.Save();
            }
        }

        void IPreferenceRepository.SetLastPlayedMstStageId(MasterDataId mstStageId)
        {
            LastPlayedMstStageId = mstStageId;
        }

        void IPreferenceRepository.SetCurrentHomeTopSelectMstQuestId(MasterDataId mstQuestId)
        {
            CurrentHomeTopSelectMstQuestId = mstQuestId;
        }

        DateTimeOffset IDisplayedInGameNoticeRepository.LastCheckedNoticeDateTimeOffset
        {
            get
            {
                var lastCheckedNoticeDateTimeOffset = EncryptionPlayerPrefs.GetString(KeyLastCheckedInGameNoticesDateTimeOffset, "");
                if (string.IsNullOrEmpty(lastCheckedNoticeDateTimeOffset)) return DateTimeOffset.MinValue;

                return GetParseToDateTimeOffset(lastCheckedNoticeDateTimeOffset);
            }
        }

        void IDisplayedInGameNoticeRepository.SaveLastCheckedNoticeDateTimeOffset(DateTimeOffset lastCheckedNoticeDateTimeOffset)
        {
            EncryptionPlayerPrefs.SetString(KeyLastCheckedInGameNoticesDateTimeOffset, lastCheckedNoticeDateTimeOffset.ToString());
            EncryptionPlayerPrefs.Save();
        }

        HashSet<NoticeId> IDisplayedInGameNoticeRepository.DisplayedDailyNoticeIdHashSet
        {
            get
            {
                var list = EncryptionPlayerPrefs.GetList(KeyDisplayedDailyInGameNoticeIds, new List<string>());
                return list.Select(id => new NoticeId(id)).ToHashSet();
            }
        }

        void IDisplayedInGameNoticeRepository.AddDisplayedDailyNoticeId(NoticeId noticeId)
        {
            var noticeList = EncryptionPlayerPrefs.GetList(KeyDisplayedDailyInGameNoticeIds, new List<string>());
            noticeList.Add(noticeId.Value);
            EncryptionPlayerPrefs.SetList(KeyDisplayedDailyInGameNoticeIds, noticeList);
            EncryptionPlayerPrefs.Save();
        }

        void IDisplayedInGameNoticeRepository.DeleteDisplayedDailyNoticeIdHashSet()
        {
            EncryptionPlayerPrefs.DeleteKey(KeyDisplayedDailyInGameNoticeIds);
            EncryptionPlayerPrefs.Save();
        }

        HashSet<NoticeId> IDisplayedInGameNoticeRepository.DisplayedWeeklyNoticeIdHashSet
        {
            get
            {
                var list = EncryptionPlayerPrefs.GetList(KeyDisplayedWeeklyInGameNoticeIds, new List<string>());
                return list.Select(id => new NoticeId(id)).ToHashSet();
            }
        }

        void IDisplayedInGameNoticeRepository.AddDisplayedWeeklyNoticeId(NoticeId noticeId)
        {
            var noticeList = EncryptionPlayerPrefs.GetList(KeyDisplayedWeeklyInGameNoticeIds, new List<string>());
            noticeList.Add(noticeId.Value);
            EncryptionPlayerPrefs.SetList(KeyDisplayedWeeklyInGameNoticeIds, noticeList);
            EncryptionPlayerPrefs.Save();
        }

        void IDisplayedInGameNoticeRepository.DeleteDisplayedWeeklyNoticeIdHashSet()
        {
            EncryptionPlayerPrefs.DeleteKey(KeyDisplayedWeeklyInGameNoticeIds);
            EncryptionPlayerPrefs.Save();
        }

        HashSet<NoticeId> IDisplayedInGameNoticeRepository.DisplayedMonthlyNoticeIdHashSet
        {
            get
            {
                var list = EncryptionPlayerPrefs.GetList(KeyDisplayedMonthlyInGameNoticeIds, new List<string>());
                return list.Select(id => new NoticeId(id)).ToHashSet();
            }
        }

        void IDisplayedInGameNoticeRepository.AddDisplayedMonthlyNoticeId(NoticeId noticeId)
        {
            var noticeList = EncryptionPlayerPrefs.GetList(KeyDisplayedMonthlyInGameNoticeIds, new List<string>());
            noticeList.Add(noticeId.Value);
            EncryptionPlayerPrefs.SetList(KeyDisplayedMonthlyInGameNoticeIds, noticeList);
            EncryptionPlayerPrefs.Save();
        }

        void IDisplayedInGameNoticeRepository.DeleteDisplayedMonthlyNoticeIdHashSet()
        {
            EncryptionPlayerPrefs.DeleteKey(KeyDisplayedMonthlyInGameNoticeIds);
            EncryptionPlayerPrefs.Save();
        }

        HashSet<NoticeId> IDisplayedInGameNoticeRepository.DisplayedOnceNoticeIdHashSet
        {
            get
            {
                var list = EncryptionPlayerPrefs.GetList(KeyDisplayedOnceInGameNoticeIds, new List<string>());
                return list.Select(id => new NoticeId(id)).ToHashSet();
            }
        }

        void IDisplayedInGameNoticeRepository.AddDisplayedOnceNoticeId(NoticeId noticeId)
        {
            var noticeList = EncryptionPlayerPrefs.GetList(KeyDisplayedOnceInGameNoticeIds, new List<string>());
            noticeList.Add(noticeId.Value);
            EncryptionPlayerPrefs.SetList(KeyDisplayedOnceInGameNoticeIds, noticeList);
            EncryptionPlayerPrefs.Save();
        }

        public Dictionary<AnnouncementId, AnnouncementLastUpdateAt> ReadAnnouncementIdAndLastUpdated
        {
            get
            {
                if(_cachedReadAnnouncementIdAndLastUpdated != null)
                {
                    return _cachedReadAnnouncementIdAndLastUpdated;
                }

                var dictionary = EncryptionPlayerPrefs.GetDictionary(KeyReadAnnouncementIds, new Dictionary<string, DateTimeOffset>());
                _cachedReadAnnouncementIdAndLastUpdated = dictionary.ToDictionary(
                    x => new AnnouncementId(x.Key),
                    x => new AnnouncementLastUpdateAt(x.Value));
                return _cachedReadAnnouncementIdAndLastUpdated;
            }
            private set
            {
                var dictionary = value.ToDictionary(
                    x => (string)x.Key.Value,
                    x => (DateTimeOffset)x.Value.Value
                );
                EncryptionPlayerPrefs.SetDictionary(KeyReadAnnouncementIds, dictionary);
                EncryptionPlayerPrefs.Save();

                _cachedReadAnnouncementIdAndLastUpdated = value;
            }
        }
        public void SetReadAnnouncementIdAndLastUpdated(Dictionary<AnnouncementId, AnnouncementLastUpdateAt> announcementIdAndLastUpdated)
        {
            ReadAnnouncementIdAndLastUpdated = announcementIdAndLastUpdated;
        }

        public void RemoveReadAnnouncementIdAndLastUpdated(IReadOnlyList<AnnouncementId> announcementIdsToRemoveFromRead)
        {
            var updatedDictionary = ReadAnnouncementIdAndLastUpdated
                .Where(pair => !announcementIdsToRemoveFromRead.Contains(pair.Key))
                .ToDictionary(pair => pair.Key, pair => pair.Value);
            ReadAnnouncementIdAndLastUpdated = updatedDictionary;
        }

        public int BeginnerMissionReleaseDayNumber
        {
            get => EncryptionPlayerPrefs.GetInt(KeyBeginnerMissionReleaseDayNumber, 1);
            set
            {
                EncryptionPlayerPrefs.SetInt(KeyBeginnerMissionReleaseDayNumber, value);
                EncryptionPlayerPrefs.Save();
            }
        }

        public void SetBeginnerMissionReleaseDayNumber(int dayNumber)
        {
            BeginnerMissionReleaseDayNumber = dayNumber;
        }

        public MasterDataId CurrentHomeTopSelectMstQuestId
        {
            get
            {
                var id = EncryptionPlayerPrefs.GetString("GLOW/CurrentSelectMstQuestId", "");
                return string.IsNullOrEmpty(id) ? MasterDataId.Empty : new MasterDataId(id);
            }
            private set
            {
                EncryptionPlayerPrefs.SetString("GLOW/CurrentSelectMstQuestId", value.Value);
                EncryptionPlayerPrefs.Save();
            }
        }

        public MasterDataId LastPlayedMstStageId
        {
            get
            {
                var id = EncryptionPlayerPrefs.GetString("GLOW/LastPlayedMstStageId", "");
                return string.IsNullOrEmpty(id) ? MasterDataId.Empty : new MasterDataId(id);
            }
            private set
            {
                EncryptionPlayerPrefs.SetString("GLOW/LastPlayedMstStageId", value.Value);
                EncryptionPlayerPrefs.Save();
            }
        }

        void IQuestStageReleaseAnimationRepository.SaveForHomeTop(ShowReleaseAnimationStatus status)
        {
            EncryptionPlayerPrefs.SetString("GLOW/QuestReleaseAnimation", status.NewReleaseMstQuestId.Value);
            EncryptionPlayerPrefs.SetString("GLOW/StageReleaseAnimation", status.NewReleaseMstStageId.Value);
            EncryptionPlayerPrefs.Save();
        }

        ShowReleaseAnimationStatus IQuestStageReleaseAnimationRepository.GetForHomeTop()
        {
            var mstQuestId = EncryptionPlayerPrefs.GetString("GLOW/QuestReleaseAnimation","");
            var mstStageId = EncryptionPlayerPrefs.GetString("GLOW/StageReleaseAnimation","");
            return new ShowReleaseAnimationStatus(
                string.IsNullOrEmpty(mstQuestId) ? MasterDataId.Empty : new MasterDataId(mstQuestId),
                string.IsNullOrEmpty(mstStageId) ? MasterDataId.Empty : new MasterDataId(mstStageId)
            );
        }
        void IQuestStageReleaseAnimationRepository.SaveForEventStageSelect(ShowReleaseAnimationStatus status)
        {
            EncryptionPlayerPrefs.SetString("GLOW/EventQuestReleaseAnimation", status.NewReleaseMstQuestId.Value);
            EncryptionPlayerPrefs.SetString("GLOW/EventStageReleaseAnimation", status.NewReleaseMstStageId.Value);
            EncryptionPlayerPrefs.Save();
        }

        ShowReleaseAnimationStatus IQuestStageReleaseAnimationRepository.GetForEventStageSelect()
        {
            var mstQuestId = EncryptionPlayerPrefs.GetString("GLOW/EventQuestReleaseAnimation","");
            var mstStageId = EncryptionPlayerPrefs.GetString("GLOW/EventStageReleaseAnimation","");
            return new ShowReleaseAnimationStatus(
                string.IsNullOrEmpty(mstQuestId) ? MasterDataId.Empty : new MasterDataId(mstQuestId),
                string.IsNullOrEmpty(mstStageId) ? MasterDataId.Empty : new MasterDataId(mstStageId)
            );
        }

        void IQuestStageReleaseAnimationRepository.DeleteAtNormal()
        {
            EncryptionPlayerPrefs.SetString("GLOW/QuestReleaseAnimation", "");
            EncryptionPlayerPrefs.SetString("GLOW/StageReleaseAnimation", "");
            EncryptionPlayerPrefs.Save();
        }
        void IQuestStageReleaseAnimationRepository.DeleteAtEvent()
        {
            EncryptionPlayerPrefs.SetString("GLOW/EventQuestReleaseAnimation", "");
            EncryptionPlayerPrefs.SetString("GLOW/EventStageReleaseAnimation", "");
            EncryptionPlayerPrefs.Save();
        }


        List<MasterDataId> IOutpostArtworkBadgeRepository.DisplayedOutpostArtworkIds
        {
            get
            {
                var list = EncryptionPlayerPrefs.GetList(KeyDisplayedOutpostArtworkIds, new List<string>());
                return list.Select(id => new MasterDataId(id)).ToList();
            }
            set
            {
                var list = value.Select(id => id.Value.ToString()).ToList();
                EncryptionPlayerPrefs.SetList(KeyDisplayedOutpostArtworkIds, list);
                EncryptionPlayerPrefs.Save();
            }
        }

        List<MasterDataId> IUserProfileBadgeRepository.DisplayedUserProfileAvatarIds
        {
            get
            {
                var list = EncryptionPlayerPrefs.GetList(KeyDisplayedUserProfileAvatarIds, new List<string>());
                return list.Select(id => new MasterDataId(id)).ToList();
            }
            set
            {
                var list = value.Select(id => id.Value.ToString()).ToList();
                EncryptionPlayerPrefs.SetList(KeyDisplayedUserProfileAvatarIds, list);
                EncryptionPlayerPrefs.Save();
            }
        }

        List<MasterDataId> IUserEmblemBadgeRepository.DisplayedUserEmblemIds
        {
            get
            {
                var list = EncryptionPlayerPrefs.GetList(KeyDisplayedUserEmblemIds, new List<string>());
                return list.Select(id => new MasterDataId(id)).ToList();
            }
            set
            {
                var list = value.Select(id => id.Value.ToString()).ToList();
                EncryptionPlayerPrefs.SetList(KeyDisplayedUserEmblemIds, list);
                EncryptionPlayerPrefs.Save();
            }
        }

        MasterDataId IPreferenceRepository.AdventBattleRankingResultAnimationPlayedId
        {
            get
            {
                var adventBattleRankingResultAnimationPlayedId = EncryptionPlayerPrefs.GetString(KeyAdventBattleRankingResultAnimationPlayedId,"");
                return string.IsNullOrEmpty(adventBattleRankingResultAnimationPlayedId)
                    ? MasterDataId.Empty
                    : new MasterDataId(adventBattleRankingResultAnimationPlayedId);
            }
        }

        void IPreferenceRepository.SetAdventBattleRankingResultAnimationPlayedId(MasterDataId id)
        {
            EncryptionPlayerPrefs.SetString(KeyAdventBattleRankingResultAnimationPlayedId, id.Value);
            EncryptionPlayerPrefs.Save();
        }

        DateTimeOffset IPreferenceRepository.GachaListViewLastOpenedDateTimeOffset
        {
            get
            {
                var lastOpened = EncryptionPlayerPrefs.GetString("GLOW/GachaListViewLastOpenedDateTimeOffset", "");
                if(string.IsNullOrEmpty(lastOpened)) return DateTimeOffset.MinValue;

                return GetParseToDateTimeOffset(lastOpened);
            }
        }

        void IPreferenceRepository.SetGachaListViewLastOpenedDateTimeOffset(DateTimeOffset dateTimeOffset)
        {
            EncryptionPlayerPrefs.SetString("GLOW/GachaListViewLastOpenedDateTimeOffset", dateTimeOffset.ToString());
            EncryptionPlayerPrefs.Save();
        }

        List<MasterDataId> IPreferenceRepository.SelectedMstQuestIds
        {
            get
            {
                var list = EncryptionPlayerPrefs.GetList("GLOW/SelectedMstQuestIds", new List<string>());
                return list.Select(id => new MasterDataId(id)).ToList();
            }
        }

        void IPreferenceRepository.AddSelectedMstQuestId(MasterDataId mstQuestId)
        {
            var list = EncryptionPlayerPrefs.GetList("GLOW/SelectedMstQuestIds", new List<string>());
            list.Add(mstQuestId.Value);
            EncryptionPlayerPrefs.SetList("GLOW/SelectedMstQuestIds", list);
            EncryptionPlayerPrefs.Save();
        }

        UserMyId IPreferenceRepository.UserMyId
        {
            get
            {
                var userMyId = EncryptionPlayerPrefs.GetString(KeyMyId, "");
                return string.IsNullOrEmpty(userMyId) ?
                    UserMyId.Empty :
                    new UserMyId(userMyId);
            }
        }

        void IPreferenceRepository.SetUserMyId(UserMyId userMyId)
        {
            var id = userMyId.ToString();
            EncryptionPlayerPrefs.SetString(KeyMyId, id);
            EncryptionPlayerPrefs.Save();
        }

        InAppPurchaseFakeStoreMode IPreferenceRepository.InAppPurchaseFakeStoreMode
        {
            get => (InAppPurchaseFakeStoreMode)EncryptionPlayerPrefs.GetInt(KeyInAppPurchaseFakeStoreMode, (int)InAppPurchaseFakeStoreMode.Default);
            set
            {
                EncryptionPlayerPrefs.SetInt(KeyInAppPurchaseFakeStoreMode, (int)value);
                EncryptionPlayerPrefs.Save();
            }
        }

        BattleSpeed IInGamePreferenceRepository.InGameBattleSpeed
        {
            get => (BattleSpeed)EncryptionPlayerPrefs.GetInt(KeyInGameBattleSpeed, (int)BattleSpeed.x1);
            set
            {
                EncryptionPlayerPrefs.SetInt(KeyInGameBattleSpeed, (int)value);
                EncryptionPlayerPrefs.Save();
            }
        }

        InGameAutoEnabledFlag IInGamePreferenceRepository.IsInGameAutoEnabled
        {
            get => new InGameAutoEnabledFlag(EncryptionPlayerPrefs.GetInt(KeyInGameAutoEnabled, 0) != 0);
            set
            {
                EncryptionPlayerPrefs.SetInt(KeyInGameAutoEnabled, value.Value ? 1 : 0);
                EncryptionPlayerPrefs.Save();
            }
        }

        InGameContinueSelectingFlag IInGamePreferenceRepository.IsInGameContinueSelecting
        {
            get => new InGameContinueSelectingFlag(EncryptionPlayerPrefs.GetInt(KeyInGameContinueSelecting, 0) != 0);
            set
            {
                EncryptionPlayerPrefs.SetInt(KeyInGameContinueSelecting, value.Value ? 1 : 0);
                EncryptionPlayerPrefs.Save();
            }
        }

        DateTimeOffset IAnnouncementPreferenceRepository.AnnouncementLastDisplayDateTimeOffsetAtLogin
        {
            get
            {
                var lastShowed = EncryptionPlayerPrefs.GetString(KeyAnnouncementLastDisplayDateTimeOffset, "");
                if(string.IsNullOrEmpty(lastShowed)) return DateTimeOffset.MinValue;

                return GetParseToDateTimeOffset(lastShowed);
            }
        }

        void IAnnouncementPreferenceRepository.SetAnnouncementLastDisplayDateTimeOffsetAtLogin(DateTimeOffset announcementLastDisplayDateTimeOffset)
        {
            EncryptionPlayerPrefs.SetString(KeyAnnouncementLastDisplayDateTimeOffset, announcementLastDisplayDateTimeOffset.ToString());
            EncryptionPlayerPrefs.Save();
        }

        AnnouncementLastUpdateAt IAnnouncementPreferenceRepository.InformationLastUpdated
        {
            get
            {
                var lastUpdated = EncryptionPlayerPrefs.GetString(KeyAnnouncementInformationLastUpdatedAt, "");
                if (string.IsNullOrEmpty(lastUpdated)) return AnnouncementLastUpdateAt.Empty;

                return new AnnouncementLastUpdateAt(GetParseToDateTimeOffset(lastUpdated));
            }
        }

        void IAnnouncementPreferenceRepository.SetInformationLastUpdated(AnnouncementLastUpdateAt informationLastUpdated)
        {
            EncryptionPlayerPrefs.SetString(KeyAnnouncementInformationLastUpdatedAt, informationLastUpdated.ToString());
            EncryptionPlayerPrefs.Save();
        }

        AnnouncementLastUpdateAt IAnnouncementPreferenceRepository.OperationLastUpdated
        {
            get
            {
                var lastUpdated = EncryptionPlayerPrefs.GetString(KeyAnnouncementOperationLastUpdatedAt, "");
                if (string.IsNullOrEmpty(lastUpdated)) return AnnouncementLastUpdateAt.Empty;

                return new AnnouncementLastUpdateAt(GetParseToDateTimeOffset(lastUpdated));
            }
        }

        void IAnnouncementPreferenceRepository.SetOperationLastUpdated(AnnouncementLastUpdateAt operationLastUpdated)
        {
            EncryptionPlayerPrefs.SetString(KeyAnnouncementOperationLastUpdatedAt, operationLastUpdated.ToString());
            EncryptionPlayerPrefs.Save();
        }

        AnnouncementLastUpdateAt IAnnouncementPreferenceRepository.ReadInformationLastUpdated
        {
            get
            {
                var lastUpdated = EncryptionPlayerPrefs.GetString(KeyReadAnnouncementInformationLastUpdatedAt, "");
                if (string.IsNullOrEmpty(lastUpdated)) return AnnouncementLastUpdateAt.Empty;

                return new AnnouncementLastUpdateAt(GetParseToDateTimeOffset(lastUpdated));
            }
        }

        void IAnnouncementPreferenceRepository.SetReadInformationLastUpdated(AnnouncementLastUpdateAt informationLastUpdated)
        {
            EncryptionPlayerPrefs.SetString(KeyReadAnnouncementInformationLastUpdatedAt, informationLastUpdated.ToString());
            EncryptionPlayerPrefs.Save();
        }

        AnnouncementLastUpdateAt IAnnouncementPreferenceRepository.ReadOperationLastUpdated
        {
            get
            {
                var lastUpdated = EncryptionPlayerPrefs.GetString(KeyReadAnnouncementOperationLastUpdatedAt, "");
                if (string.IsNullOrEmpty(lastUpdated)) return AnnouncementLastUpdateAt.Empty;

                return new AnnouncementLastUpdateAt(GetParseToDateTimeOffset(lastUpdated));
            }
        }

        void IAnnouncementPreferenceRepository.SetReadOperationLastUpdated(AnnouncementLastUpdateAt operationLastUpdated)
        {
            EncryptionPlayerPrefs.SetString(KeyReadAnnouncementOperationLastUpdatedAt, operationLastUpdated.ToString());
            EncryptionPlayerPrefs.Save();
        }

        AlreadyReadAnnouncementFlag IAnnouncementPreferenceRepository.AnnouncementAlreadyReadAll
        {
            get
            {
                var savedValue = EncryptionPlayerPrefs.GetInt(KeyAnnouncementReadAll, 1);
                return (savedValue == 0) ? AlreadyReadAnnouncementFlag.False : AlreadyReadAnnouncementFlag.True;
            }
        }

        void IAnnouncementPreferenceRepository.SetAnnouncementAlreadyReadAll(AlreadyReadAnnouncementFlag announcementReadAll)
        {
            var saveValue = announcementReadAll.Value ? 1 : 0;
            EncryptionPlayerPrefs.SetInt(KeyAnnouncementReadAll, saveValue);
            EncryptionPlayerPrefs.Save();
        }

        AdventBattleRaidTotalScoreModel IAdventBattlePreferenceRepository.EvaluatedRaidTotalScoreModelForRewards
        {
            get
            {
                var adventBattleId = EncryptionPlayerPrefs.GetString(KeyReceivedRaidRewardAdventBattleIdsEvaluated, "");
                if (adventBattleId == "")
                {
                    return AdventBattleRaidTotalScoreModel.Empty;
                }

                var totalScoreRewardsEvaluated = long.Parse(
                    EncryptionPlayerPrefs.GetString(KeyAdventBattleRaidTotalScoreRewardsEvaluated, "0"),
                    CultureInfo.InvariantCulture);

                return new AdventBattleRaidTotalScoreModel(new MasterDataId(adventBattleId), new AdventBattleRaidTotalScore(totalScoreRewardsEvaluated));
            }
        }

        void IAdventBattlePreferenceRepository.SetEvaluatedRaidTotalScoreModelForRewards(AdventBattleRaidTotalScoreModel model)
        {
            EncryptionPlayerPrefs.SetString(KeyReceivedRaidRewardAdventBattleIdsEvaluated, model.MstAdventBattleId.Value);
            EncryptionPlayerPrefs.SetString(KeyAdventBattleRaidTotalScoreRewardsEvaluated,
                model.AdventBattleRaidTotalScore.Value.ToString());
            EncryptionPlayerPrefs.Save();
        }

        Dictionary<LocalNotificationType, LocalNotificationIdentifier> IPreferenceRepository.LocalNotificationScheduledIdentifiers
        {
            get
            {
                var keyValuePairs = EncryptionPlayerPrefs
                    .GetDictionary(
                        KeyLocalNotificationScheduledIdentifiers,
                        new Dictionary<string, string>())
                    .Select(x =>
                    {
                        var type = (LocalNotificationType)Enum.Parse(typeof(LocalNotificationType), x.Key);
                        var identifier = new LocalNotificationIdentifier(x.Value);
                        return new KeyValuePair<LocalNotificationType, LocalNotificationIdentifier>(type, identifier);
                    });
                return keyValuePairs.ToDictionary(
                    x => x.Key,
                    x => x.Value);
            }
            set
            {
                // NOTE: C#が持っているclassやプリミティブな型で保存を行う
                var dictionary = value.ToDictionary(
                    x => x.Key.ToString(),
                    x => x.Value.ToString());
                EncryptionPlayerPrefs.SetDictionary(KeyLocalNotificationScheduledIdentifiers, dictionary);
                EncryptionPlayerPrefs.Save();
            }
        }

        Dictionary<MasterDataId, MasterDataId> IPreferenceRepository.LastPlayedEventAtMstQuestGroupIds
        {
            get
            {
                var dictionary = EncryptionPlayerPrefs.GetDictionary(LastPlayedEventAtMstQuestGroupIds, new Dictionary<string, string>());
                return dictionary.ToDictionary(
                    x => new MasterDataId(x.Key),
                    x => new MasterDataId(x.Value));
            }
        }

        void IPreferenceRepository.SetLastPlayedEventAtMstQuestId(MasterDataId mstQuestGroupId, MasterDataId mstStageId)
        {
            var dictionary = EncryptionPlayerPrefs.GetDictionary(LastPlayedEventAtMstQuestGroupIds, new Dictionary<string, string>());
            //無かったら追加
            if (!dictionary.ContainsKey(mstQuestGroupId.Value))
            {
                dictionary.Add(mstQuestGroupId.Value, mstStageId.Value);
            }
            dictionary[mstQuestGroupId.Value] = mstStageId.Value;
            EncryptionPlayerPrefs.SetDictionary(LastPlayedEventAtMstQuestGroupIds, dictionary);
            EncryptionPlayerPrefs.Save();
        }

        bool IPreferenceRepository.ShouldStartOutpostEnhanceTutorial
        {
            get => EncryptionPlayerPrefs.GetInt(KeyShouldStartOutpostEnhanceTutorial, 0) != 0;
            set
            {
                // trueなら1、falseなら0を保存
                EncryptionPlayerPrefs.SetInt(KeyShouldStartOutpostEnhanceTutorial, value ? 1 : 0);
                EncryptionPlayerPrefs.Save();
            }
        }

        InAppReviewFlag IInAppReviewPreferenceRepository.IsAppReviewDisplayedAfterGachaUrDrawn
        {
            get
            {
                var flag = EncryptionPlayerPrefs.GetInt(KeyInAppReviewAfterGachaUrDrawn, 0);
                return new InAppReviewFlag(flag != 0);
            }
        }

        void IInAppReviewPreferenceRepository.SetIsAppReviewDisplayedAfterGachaUrDrawn(InAppReviewFlag flag)
        {
            EncryptionPlayerPrefs.SetInt(KeyInAppReviewAfterGachaUrDrawn, flag == InAppReviewFlag.True ? 1 : 0);
            EncryptionPlayerPrefs.Save();
        }


        void IContentTopAccessPreferenceRepository.SaveAccessTime(DateTimeOffset accessDateTimeOffset)
        {
            EncryptionPlayerPrefs.SetString(KeyContentTopAccessTime, accessDateTimeOffset.ToString());
            EncryptionPlayerPrefs.Save();
        }

        List<MasterDataId> IOpenedMessagePreferenceRepository.OpenedMessageIds
        {
            get
            {
                if (_cachedOpenedMessageIds != null)
                {
                    return _cachedOpenedMessageIds;
                }

                var openedMessageIds = EncryptionPlayerPrefs.GetList(KeyOpenedMessageIds, new List<string>());
                _cachedOpenedMessageIds = openedMessageIds.Select(id => new MasterDataId(id)).ToList();
                return _cachedOpenedMessageIds;
            }
        }

        void IOpenedMessagePreferenceRepository.SetOpenedMessageIds(List<MasterDataId> messageIds)
        {
            var messageIdList = messageIds.Select(id => id.ToString()).ToList();
            EncryptionPlayerPrefs.SetList(KeyOpenedMessageIds, messageIdList);
            EncryptionPlayerPrefs.Save();

            // キャッシュも更新
            _cachedOpenedMessageIds = messageIds;
        }

        void IOpenedMessagePreferenceRepository.ClearOpenedMessageIds()
        {
            EncryptionPlayerPrefs.DeleteKey(KeyOpenedMessageIds);
            EncryptionPlayerPrefs.Save();

            // キャッシュもクリア
            _cachedOpenedMessageIds = null;
        }

        bool IContentTopAccessPreferenceRepository.HasValue => EncryptionPlayerPrefs.HasKey(KeyContentTopAccessTime);

        DateTimeOffset IContentTopAccessPreferenceRepository.GetLastAccessTime()
        {
            var accessTime = EncryptionPlayerPrefs.GetString(KeyContentTopAccessTime, "");
            if (string.IsNullOrEmpty(accessTime)) return DateTimeOffset.MinValue;

            return GetParseToDateTimeOffset(accessTime);
        }

        void IPreferenceRepository.DeleteAll()
        {
            // NOTE: ユーザーデータ関連の削除
            EncryptionPlayerPrefs.DeleteAll();
            EncryptionPlayerPrefs.Save();

            // キャッシュもクリア
            _cachedDisplayedShopProductIdHashSet = null;
            _cachedDisplayedOprPackProductIds = null;
            _cachedReadAnnouncementIdAndLastUpdated = null;
            _cachedOpenedMessageIds = null;
        }

        #region IShopProductCacheRepository
        DateTimeOffset IShopProductCacheRepository.LastCheckedNewShopProductDateTimeOffset
        {
            get
            {
                var lastCheckedNewShopProductDateTimeOffset = EncryptionPlayerPrefs.GetString(KeyLastCheckedNewShopProductsDateTimeOffset, "");
                if (string.IsNullOrEmpty(lastCheckedNewShopProductDateTimeOffset)) return DateTimeOffset.MinValue;

                return GetParseToDateTimeOffset(lastCheckedNewShopProductDateTimeOffset);
            }
        }

        void IShopProductCacheRepository.SaveLastCheckedNewShopProductDateTimeOffset(DateTimeOffset lastCheckedNewShopProductDateTimeOffset)
        {
            EncryptionPlayerPrefs.SetString(KeyLastCheckedNewShopProductsDateTimeOffset, lastCheckedNewShopProductDateTimeOffset.ToString());
            EncryptionPlayerPrefs.Save();
        }

        public HashSet<MasterDataId> DisplayedShopProductIdHashSet
        {
            get
            {
                if (_cachedDisplayedShopProductIdHashSet != null)
                {
                    return _cachedDisplayedShopProductIdHashSet;
                }

                _cachedDisplayedShopProductIdHashSet = EncryptionPlayerPrefs
                    .GetList(KeyDisplayedShopProductIds, new List<string>())
                    .Select(id => new MasterDataId(id))
                    .ToHashSet();

                return _cachedDisplayedShopProductIdHashSet;
            }
            private set
            {
                var productIdList = value.Select(id => id.ToString()).ToList();
                EncryptionPlayerPrefs.SetList(KeyDisplayedShopProductIds, productIdList);
                EncryptionPlayerPrefs.Save();

                // キャッシュも更新
                _cachedDisplayedShopProductIdHashSet = value;
            }
        }

        void IShopProductCacheRepository.SetDisplayedShopProductIdHashSet(HashSet<MasterDataId> productIdHashSet)
        {
            DisplayedShopProductIdHashSet = productIdHashSet;
        }

        void IShopProductCacheRepository.AddDisplayedShopProductIds(IReadOnlyCollection<MasterDataId> productIds)
        {
            var currentDisplayedIds = DisplayedShopProductIdHashSet;
            currentDisplayedIds.UnionWith(productIds);
            DisplayedShopProductIdHashSet = currentDisplayedIds;
        }

        List<MasterDataId> IShopProductCacheRepository.DisplayedOprPackProductIds
        {
            get
            {
                if (_cachedDisplayedOprPackProductIds != null)
                {
                    return _cachedDisplayedOprPackProductIds;
                }

                var oprPackProductIds = EncryptionPlayerPrefs.GetList(KeyDisplayedOprPackProductIds, new List<string>());
                _cachedDisplayedOprPackProductIds = oprPackProductIds.Select(id => new MasterDataId(id)).ToList();
                return _cachedDisplayedOprPackProductIds;
            }
            set
            {
                var productIdList = value.Select(id => id.ToString()).ToList();
                EncryptionPlayerPrefs.SetList(KeyDisplayedOprPackProductIds, productIdList);
                EncryptionPlayerPrefs.Save();

                // キャッシュも更新
                _cachedDisplayedOprPackProductIds = value;
            }
        }

        #endregion

#region IDeferredPurchaseCacheRepository
        void IDeferredPurchaseCacheRepository.AddRestorePurchaseResult(PurchaseResultCacheModel model)
        {
            AddPurchaseResult(model, KeyRestorePurchaseResult);
        }

        IReadOnlyList<PurchaseResultCacheModel> IDeferredPurchaseCacheRepository.GetAndResetRestorePurchaseResults()
        {
            return GetAndResetPurchaseResults(KeyRestorePurchaseResult);
        }

        void IDeferredPurchaseCacheRepository.AddDeferredPurchaseResult(PurchaseResultCacheModel model)
        {
            AddPurchaseResult(model, KeyDeferredPurchaseResult);
        }

        IReadOnlyList<PurchaseResultCacheModel> IDeferredPurchaseCacheRepository.GetAndResetDeferredPurchaseResults()
        {
            return GetAndResetPurchaseResults(KeyDeferredPurchaseResult);
        }

        void IDeferredPurchaseCacheRepository.AddDeferredPurchaseErrorCode(DeferredPurchaseErrorCode errorCode)
        {
            var list = EncryptionPlayerPrefs.GetList(KeyDeferredPurchaseErrorCode, new List<int>());
            list.Add(errorCode.Value);
            EncryptionPlayerPrefs.SetList(KeyDeferredPurchaseErrorCode, list);
            EncryptionPlayerPrefs.Save();
        }

        IReadOnlyList<DeferredPurchaseErrorCode> IDeferredPurchaseCacheRepository.GetAndResetDeferredPurchaseErrorCode()
        {
            var list = EncryptionPlayerPrefs.GetList(KeyDeferredPurchaseErrorCode, new List<int>())
                .Select(code => new DeferredPurchaseErrorCode(code))
                .ToList();
            EncryptionPlayerPrefs.DeleteKey(KeyDeferredPurchaseErrorCode);
            EncryptionPlayerPrefs.Save();
            return list;
        }

        void AddPurchaseResult(PurchaseResultCacheModel model, string key)
        {
            var list = EncryptionPlayerPrefs.GetList(key, new List<string>());

            var serializer = GetPurchaseResultSerializer();
            using (var textWriter = new System.IO.StringWriter())
            {
                serializer.Serialize(textWriter, model, typeof(PurchaseResultCacheModel));
                list.Add(textWriter.ToString());
            }
            EncryptionPlayerPrefs.SetList(key, list);
            EncryptionPlayerPrefs.Save();
        }

        IReadOnlyList<PurchaseResultCacheModel> GetAndResetPurchaseResults(string key)
        {
            var list = EncryptionPlayerPrefs.GetList(key, new List<string>());
            // デシリアライズに失敗するようになると無限ロードになるので、先に削除しておく
            EncryptionPlayerPrefs.DeleteKey(key);
            EncryptionPlayerPrefs.Save();

            var serializer = GetPurchaseResultSerializer();
            var purchaseResults = list
                    .Select(result =>
                    {
                        using var stringReader = new System.IO.StringReader(result);
                        using var jsonReader = new JsonTextReader(stringReader);
                        return serializer.Deserialize<PurchaseResultCacheModel>(jsonReader);
                    })
                .ToList();
            return purchaseResults;
        }

        JsonSerializer GetPurchaseResultSerializer()
        {
            var serializer = new JsonSerializer();
            serializer.Converters.Add(new PreConversionResourceModelJsonConverter());
            serializer.Converters.Add(new ObscuredStringJsonConverter());
            serializer.Converters.Add(new ObscuredIntJsonConverter());
            return serializer;
        }
#endregion

        // 海外のパース出来ない形式の時間Formatを加味したDateTimeOffset変換
        DateTimeOffset GetParseToDateTimeOffset (string time)
        {
            return DateTimeOffset.Parse(time, CultureInfo.InvariantCulture);
        }
    }
}
