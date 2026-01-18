using System;
using System.Collections.Generic;
using GLOW.Core.Constants.LocalNotification;
using GLOW.Core.Domain.Constants.Shop;
using GLOW.Core.Domain.ValueObjects;

namespace GLOW.Core.Domain.Repositories
{
    public interface IPreferenceRepository
    {
        PartyNo SelectPartyNo { get; set; }
        
        MasterDataId LastPlayedMstStageId { get; }
        void SetLastPlayedMstStageId(MasterDataId mstStageId);

        MasterDataId CurrentHomeTopSelectMstQuestId{ get;}
        void SetCurrentHomeTopSelectMstQuestId(MasterDataId mstQuestId);

        int BeginnerMissionReleaseDayNumber { get; }
        void SetBeginnerMissionReleaseDayNumber(int dayNumber);

        MasterDataId AdventBattleRankingResultAnimationPlayedId { get; }
        void SetAdventBattleRankingResultAnimationPlayedId(MasterDataId id);

        DateTimeOffset GachaListViewLastOpenedDateTimeOffset { get; }
        void SetGachaListViewLastOpenedDateTimeOffset(DateTimeOffset dateTimeOffset);

        List<MasterDataId> SelectedMstQuestIds { get; }
        void AddSelectedMstQuestId(MasterDataId mstQuestId);

        UserMyId UserMyId { get;}
        void SetUserMyId(UserMyId userMyId);

        InAppPurchaseFakeStoreMode InAppPurchaseFakeStoreMode { get; set; }

        Dictionary<LocalNotificationType, LocalNotificationIdentifier> LocalNotificationScheduledIdentifiers { get; set; }

        Dictionary<MasterDataId, MasterDataId> LastPlayedEventAtMstQuestGroupIds { get; }
        void SetLastPlayedEventAtMstQuestId(MasterDataId mstQuestGroupId, MasterDataId mstStageId);
        
        bool ShouldStartOutpostEnhanceTutorial { get; set; }
        
        void DeleteAll();
    }
}
