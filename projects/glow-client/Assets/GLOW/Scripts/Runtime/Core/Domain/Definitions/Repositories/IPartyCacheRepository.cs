using System.Collections.Generic;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.Models.Party;
using GLOW.Core.Domain.ValueObjects;

namespace GLOW.Core.Domain.Repositories
{
    public interface IPartyCacheRepository
    {
        UserPartyCacheModel GetCurrentPartyModel();
        void SetParties(IReadOnlyList<UserPartyModel> userPartyModels, PartyNo selectParty);
        void SetPartyMemberSlotCount(PartyMemberSlotCount slotCount);
        void SetSelectPartyNo(PartyNo selectParty);
        void SetUnitList(PartyNo partyNo, IReadOnlyList<UserDataId> userUnitIds);
        void UpdateParty(PartyNo partyNo, PartyName name, IReadOnlyList<UserDataId> partyMembers);
        UserPartyCacheModel GetCacheParty(PartyNo partyNo);
        IReadOnlyList<UserDataId> GetUnitList(PartyNo partyNo);
        IReadOnlyList<UserPartyCacheModel> GetNeedsApplyParty(IReadOnlyList<UserPartyModel> originalParties);
        IReadOnlyList<UserPartyCacheModel> GetAllParties();

        // 編成時のボーナス対象クエスト
        // NOTE:パーティ編成への付加情報が増えたら別Repository等に分離する
        public void SetBonusUnits(IReadOnlyList<PartyBonusUnitModel> bonusUnits);
        public IReadOnlyList<PartyBonusUnitModel> GetBonusUnits();
        public void ResetBonusUnits();
    }
}
