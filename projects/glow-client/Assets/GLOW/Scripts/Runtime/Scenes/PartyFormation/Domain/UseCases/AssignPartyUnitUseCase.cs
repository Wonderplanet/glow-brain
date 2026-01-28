using System.Collections.Generic;
using System.Linq;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.Repositories;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Extensions;
using GLOW.Scenes.PartyFormation.Domain.Constants;
using Zenject;

namespace GLOW.Scenes.PartyFormation.Domain.UseCases
{
    public class AssignPartyUnitUseCase
    {
        [Inject] IPartyCacheRepository PartyCacheRepository { get; }
        [Inject] IMstConfigRepository MstConfigRepository { get; }
        [Inject] IMstCharacterDataRepository MstCharacterDataRepository { get; }
        [Inject] IGameRepository GameRepository { get; }

        public PartyMemberAssignmentResultType CheckValidAssignUnit(PartyNo partyNo, UserDataId selectUserUnitId)
        {
            // パーティ空き枠確認
            var party = PartyCacheRepository.GetCacheParty(partyNo);
            var partyUnits = party.GetUnitList();
            if (!partyUnits.Any(userUnitId => userUnitId.IsEmpty()))
            {
                return PartyMemberAssignmentResultType.NotEmpty;
            }

            // スペシャルユニット制限数確認
            var userUnitModels = GameRepository.GetGameFetchOther().UserUnitModels;
            if (GetMstCharacterModel(selectUserUnitId, userUnitModels).RoleType == CharacterUnitRoleType.Special)
            {
                var currentSpecialUnitAssignNum =
                    partyUnits.Count(userUnitId => GetMstCharacterModel(userUnitId, userUnitModels).RoleType == CharacterUnitRoleType.Special);

                if (currentSpecialUnitAssignNum >= MstConfigRepository.GetConfig(MstConfigKey.PartySpecialUnitAssignLimit).Value.ToInt())
                {
                    return PartyMemberAssignmentResultType.SpecialUnitLimit;
                }
            }

            return PartyMemberAssignmentResultType.Valid;
        }

        public void AssignUnitToEmptyIndex(PartyNo partyNo, UserDataId userUnitId)
        {
            var party = PartyCacheRepository.GetCacheParty(partyNo);
            var partyUnits = party.GetUnitList().ToList();
            for(var i = 0 ; i < partyUnits.Count; i++)
            {
                if (partyUnits[i] != UserDataId.Empty) continue;
                partyUnits[i] = userUnitId;
                break;
            }

            PartyCacheRepository.UpdateParty(partyNo, party.PartyName, partyUnits);
        }

        public void  InterruptAssignUnit(PartyNo partyNo, PartyMemberIndex partyMemberIndex, UserDataId userUnitId)
        {
            var party = PartyCacheRepository.GetCacheParty(partyNo);
            var oldPartyUnits = party.GetUnitList().ToList();

            var prevIndex = oldPartyUnits.FindIndex(id => id == userUnitId);
            var newPartyUnits = oldPartyUnits.ToList();
            newPartyUnits.RemoveAt(prevIndex);
            if (partyMemberIndex.Value < newPartyUnits.Count)
            {
                newPartyUnits.Insert(partyMemberIndex.Value, userUnitId);
            }
            else
            {
                newPartyUnits.Add(userUnitId);
            }

            newPartyUnits.Sort((a, b) => a == UserDataId.Empty ? 1 : b == UserDataId.Empty ? -1 : 0);

            PartyCacheRepository.UpdateParty(partyNo, party.PartyName, newPartyUnits);
        }

        MstCharacterModel GetMstCharacterModel(UserDataId userUnitId, IReadOnlyList<UserUnitModel> userUnitModels)
        {
            if (userUnitId.IsEmpty())
            {
                return MstCharacterModel.Empty;
            }

            var userUnit = userUnitModels.Find(unit => unit.UsrUnitId == userUnitId);
            var mstUnit = MstCharacterDataRepository.GetCharacter(userUnit.MstUnitId);
            return mstUnit;
        }
    }
}
