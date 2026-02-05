using System.Collections.Generic;
using System.Linq;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.Models.Party;
using GLOW.Core.Domain.Repositories;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Extensions;

namespace GLOW.Core.Data.Repositories
{
    public class PartyCacheRepository : IPartyCacheRepository
    {
        List<UserPartyCacheModel> _userPartyModels = new();

        List<UserDataId> _userUnitIds = new ();
        PartyNo _unitListPartyNo;
        PartyNo _selectParty = PartyNo.One;
        // 有効なパーティ内スロット数
        PartyMemberSlotCount _partyMemberSlotCount = PartyMemberSlotCount.Max;
        // 編成時のボーナス対象
        IReadOnlyList<PartyBonusUnitModel> _partyBonusUnitModels = new List<PartyBonusUnitModel>();

        UserPartyCacheModel IPartyCacheRepository.GetCurrentPartyModel()
        {
            return _userPartyModels.FirstOrDefault(x => x.PartyNo == _selectParty, UserPartyCacheModel.Empty);
        }

        public void SetParties(IReadOnlyList<UserPartyModel> userPartyModels, PartyNo selectParty)
        {
            SetSelectPartyNo(selectParty);
            _userPartyModels = (userPartyModels)
                .Select(party => UserPartyCacheModel.Create(party, _partyMemberSlotCount))
                .ToList();
            UpdatePartyMemberSlotCount();
        }

        public void SetPartyMemberSlotCount(PartyMemberSlotCount slotCount)
        {
            _partyMemberSlotCount = slotCount;
            UpdatePartyMemberSlotCount();
        }

        void UpdatePartyMemberSlotCount()
        {
            for(var i = 0 ; i < _userPartyModels.Count ; ++i)
            {
                var model = _userPartyModels[i];
                _userPartyModels[i] = model with
                {
                    SlotCount = _partyMemberSlotCount
                };
            }
        }

        public void SetSelectPartyNo(PartyNo selectParty)
        {
            _selectParty = selectParty;
        }

        public void SetUnitList(PartyNo partyNo, IReadOnlyList<UserDataId> userUnitIds)
        {
            _unitListPartyNo = partyNo;
            _userUnitIds = new List<UserDataId>(userUnitIds);
        }

        public void UpdateParty(PartyNo partyNo, PartyName name, IReadOnlyList<UserDataId> partyMembers)
        {
            if (!IsNeedsApply(partyNo, name, partyMembers)) return;
            var index = _userPartyModels.FindIndex(model => model.PartyNo == partyNo);
            if (-1 == index) return;

            // 要素が足りない場合はEmptyで埋める
            var list = new List<UserDataId>(partyMembers);
            var max = PartyMemberSlotCount.Max;
            for (int i = partyMembers.Count; i < max.Value; ++i)
            {
                list.Add(UserDataId.Empty);
            }

            var oldParty = _userPartyModels[index];
            _userPartyModels[index] =
                new UserPartyCacheModel(oldParty.PartyNo, name, oldParty.SlotCount, list);
        }
        public UserPartyCacheModel GetCacheParty(PartyNo partyNo)
        {
            return _userPartyModels.Find(x => x.PartyNo == partyNo);
        }

        public IReadOnlyList<UserDataId> GetUnitList(PartyNo partyNo)
        {
            if(_unitListPartyNo != partyNo) return new List<UserDataId>();
            return _userUnitIds;
        }

        public IReadOnlyList<UserPartyCacheModel> GetNeedsApplyParty(IReadOnlyList<UserPartyModel> originalParties)
        {
            return originalParties
                .Where(model => IsNeedsApply(model.PartyNo, model.PartyName, model.GetUnitList()))
                .Select(model => GetCacheParty(model.PartyNo))
                .ToList();
        }

        bool IsNeedsApply(PartyNo partyNo, PartyName name, IReadOnlyList<UserDataId> partyMembers)
        {
            var tmpParty = GetCacheParty(partyNo);
            if (tmpParty.PartyName != name) return true;

            var tmpPartyUnits = tmpParty.GetUnitList();
            for (int i = 0; i < _partyMemberSlotCount.Value; ++i)
            {
                // partyMembersの数が足りない場合、tmpPartyUnitsにEmptyが入っているか確認
                if (i >= partyMembers.Count)
                {
                    if (tmpPartyUnits[i] != UserDataId.Empty) return true;
                    continue;
                }
                if(tmpPartyUnits[i] != partyMembers[i]) return true;
            }

            return false;
        }

        public IReadOnlyList<UserPartyCacheModel> GetAllParties()
        {
            return _userPartyModels;
        }

        public void SetBonusUnits(IReadOnlyList<PartyBonusUnitModel> bonusUnits)
        {
            _partyBonusUnitModels = bonusUnits;
        }

        public IReadOnlyList<PartyBonusUnitModel> GetBonusUnits()
        {
            return _partyBonusUnitModels;
        }

        public void ResetBonusUnits()
        {
            _partyBonusUnitModels = new List<PartyBonusUnitModel>();
        }
    }
}
